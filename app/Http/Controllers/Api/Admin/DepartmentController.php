<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * 获取部门列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = Department::with(['institution', 'parent']);

        // 机构筛选
        if ($request->filled('institution_id')) {
            $query->where('institution_id', $request->institution_id);
        }

        // 类型筛选
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 上级部门筛选
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        // 搜索
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }

        // 状态筛选
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 排序
        $query->orderBy('sort_order')->orderBy('created_at');

        // 分页
        $perPage = $request->get('per_page', 15);
        $departments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => '获取部门列表成功',
            'data' => $departments
        ]);
    }

    /**
     * 创建部门
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'institution_id' => 'required|exists:institutions,id',
            'parent_id' => 'nullable|exists:departments,id',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'type' => ['required', Rule::in(['campus', 'department', 'classroom'])],
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:50',
            'manager_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'facilities' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        // 验证部门代码在同一机构内唯一
        $exists = Department::where('institution_id', $validated['institution_id'])
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '该机构内部门代码已存在'
            ], 422);
        }

        // 验证上级部门属于同一机构
        if (!empty($validated['parent_id'])) {
            $parent = Department::find($validated['parent_id']);
            if ($parent->institution_id !== $validated['institution_id']) {
                return response()->json([
                    'success' => false,
                    'message' => '上级部门必须属于同一机构'
                ], 422);
            }
        }

        $department = Department::create($validated);
        $department->load(['institution', 'parent']);

        return response()->json([
            'success' => true,
            'message' => '部门创建成功',
            'data' => $department
        ], 201);
    }

    /**
     * 获取部门详情
     */
    public function show(Department $department): JsonResponse
    {
        $department->load(['institution', 'parent', 'children', 'users']);

        return response()->json([
            'success' => true,
            'message' => '获取部门详情成功',
            'data' => $department
        ]);
    }

    /**
     * 更新部门
     */
    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:departments,id',
            'name' => 'sometimes|required|string|max:100',
            'code' => 'sometimes|required|string|max:50',
            'type' => ['sometimes', 'required', Rule::in(['campus', 'department', 'classroom'])],
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:50',
            'manager_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'facilities' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        // 验证部门代码在同一机构内唯一
        if (isset($validated['code'])) {
            $exists = Department::where('institution_id', $department->institution_id)
                ->where('code', $validated['code'])
                ->where('id', '!=', $department->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => '该机构内部门代码已存在'
                ], 422);
            }
        }

        // 验证上级部门
        if (isset($validated['parent_id'])) {
            if ($validated['parent_id'] == $department->id) {
                return response()->json([
                    'success' => false,
                    'message' => '不能将自己设为上级部门'
                ], 422);
            }

            $parent = Department::find($validated['parent_id']);
            if ($parent->institution_id !== $department->institution_id) {
                return response()->json([
                    'success' => false,
                    'message' => '上级部门必须属于同一机构'
                ], 422);
            }
        }

        $department->update($validated);
        $department->load(['institution', 'parent']);

        return response()->json([
            'success' => true,
            'message' => '部门更新成功',
            'data' => $department
        ]);
    }

    /**
     * 删除部门
     */
    public function destroy(Department $department): JsonResponse
    {
        // 检查是否有子部门
        if ($department->children()->exists()) {
            return response()->json([
                'success' => false,
                'message' => '该部门下还有子部门，无法删除'
            ], 422);
        }

        // 检查是否有用户
        if ($department->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => '该部门下还有用户，无法删除'
            ], 422);
        }

        $department->delete();

        return response()->json([
            'success' => true,
            'message' => '部门删除成功'
        ]);
    }

    /**
     * 获取部门树形结构
     */
    public function tree(Request $request): JsonResponse
    {
        $institutionId = $request->get('institution_id');

        if (!$institutionId) {
            return response()->json([
                'success' => false,
                'message' => '请指定机构ID'
            ], 422);
        }

        // 先检查是否有数据
        $totalCount = Department::where('institution_id', $institutionId)->count();
        $rootCount = Department::where('institution_id', $institutionId)
            ->whereNull('parent_id')
            ->count();

        // 尝试不同的查询方式
        $departments = Department::where('institution_id', $institutionId)
            ->where(function($query) {
                $query->whereNull('parent_id')
                      ->orWhere('parent_id', '');
            })
            ->with('allChildren')
            ->orderBy('sort_order')
            ->get();

        // 如果还是没有数据，尝试获取所有部门然后手动构建树形结构
        if ($departments->isEmpty()) {
            $allDepartments = Department::where('institution_id', $institutionId)
                ->orderBy('sort_order')
                ->get();

            // 手动构建树形结构
            $departments = $allDepartments->filter(function($dept) {
                return is_null($dept->parent_id) || $dept->parent_id === '' || $dept->parent_id === 0;
            });

            foreach ($departments as $dept) {
                $dept->load('allChildren');
            }
        }

        return response()->json([
            'success' => true,
            'message' => '获取部门树形结构成功',
            'data' => $departments
        ]);
    }
}
