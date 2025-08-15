<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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
            'data' => $students,
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
            'student_type' => 'required|in:potential,trial,enrolled,graduated,suspended',
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
                'data' => $student,
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
            'data' => $student,
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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'parent_name' => 'required|string|max:255',
            'parent_phone' => 'required|string|max:20',
            'parent_relationship' => 'required|in:father,mother,guardian,other',
            'student_type' => 'required|in:potential,trial,enrolled,graduated,suspended',
            'follow_up_status' => 'required|in:new,contacted,interested,not_interested,follow_up',
            'intention_level' => 'required|in:high,medium,low',
            'source' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $student->update($validated);
            $student->load(['user', 'institution', 'users']);

            return response()->json([
                'code' => 200,
                'message' => '学员信息更新成功',
                'data' => $student,
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
}
