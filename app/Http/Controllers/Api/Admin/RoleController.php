<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    /**
     * 获取机构角色列表
     */
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->get('institution_id');

        $query = Role::with(['institution', 'permissions', 'dataPermissions', 'menus']);

        if ($institutionId) {
            // 获取指定机构的角色 + 系统角色
            $query->where(function($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)
                  ->orWhere('is_system', true);
            });
        }

        $roles = $query->orderBy('is_system', 'desc')
                      ->orderBy('sort_order')
                      ->orderBy('created_at', 'desc')
                      ->paginate(20);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $roles
        ]);
    }

    /**
     * 创建角色
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'institution_id' => 'required|exists:institutions,id',
            'permission_ids' => 'array',
            'data_permission_ids' => 'array',
            'menu_ids' => 'array',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'institution_id' => $request->institution_id,
            'is_system' => false,
            'status' => 'active',
        ]);

        // 分配功能权限
        if ($request->permission_ids) {
            $role->permissions()->sync($request->permission_ids);
        }

        // 分配数据权限
        if ($request->data_permission_ids) {
            $role->dataPermissions()->sync($request->data_permission_ids);
        }

        // 分配菜单权限（新模型）
        if ($request->menu_ids) {
            $role->menus()->sync($request->menu_ids);
        }

        return response()->json([
            'code' => 200,
            'message' => '角色创建成功',
            'data' => $role->load(['permissions', 'dataPermissions', 'menus'])
        ]);
    }

    /**
     * 获取角色详情
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::with(['institution', 'permissions', 'dataPermissions', 'menus'])->findOrFail($id);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $role
        ]);
    }

    /**
     * 更新角色
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // 系统角色不允许修改
        if ($role->is_system) {
            return response()->json([
                'code' => 400,
                'message' => '系统角色不允许修改'
            ], 400);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'permission_ids' => 'array',
            'data_permission_ids' => 'array',
            'menu_ids' => 'array',
        ]);

        $role->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // 更新功能权限
        if ($request->has('permission_ids')) {
            $role->permissions()->sync($request->permission_ids);
        }

        // 更新数据权限
        if ($request->has('data_permission_ids')) {
            $role->dataPermissions()->sync($request->data_permission_ids);
        }

        // 更新菜单权限（新模型）
        if ($request->has('menu_ids')) {
            $role->menus()->sync($request->menu_ids);
        }

        return response()->json([
            'code' => 200,
            'message' => '角色更新成功',
            'data' => $role->load(['permissions', 'dataPermissions'])
        ]);
    }

    /**
     * 删除角色
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        // 系统角色不允许删除
        if ($role->is_system) {
            return response()->json([
                'code' => 400,
                'message' => '系统角色不允许删除'
            ], 400);
        }

        // 检查是否有用户使用此角色
        if ($role->users()->count() > 0) {
            return response()->json([
                'code' => 400,
                'message' => '该角色下还有用户，无法删除'
            ], 400);
        }

        $role->delete();

        return response()->json([
            'code' => 200,
            'message' => '角色删除成功'
        ]);
    }
}
