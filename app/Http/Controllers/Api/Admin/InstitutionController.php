<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class InstitutionController extends Controller
{
    /**
     * 获取机构列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = Institution::query();

        // 搜索
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%");
            });
        }

        // 状态筛选
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 排序
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 分页
        $perPage = $request->get('per_page', 15);
        $institutions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => '获取机构列表成功',
            'data' => $institutions
        ]);
    }

    /**
     * 创建机构
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:institutions,code',
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'contact_person' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'business_license' => 'nullable|string',
            'business_hours' => 'nullable|array',
            'settings' => 'nullable|array',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
            'established_at' => 'nullable|date',
        ]);

        $institution = Institution::create($validated);

        return response()->json([
            'success' => true,
            'message' => '机构创建成功',
            'data' => $institution
        ], 201);
    }

    /**
     * 获取机构详情
     */
    public function show(Institution $institution): JsonResponse
    {
        $institution->load(['departments', 'users', 'roles']);

        return response()->json([
            'success' => true,
            'message' => '获取机构详情成功',
            'data' => $institution
        ]);
    }

    /**
     * 更新机构
     */
    public function update(Request $request, Institution $institution): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('institutions', 'code')->ignore($institution->id)],
            'logo' => 'nullable|string',
            'description' => 'nullable|string',
            'contact_person' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'business_license' => 'nullable|string',
            'business_hours' => 'nullable|array',
            'settings' => 'nullable|array',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'suspended'])],
            'established_at' => 'nullable|date',
        ]);

        $institution->update($validated);

        return response()->json([
            'success' => true,
            'message' => '机构更新成功',
            'data' => $institution
        ]);
    }

    /**
     * 删除机构
     */
    public function destroy(Institution $institution): JsonResponse
    {
        // 检查是否有关联数据
        if ($institution->users()->exists() || $institution->departments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => '该机构下还有用户或部门，无法删除'
            ], 422);
        }

        $institution->delete();

        return response()->json([
            'success' => true,
            'message' => '机构删除成功'
        ]);
    }

    /**
     * 获取机构统计信息
     */
    public function statistics(Institution $institution): JsonResponse
    {
        $stats = [
            'total_users' => $institution->users()->count(),
            'active_users' => $institution->users()->where('status', 'active')->count(),
            'total_departments' => $institution->departments()->count(),
            'campuses_count' => $institution->campuses()->count(),
            'teachers_count' => $institution->users()->where('can_teach', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => '获取机构统计信息成功',
            'data' => $stats
        ]);
    }
}
