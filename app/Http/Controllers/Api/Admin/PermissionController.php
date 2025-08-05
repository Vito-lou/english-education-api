<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\DataPermission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PermissionController extends Controller
{
    /**
     * 获取功能权限树
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::with('children')
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $permissions
        ]);
    }

    /**
     * 获取数据权限列表
     */
    public function dataPermissions(): JsonResponse
    {
        $dataPermissions = DataPermission::where('status', 'active')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('resource_type');

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $dataPermissions
        ]);
    }

    /**
     * 获取所有权限（用于角色配置）
     */
    public function all(): JsonResponse
    {
        // 获取功能权限（简化版：每个菜单一个权限）
        $permissions = Permission::with('menu')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        // 获取数据权限，按资源类型分组
        $dataPermissions = DataPermission::where('status', 'active')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('resource_type');

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'permissions' => $permissions,
                'data_permissions' => $dataPermissions
            ]
        ]);
    }

    /**
     * 获取菜单权限树（新的格式，按菜单分组）
     */
    public function menuPermissions(): JsonResponse
    {
        $menus = \App\Models\SystemMenu::with(['permissions' => function($query) {
            $query->with('children')->whereNull('parent_id')->orderBy('sort_order');
        }])
        ->where('status', 'active')
        ->orderBy('sort_order')
        ->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $menus
        ]);
    }
}
