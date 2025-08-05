<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * 获取当前用户的权限（新模型：基于菜单）
     */
    public function permissions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        // 获取用户的所有菜单权限（通过角色）
        $menus = $user->roles()
            ->with(['menus' => function($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->pluck('menus')
            ->flatten()
            ->unique('id')
            ->values();

        // 转换为权限格式（保持前端兼容性）
        $permissions = $menus->map(function($menu) {
            return [
                'id' => $menu->id,
                'name' => $menu->name,
                'code' => $menu->code,
                'type' => 'menu',
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $permissions
        ]);
    }

    /**
     * 获取当前用户信息
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        // 加载用户角色信息
        $user->load(['roles', 'institution', 'department']);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $user
        ]);
    }
}
