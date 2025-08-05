<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Institution;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * 获取用户列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['institution', 'department', 'roles']);

        // 搜索过滤
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 机构过滤
        if ($request->has('institution_id') && $request->institution_id) {
            $query->where('institution_id', $request->institution_id);
        }

        // 部门过滤
        if ($request->has('department_id') && $request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        // 状态过滤
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 20));

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $users
        ]);
    }

    /**
     * 创建用户
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'institution_id' => 'required|exists:institutions,id',
            'department_id' => 'nullable|exists:departments,id',
            'role_ids' => 'array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'institution_id' => $request->institution_id,
            'department_id' => $request->department_id,
            'status' => 'active',
        ]);

        // 分配角色
        if ($request->role_ids) {
            $user->roles()->sync($request->role_ids);
        }

        return response()->json([
            'code' => 200,
            'message' => '用户创建成功',
            'data' => $user->load(['institution', 'department', 'roles'])
        ]);
    }

    /**
     * 获取用户详情
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['institution', 'department', 'roles'])->findOrFail($id);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $user
        ]);
    }

    /**
     * 更新用户
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users')->ignore($user->id)],
            'institution_id' => 'required|exists:institutions,id',
            'department_id' => 'nullable|exists:departments,id',
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'institution_id' => $request->institution_id,
            'department_id' => $request->department_id,
            'status' => $request->status,
        ]);

        return response()->json([
            'code' => 200,
            'message' => '用户更新成功',
            'data' => $user->load(['institution', 'department', 'roles'])
        ]);
    }

    /**
     * 分配用户角色
     */
    public function assignRoles(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user->roles()->sync($request->role_ids);

        return response()->json([
            'code' => 200,
            'message' => '角色分配成功',
            'data' => $user->load(['roles'])
        ]);
    }

    /**
     * 删除用户
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        // 检查是否为当前登录用户
        if ($user->id === auth()->id()) {
            return response()->json([
                'code' => 400,
                'message' => '不能删除当前登录用户'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'code' => 200,
            'message' => '用户删除成功'
        ]);
    }
}
