<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ManualAttendanceController extends Controller
{
    /**
     * 获取班级学员列表（用于手动点名）
     */
    public function getClassStudents(Request $request, $classId): JsonResponse
    {
        $user = Auth::user();

        // 验证班级是否属于当前机构
        $class = ClassModel::where('id', $classId)
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$class) {
            return response()->json([
                'code' => 404,
                'message' => '班级不存在',
            ], 404);
        }

        // 获取班级学员
        $students = Student::whereHas('classes', function ($query) use ($classId) {
                $query->where('class_id', $classId)
                      ->where('student_classes.status', 'active');
            })
            ->with(['user:id,name,avatar'])
            ->get(['id', 'user_id', 'name', 'student_type']);

        // 添加学员类型中文名
        $students->each(function ($student) {
            $student->student_type_name = $student->student_type_name;
        });

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'class' => $class,
                'students' => $students,
            ],
        ]);
    }

    /**
     * 获取课程内容列表（用于选择上课内容）
     */
    public function getLessons(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Lesson::whereHas('unit.course', function ($q) use ($user) {
            $q->where('institution_id', $user->institution_id);
        });

        // 按课程等级筛选
        if ($request->filled('level')) {
            $query->whereHas('unit.course', function ($q) use ($request) {
                $q->where('level', $request->level);
            });
        }

        // 按课程筛选
        if ($request->filled('course_id')) {
            $query->whereHas('unit', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        $lessons = $query->with([
                'unit:id,course_id,name',
                'unit.course:id,name,code'
            ])
            ->orderBy('sort_order')
            ->get(['id', 'unit_id', 'name', 'content', 'sort_order']);

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $lessons,
        ]);
    }

    /**
     * 创建手动点名记录
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'lesson_time' => 'required|date', // 改为统一的lesson_time字段
            'lesson_id' => 'nullable|exists:lessons,id',
            'lesson_content' => 'nullable|string|max:1000',
            'students' => 'required|array|min:1',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.attendance_status' => [
                'required',
                Rule::in(['present', 'absent', 'late', 'leave_early', 'sick_leave', 'personal_leave'])
            ],
            'students.*.deducted_lessons' => 'required|numeric|min:0|max:10',
            'students.*.teacher_notes' => 'nullable|string|max:500',
        ]);

        // 验证班级是否属于当前机构
        $class = ClassModel::where('id', $validated['class_id'])
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$class) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作此班级',
            ], 403);
        }

        // 验证学员是否都属于该班级
        $classStudentIds = Student::whereHas('classes', function ($query) use ($validated) {
                $query->where('classes.id', $validated['class_id']);
            })->pluck('id')->toArray();

        $requestStudentIds = collect($validated['students'])->pluck('student_id')->toArray();
        $invalidStudents = array_diff($requestStudentIds, $classStudentIds);

        if (!empty($invalidStudents)) {
            return response()->json([
                'code' => 400,
                'message' => '部分学员不属于该班级',
                'errors' => ['students' => '学员ID: ' . implode(', ', $invalidStudents) . ' 不属于该班级'],
            ], 400);
        }

        // 检查是否有重复的点名记录
        $existingRecords = AttendanceRecord::where('record_type', 'manual')
            ->where('class_id', $validated['class_id'])
            ->where('lesson_time', $validated['lesson_time'])
            ->whereIn('student_id', $requestStudentIds)
            ->exists();

        if ($existingRecords) {
            return response()->json([
                'code' => 400,
                'message' => '该时间段已有点名记录，请检查',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $attendanceRecords = [];

            foreach ($validated['students'] as $studentData) {
                $attendanceRecord = AttendanceRecord::create([
                    'record_type' => 'manual',
                    'schedule_id' => null,
                    'class_id' => $validated['class_id'],
                    'lesson_id' => $validated['lesson_id'] ?? null,
                    'lesson_time' => $validated['lesson_time'],
                    'lesson_content' => $validated['lesson_content'] ?? null,
                    'student_id' => $studentData['student_id'],
                    'attendance_status' => $studentData['attendance_status'],
                    'deducted_lessons' => $studentData['deducted_lessons'],
                    'teacher_notes' => $studentData['teacher_notes'] ?? null,
                    'recorded_by' => $user->id,
                    'recorded_at' => now(),
                ]);

                $attendanceRecords[] = $attendanceRecord;
            }

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '手动点名记录创建成功',
                'data' => [
                    'created_count' => count($attendanceRecords),
                    'records' => $attendanceRecords,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'code' => 500,
                'message' => '创建失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取班级的手动点名记录
     */
    public function getClassManualRecords(Request $request, $classId): JsonResponse
    {
        $user = Auth::user();

        // 验证班级权限
        $class = ClassModel::where('id', $classId)
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$class) {
            return response()->json([
                'code' => 404,
                'message' => '班级不存在',
            ], 404);
        }

        $query = AttendanceRecord::where('record_type', 'manual')
            ->where('class_id', $classId)
            ->with([
                'student.user:id,name',
                'lesson:id,title',
                'recordedBy:id,name'
            ]);

        // 按时间筛选
        if ($request->filled('start_date')) {
            $query->whereDate('lesson_time', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('lesson_time', '<=', $request->end_date);
        }

        $records = $query->orderBy('lesson_time', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $records,
        ]);
    }
}
