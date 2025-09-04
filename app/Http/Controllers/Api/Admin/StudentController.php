<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Student::with(['user', 'institution'])
            ->byInstitution(auth()->user()->institution_id);

        // 按学员类型筛选
        if ($request->filled('student_type')) {
            $query->byType($request->student_type);
        }

        // 按跟进状态筛选
        if ($request->filled('follow_up_status')) {
            $query->where('follow_up_status', $request->follow_up_status);
        }

        // 按意向等级筛选
        if ($request->filled('intention_level')) {
            $query->where('intention_level', $request->intention_level);
        }

        // 排除已在指定班级中的学员
        if ($request->filled('exclude_class_id')) {
            $classId = $request->exclude_class_id;
            $query->whereDoesntHave('studentClasses', function ($q) use ($classId) {
                $q->where('class_id', $classId)->where('status', 'active');
            });
        }

        // 搜索
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%");
            });
        }

        // 排序
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $students = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'data' => StudentResource::collection($students->items()),
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'parent_relationship' => 'required|in:father,mother,guardian,other',
            'student_type' => 'required|in:' . Student::TYPE_POTENTIAL . ',' . Student::TYPE_TRIAL,
            'follow_up_status' => 'required|in:new,contacted,interested,not_interested,follow_up',
            'intention_level' => 'required|in:high,medium,low',
            'source' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'create_parent_account' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // 添加机构ID
            $validated['institution_id'] = auth()->user()->institution_id;

            // 创建学员
            $student = Student::create($validated);

            // 如果需要创建家长账号
            if ($request->boolean('create_parent_account')) {
                $user = User::create([
                    'name' => $validated['parent_name'],
                    'phone' => $validated['parent_phone'],
                    'email' => $validated['parent_phone'] . '@parent.local', // 临时邮箱
                    'password' => Hash::make('123456'), // 默认密码
                    'institution_id' => $validated['institution_id'],
                    'status' => 'active',
                ]);

                // 关联学员和用户
                $student->users()->attach($user->id, [
                    'relationship' => $validated['parent_relationship']
                ]);

                // 更新学员的主要用户ID
                $student->update(['user_id' => $user->id]);
            }

            DB::commit();

            $student->load(['user', 'institution', 'users']);

            return response()->json([
                'code' => 200,
                'message' => '学员创建成功',
                'data' => new StudentResource($student),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '创建失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student): JsonResponse
    {
        // 检查权限：只能查看同机构的学员
        if ($student->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问该学员信息',
            ], 403);
        }

        $student->load(['user', 'institution', 'users']);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => new StudentResource($student),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student): JsonResponse
    {
        // 检查权限：只能修改同机构的学员
        if ($student->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权修改该学员信息',
            ], 403);
        }

        // 构建验证规则
        $allowedTypes = [Student::TYPE_POTENTIAL, Student::TYPE_TRIAL, Student::TYPE_REFUNDED, Student::TYPE_GRADUATED, Student::TYPE_SUSPENDED];

        // 如果当前学员已经是正式学员，允许保持该状态
        if ($student->student_type === Student::TYPE_ENROLLED) {
            $allowedTypes[] = Student::TYPE_ENROLLED;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'parent_relationship' => 'required|in:father,mother,guardian,other',
            'student_type' => 'required|in:' . implode(',', $allowedTypes),
            'follow_up_status' => 'required|in:new,contacted,interested,not_interested,follow_up',
            'intention_level' => 'required|in:high,medium,low',
            'source' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // 防止手动将非正式学员改为正式学员
        if ($request->student_type === Student::TYPE_ENROLLED && $student->student_type !== Student::TYPE_ENROLLED) {
            return response()->json([
                'code' => 400,
                'message' => '不能手动将学员状态改为正式学员，请通过报名流程完成',
            ], 400);
        }

        try {
            $student->update($validated);
            $student->load(['user', 'institution', 'users']);

            return response()->json([
                'code' => 200,
                'message' => '学员信息更新成功',
                'data' => new StudentResource($student),
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
    public function destroy(Student $student): JsonResponse
    {
        // 检查权限：只能删除同机构的学员
        if ($student->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权删除该学员',
            ], 403);
        }

        try {
            // 软删除学员
            $student->delete();

            return response()->json([
                'code' => 200,
                'message' => '学员删除成功',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '删除失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取学员统计信息
     */
    public function statistics(): JsonResponse
    {
        $institutionId = auth()->user()->institution_id;

        $stats = [
            'total' => Student::byInstitution($institutionId)->count(),
            'by_type' => [
                'potential' => Student::byInstitution($institutionId)->byType('potential')->count(),
                'trial' => Student::byInstitution($institutionId)->byType('trial')->count(),
                'enrolled' => Student::byInstitution($institutionId)->byType('enrolled')->count(),
                'graduated' => Student::byInstitution($institutionId)->byType('graduated')->count(),
                'suspended' => Student::byInstitution($institutionId)->byType('suspended')->count(),
            ],
            'by_follow_up' => [
                'new' => Student::byInstitution($institutionId)->where('follow_up_status', 'new')->count(),
                'contacted' => Student::byInstitution($institutionId)->where('follow_up_status', 'contacted')->count(),
                'interested' => Student::byInstitution($institutionId)->where('follow_up_status', 'interested')->count(),
                'not_interested' => Student::byInstitution($institutionId)->where('follow_up_status', 'not_interested')->count(),
                'follow_up' => Student::byInstitution($institutionId)->where('follow_up_status', 'follow_up')->count(),
            ],
            'by_intention' => [
                'high' => Student::byInstitution($institutionId)->where('intention_level', 'high')->count(),
                'medium' => Student::byInstitution($institutionId)->where('intention_level', 'medium')->count(),
                'low' => Student::byInstitution($institutionId)->where('intention_level', 'low')->count(),
            ],
        ];

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $stats,
        ]);
    }

    /**
     * 获取可创建的学员类型选项
     */
    public function getCreatableTypes(): JsonResponse
    {
        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => Student::getCreatableTypes(),
        ]);
    }

    /**
     * 关联用户到学员
     */
    public function linkUser(Request $request, Student $student): JsonResponse
    {
        // 检查权限：只能操作同机构的学员
        if ($student->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作该学员',
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        // 检查用户是否属于同一机构
        if ($user->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '只能关联同机构的用户',
            ], 403);
        }

        try {
            DB::beginTransaction();

            // 更新学员的主要用户ID
            $student->update(['user_id' => $user->id]);

            // 在多对多关系表中添加关联（如果不存在）
            if (!$student->users()->where('user_id', $user->id)->exists()) {
                $student->users()->attach($user->id, [
                    'relationship' => $student->parent_relationship ?? 'parent'
                ]);
            }

            DB::commit();

            $student->load(['user', 'users']);

            return response()->json([
                'code' => 200,
                'message' => '用户关联成功',
                'data' => new StudentResource($student),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '关联失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 取消学员与用户的关联
     */
    public function unlinkUser(Student $student): JsonResponse
    {
        // 检查权限：只能操作同机构的学员
        if ($student->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作该学员',
            ], 403);
        }

        try {
            DB::beginTransaction();

            // 清除学员的主要用户ID
            $student->update(['user_id' => null]);

            // 清除多对多关系表中的所有关联
            $student->users()->detach();

            DB::commit();

            $student->load(['user', 'users']);

            return response()->json([
                'code' => 200,
                'message' => '取消关联成功',
                'data' => new StudentResource($student),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '取消关联失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
