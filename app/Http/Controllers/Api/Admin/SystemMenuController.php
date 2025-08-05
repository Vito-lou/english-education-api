<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemMenu;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SystemMenuController extends Controller
{
    /**
     * 获取菜单树
     */
    public function index(): JsonResponse
    {
        $menus = SystemMenu::getMenuTree();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $menus
        ]);
    }

    /**
     * 获取菜单列表（平铺）
     */
    public function list(): JsonResponse
    {
        $menus = SystemMenu::with(['parent', 'permissions'])
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $menus
        ]);
    }

    /**
     * 创建菜单
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:100|unique:system_menus,code',
            'path' => 'nullable|string|max:200',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:system_menus,id',
            'sort_order' => 'integer|min:0',
            'description' => 'nullable|string',
        ]);

        $menu = SystemMenu::create($request->all());

        // 自动创建对应的权限
        Permission::create([
            'name' => $menu->name,
            'code' => $menu->code,
            'type' => 'menu',
            'menu_id' => $menu->id,
            'parent_id' => null,
            'sort_order' => $menu->sort_order,
            'status' => 'active',
            'description' => '访问' . $menu->name . '的权限',
        ]);

        return response()->json([
            'code' => 200,
            'message' => '菜单创建成功，已自动创建对应权限',
            'data' => $menu->load(['parent', 'permissions'])
        ]);
    }

    /**
     * 获取菜单详情
     */
    public function show(string $id): JsonResponse
    {
        $menu = SystemMenu::with(['parent', 'children', 'permissions'])
            ->findOrFail($id);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $menu
        ]);
    }

    /**
     * 更新菜单
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $menu = SystemMenu::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:100|unique:system_menus,code,' . $menu->id,
            'path' => 'nullable|string|max:200',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|exists:system_menus,id',
            'sort_order' => 'integer|min:0',
            'status' => 'required|in:active,inactive',
            'description' => 'nullable|string',
        ]);

        $menu->update($request->all());

        return response()->json([
            'code' => 200,
            'message' => '菜单更新成功',
            'data' => $menu->load(['parent', 'permissions'])
        ]);
    }

    /**
     * 删除菜单
     */
    public function destroy(string $id): JsonResponse
    {
        $menu = SystemMenu::findOrFail($id);

        // 检查是否有子菜单
        if ($menu->children()->count() > 0) {
            return response()->json([
                'code' => 400,
                'message' => '该菜单下还有子菜单，无法删除'
            ], 400);
        }

        // 检查是否有角色在使用该权限
        $permission = Permission::where('menu_id', $menu->id)->first();
        if ($permission && $permission->roles()->count() > 0) {
            return response()->json([
                'code' => 400,
                'message' => '该菜单权限正在被角色使用，无法删除'
            ], 400);
        }

        // 删除菜单和对应的权限
        if ($permission) {
            $permission->delete();
        }
        $menu->delete();

        return response()->json([
            'code' => 200,
            'message' => '菜单删除成功'
        ]);
    }
}
