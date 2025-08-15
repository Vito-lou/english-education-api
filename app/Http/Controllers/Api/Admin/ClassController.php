<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClassController extends Controller
{
    /**
     * 获取班级列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $institutionId = $user->institution_id;

        $query = ClassModel::with([
            'campus:id,name',
            'course:id,name',
            'level:id,name',
            'teacher:id,name'
        ])
        ->forInstitution($institutionId)
        ->byStatus($request->get('status'))
        ->byCampus($request->get('campus_id'))
        ->byCourse($request->get('course_id'))
        ->search($request->get('search'));

        // 排序
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $classes = $query->paginate($request->get('per_page', 15));

        // 添加计算字段
        $classes->getCollection()->transform(function ($class) {
            $class->current_student_count = $class->current_student_count;
            $class->capacity_info = $class->capacity_info;
            $class->status_name = $class->status_name;
            return $class;
        });

        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    /**
     * 获取班级详情
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();

        $class = ClassModel::with([
            'campus:id,name',
            'course:id,name',
            'level:id,name',
            'teacher:id,name',
            'institution:id,name'
        ])
        ->forInstitution($user->institution_id)
        ->findOrFail($id);

        // 添加计算字段
        $class->current_student_count = $class->current_student_count;
        $class->capacity_info = $class->capacity_info;
        $class->status_name = $class->status_name;

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $class,
        ]);
    }

    /**
     * 创建班级
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'campus_id' => 'required|exists:departments,id',
            'course_id' => 'required|exists:courses,id',
            'level_id' => 'nullable|exists:course_levels,id',
            'max_students' => 'required|integer|min:1|max:100',
            'teacher_id' => 'required|exists:users,id',
            'total_lessons' => 'required|integer|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // 验证校区和课程是否属于当前机构
        $campus = Department::where('id', $validated['campus_id'])
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$campus) {
            return response()->json([
                'success' => false,
                'message' => '校区不存在或不属于当前机构',
            ], 422);
        }

        $course = Course::where('id', $validated['course_id'])
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => '课程不存在或不属于当前机构',
            ], 422);
        }

        // 验证级别是否属于该课程
        if ($validated['level_id']) {
            $level = CourseLevel::where('id', $validated['level_id'])
                ->where('course_id', $validated['course_id'])
                ->first();

            if (!$level) {
                return response()->json([
                    'success' => false,
                    'message' => '课程级别不存在或不属于该课程',
                ], 422);
            }
        }

        // 验证教师是否属于当前机构
        $teacher = User::where('id', $validated['teacher_id'])
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => '教师不存在或不属于当前机构',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $class = ClassModel::create([
                ...$validated,
                'institution_id' => $user->institution_id,
                'start_date' => now()->toDateString(),
                'status' => 'active',
            ]);

            $class->load([
                'campus:id,name',
                'course:id,name',
                'level:id,name',
                'teacher:id,name'
            ]);

            // 添加计算字段
            $class->current_student_count = $class->current_student_count;
            $class->capacity_info = $class->capacity_info;
            $class->status_name = $class->status_name;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '班级创建成功',
                'data' => $class,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '班级创建失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 更新班级
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();

        $class = ClassModel::forInstitution($user->institution_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'campus_id' => 'required|exists:departments,id',
            'course_id' => 'required|exists:courses,id',
            'level_id' => 'nullable|exists:course_levels,id',
            'max_students' => 'required|integer|min:1|max:100',
            'teacher_id' => 'required|exists:users,id',
            'total_lessons' => 'required|integer|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        // 同样的验证逻辑...
        $campus = Department::where('id', $validated['campus_id'])
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$campus) {
            return response()->json([
                'success' => false,
                'message' => '校区不存在或不属于当前机构',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $class->update($validated);

            $class->load([
                'campus:id,name',
                'course:id,name',
                'level:id,name',
                'teacher:id,name'
            ]);

            // 添加计算字段
            $class->current_student_count = $class->current_student_count;
            $class->capacity_info = $class->capacity_info;
            $class->status_name = $class->status_name;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '班级更新成功',
                'data' => $class,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '班级更新失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 删除班级
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();

        $class = ClassModel::forInstitution($user->institution_id)->findOrFail($id);

        // 检查是否有学员
        if ($class->current_student_count > 0) {
            return response()->json([
                'success' => false,
                'message' => '班级中还有学员，无法删除',
            ], 422);
        }

        try {
            $class->delete();

            return response()->json([
                'success' => true,
                'message' => '班级删除成功',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '班级删除失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 结业班级
     */
    public function graduate($id): JsonResponse
    {
        $user = Auth::user();

        $class = ClassModel::forInstitution($user->institution_id)->findOrFail($id);

        if ($class->status === 'graduated') {
            return response()->json([
                'success' => false,
                'message' => '班级已经结业',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $class->graduate();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '班级结业成功',
                'data' => $class,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '班级结业失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取班级统计信息
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'total' => ClassModel::forInstitution($user->institution_id)->count(),
            'active' => ClassModel::forInstitution($user->institution_id)->where('status', 'active')->count(),
            'graduated' => ClassModel::forInstitution($user->institution_id)->where('status', 'graduated')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
