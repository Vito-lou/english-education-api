<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemMenu;
use App\Models\Permission;
use App\Models\Role;

class SimpleMenuPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 清理现有的复杂权限，重新创建简单权限
        // 先清理关联关系
        \DB::table('role_permissions')->delete();
        Permission::query()->delete();

        // 获取所有菜单
        $menus = SystemMenu::all();

        foreach ($menus as $menu) {
            // 每个菜单只创建一个权限
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
        }

        // 重新分配角色权限（简化版）
        $this->assignSimpleRolePermissions();

        echo "简化权限创建完成！\n";
    }

    private function assignSimpleRolePermissions(): void
    {
        // 超级管理员：拥有所有菜单权限
        $superAdminRole = Role::where('code', 'super_admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::all();
            $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
        }

        // 校长：拥有大部分菜单权限
        $principalRole = Role::where('code', 'principal')->first();
        if ($principalRole) {
            $principalPermissions = Permission::whereIn('code', [
                'dashboard',
                'organization',
                'account',
                'user_management',
                'role_management',
                'student',
                'course',
                'finance'
            ])->get();
            $principalRole->permissions()->sync($principalPermissions->pluck('id'));
        }

        // 老师：只有基础菜单权限
        $teacherRole = Role::where('code', 'teacher')->first();
        if ($teacherRole) {
            $teacherPermissions = Permission::whereIn('code', [
                'dashboard',
                'student',
                'course'
            ])->get();
            $teacherRole->permissions()->sync($teacherPermissions->pluck('id'));
        }

        echo "角色权限分配完成！\n";
    }
}
