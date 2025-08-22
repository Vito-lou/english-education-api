<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentEnrollment;
use App\Models\Student;
use App\Models\Department;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class StudentEnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudentEnrollment::with([
            'student',
            'campus',
            'course',
            'level',
            'salesPerson'
        ]);

        // 按学员ID筛选
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        // 按校区筛选
        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        // 按课程筛选
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // 按状态筛选
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 按付款状态筛选
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        // 分页
        $perPage = $request->get('per_page', 15);
        $enrollments = $query->paginate($perPage);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $enrollments,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'campus_id' => 'required|exists:departments,id',
            'course_id' => 'required|exists:courses,id',
            'price_per_lesson' => 'required|numeric|min:0.01',
            'lesson_count' => 'required|integer|min:1',
            'discount_type' => 'required|in:none,percentage,amount',
            'discount_value' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'remaining_lessons' => 'required|integer|min:1',
        ], [
            'student_id.required' => '学员ID不能为空',
            'student_id.exists' => '学员不存在',
            'campus_id.required' => '校区不能为空',
            'campus_id.exists' => '校区不存在',
            'course_id.required' => '课程不能为空',
            'course_id.exists' => '课程不存在',
            'price_per_lesson.required' => '课时单价不能为空',
            'price_per_lesson.numeric' => '课时单价必须为数字',
            'price_per_lesson.min' => '课时单价必须大于0',
            'lesson_count.required' => '课时数量不能为空',
            'lesson_count.integer' => '课时数量必须为整数',
            'lesson_count.min' => '课时数量必须大于0',
            'discount_type.required' => '折扣类型不能为空',
            'discount_type.in' => '折扣类型无效',
            'discount_value.required' => '折扣值不能为空',
            'discount_value.numeric' => '折扣值必须为数字',
            'discount_value.min' => '折扣值不能为负数',
            'total_amount.required' => '总金额不能为空',
            'total_amount.numeric' => '总金额必须为数字',
            'total_amount.min' => '总金额不能为负数',
            'remaining_lessons.required' => '剩余课时不能为空',
            'remaining_lessons.integer' => '剩余课时必须为整数',
            'remaining_lessons.min' => '剩余课时必须大于0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            DB::beginTransaction();

            // 获取学员信息以获取机构ID
            $student = Student::findOrFail($request->student_id);

            // 创建报名记录
            $enrollment = StudentEnrollment::create([
                'student_id' => $request->student_id,
                'institution_id' => $student->institution_id,
                'campus_id' => $request->campus_id,
                'course_id' => $request->course_id,
                'enrollment_date' => Carbon::now()->toDateString(),
                'total_lessons' => $request->lesson_count,
                'used_lessons' => 0,
                'remaining_lessons' => $request->remaining_lessons,
                'status' => 'active', // 默认为进行中
                'enrollment_fee' => $request->price_per_lesson * $request->lesson_count, // 原价
                'price_per_lesson' => $request->price_per_lesson,
                'discount_type' => $request->discount_type,
                'discount_value' => $request->discount_value,
                'actual_amount' => $request->total_amount, // 实际收费
                'paid_amount' => $request->total_amount, // 假设立即付款
                'payment_status' => 'paid', // 假设立即付款
                'sales_person_id' => auth()->id(),
            ]);

            // 报名成功后，更新学员状态为正式学员
            if (in_array($student->student_type, [Student::TYPE_POTENTIAL, Student::TYPE_TRIAL])) {
                $student->markAsEnrolled();
            }

            DB::commit();

            // 加载关联数据
            $enrollment->load(['student', 'campus', 'course', 'salesPerson']);

            return response()->json([
                'code' => 200,
                'message' => '报名成功',
                'data' => $enrollment,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '报名失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $enrollment = StudentEnrollment::with([
                'student',
                'campus',
                'course',
                'level',
                'salesPerson'
            ])->findOrFail($id);

            return response()->json([
                'code' => 200,
                'message' => '获取成功',
                'data' => $enrollment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 404,
                'message' => '报名记录不存在',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $enrollment = StudentEnrollment::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|in:pending,active,suspended,completed,cancelled',
                'payment_status' => 'sometimes|in:unpaid,partial,paid,refunded',
                'remarks' => 'sometimes|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 400,
                    'message' => '参数验证失败',
                    'errors' => $validator->errors(),
                ], 400);
            }

            $enrollment->update($request->only([
                'status',
                'payment_status',
                'remarks'
            ]));

            $enrollment->load(['student', 'campus', 'course', 'salesPerson']);

            return response()->json([
                'code' => 200,
                'message' => '更新成功',
                'data' => $enrollment,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '更新失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $enrollment = StudentEnrollment::findOrFail($id);

            // 检查是否已经有上课记录
            if ($enrollment->used_lessons > 0) {
                return response()->json([
                    'code' => 400,
                    'message' => '该报名记录已有上课记录，无法删除',
                ], 400);
            }

            $enrollment->delete();

            return response()->json([
                'code' => 200,
                'message' => '删除成功',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '删除失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取退费信息（计算建议退费金额）
     */
    public function getRefundInfo(string $id): JsonResponse
    {
        try {
            $enrollment = StudentEnrollment::with(['student', 'course'])->findOrFail($id);

            // 计算已消课数量（通过点名记录统计）
            $attendedLessons = \App\Models\AttendanceRecord::whereHas('schedule', function ($query) use ($enrollment) {
                $query->whereHas('class.students', function ($q) use ($enrollment) {
                    $q->where('student_classes.student_id', $enrollment->student_id)
                      ->where('student_classes.status', 'active');
                });
            })
            ->where('student_id', $enrollment->student_id)
            ->where('attendance_status', 'present') // 只统计出席的课程
            ->count();

            // 计算建议退费金额
            $consumedAmount = $attendedLessons * $enrollment->price_per_lesson;
            $suggestedRefundAmount = max(0, $enrollment->actual_amount - $consumedAmount);

            return response()->json([
                'code' => 200,
                'message' => '获取退费信息成功',
                'data' => [
                    'enrollment_id' => $enrollment->id,
                    'student_name' => $enrollment->student->name,
                    'course_name' => $enrollment->course->name,
                    'original_amount' => $enrollment->actual_amount,
                    'price_per_lesson' => $enrollment->price_per_lesson,
                    'total_lessons' => $enrollment->total_lessons,
                    'attended_lessons' => $attendedLessons,
                    'consumed_amount' => $consumedAmount,
                    'suggested_refund_amount' => $suggestedRefundAmount,
                    'max_refund_amount' => $enrollment->actual_amount, // 最大可退费金额
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('获取退费信息失败: ' . $e->getMessage());
            return response()->json([
                'code' => 500,
                'message' => '获取退费信息失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 退费处理
     */
    public function refund(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_reason' => 'required|string|max:500',
        ], [
            'refund_amount.required' => '退费金额不能为空',
            'refund_amount.numeric' => '退费金额必须为数字',
            'refund_amount.min' => '退费金额必须大于0',
            'refund_reason.required' => '退费原因不能为空',
            'refund_reason.string' => '退费原因必须为文本',
            'refund_reason.max' => '退费原因不能超过500字符',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => '参数验证失败',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            DB::beginTransaction();

            $enrollment = StudentEnrollment::findOrFail($id);

            // 业务逻辑校验
            if ($request->refund_amount > $enrollment->actual_amount) {
                return response()->json(['code' => 400, 'message' => '退费金额不能超过订单实际支付金额'], 400);
            }
            if ($enrollment->payment_status === 'refunded') {
                return response()->json(['code' => 400, 'message' => '该订单已处理过退费，请勿重复操作'], 400);
            }

            // 更新报名记录
            $enrollment->update([
                'payment_status' => 'refunded',
                'status' => 'cancelled', // 将订单状态标记为已取消
                'refund_amount' => $request->refund_amount,
                'refund_reason' => $request->refund_reason,
                'refunded_at' => now(),
                'refund_processed_by' => auth()->id(),
                'remarks' => trim(($enrollment->remarks ?? '') . "\n" . "执行退费操作：退款 {$request->refund_amount}元，原因：{$request->refund_reason}"),
                'remaining_lessons' => 0, // 退费后，此订单的剩余课时直接清零
            ]);

            // 注意：学员的总剩余课时是通过动态计算所有有效报名订单的 remaining_lessons 之和得出的。
            // 因此，此处无需也无法直接修改 students 表。将此订单的剩余课时清零即完成了课时“返还”的逻辑。

            // 检查学员是否还有其他有效的报名记录
            $student = $enrollment->student;
            $hasActiveEnrollments = $student->enrollments()
                ->where('id', '!=', $enrollment->id)
                ->whereIn('status', ['active', 'completed'])
                ->where('payment_status', '!=', 'refunded')
                ->exists();

            // 如果没有其他有效报名记录，将学员状态调整为已退费学员
            if (!$hasActiveEnrollments && $student->student_type === Student::TYPE_ENROLLED) {
                $student->update(['student_type' => Student::TYPE_REFUNDED]);
            }

            // 将该学员从所有相关班级中标记为已退班
            $student->studentClasses()
                ->whereHas('class', function ($q) use ($enrollment) {
                    $q->where('course_id', $enrollment->course_id);
                })
                ->where('status', 'active')
                ->update(['status' => 'dropped']);  // 已退班

            DB::commit();

            $enrollment->load(['student', 'campus', 'course', 'salesPerson']);

            return response()->json([
                'code' => 200,
                'message' => '退费成功',
                'data' => $enrollment,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '退费失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
