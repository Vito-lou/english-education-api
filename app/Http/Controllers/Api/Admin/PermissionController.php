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
        $permissions = Permission::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

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
}
