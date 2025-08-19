<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Institution;

class BasicDataSeeder extends Seeder
{
    public function run(): void
    {
        // 创建基础权限
        $permissions = [
            ['name' => '用户管理', 'code' => 'user_management', 'type' => 'menu', 'parent_id' => null, 'sort_order' => 1],
            ['name' => '查看用户', 'code' => 'user_view', 'type' => 'button', 'parent_id' => 1, 'sort_order' => 1],
            ['name' => '新增用户', 'code' => 'user_create', 'type' => 'button', 'parent_id' => 1, 'sort_order' => 2],
            ['name' => '编辑用户', 'code' => 'user_edit', 'type' => 'button', 'parent_id' => 1, 'sort_order' => 3],
            ['name' => '删除用户', 'code' => 'user_delete', 'type' => 'button', 'parent_id' => 1, 'sort_order' => 4],

            ['name' => '学员管理', 'code' => 'student_management', 'type' => 'menu', 'parent_id' => null, 'sort_order' => 2],
            ['name' => '查看学员', 'code' => 'student_view', 'type' => 'button', 'parent_id' => 6, 'sort_order' => 1],
            ['name' => '新增学员', 'code' => 'student_create', 'type' => 'button', 'parent_id' => 6, 'sort_order' => 2],
            ['name' => '编辑学员', 'code' => 'student_edit', 'type' => 'button', 'parent_id' => 6, 'sort_order' => 3],

            ['name' => '财务管理', 'code' => 'finance_management', 'type' => 'menu', 'parent_id' => null, 'sort_order' => 3],
            ['name' => '查看财务', 'code' => 'finance_view', 'type' => 'button', 'parent_id' => 10, 'sort_order' => 1],
            ['name' => '财务统计', 'code' => 'finance_stats', 'type' => 'button', 'parent_id' => 10, 'sort_order' => 2],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['code' => $permission['code']], $permission);
        }

        // 获取已创建的机构
        $institution = Institution::where('code', 'EETC001')->first();
        if (!$institution) {
            throw new \Exception('机构不存在，请先运行 OrganizationSeeder');
        }

        // 创建基础角色
        $roles = [
            [
                'name' => '超级管理员',
                'code' => 'super_admin',
                'description' => '拥有系统所有权限',
                'institution_id' => null,
                'is_system' => true,
                'status' => 'active',
            ],
            [
                'name' => '校长',
                'code' => 'principal',
                'description' => '学校管理者',
                'institution_id' => $institution->id,
                'is_system' => false,
                'status' => 'active',
            ],
            [
                'name' => '老师',
                'code' => 'teacher',
                'description' => '负责教学工作',
                'institution_id' => $institution->id,
                'is_system' => false,
                'status' => 'active',
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(['code' => $roleData['code']], $roleData);

            // 为超级管理员分配所有权限
            if ($role->code === 'super_admin') {
                $allPermissions = Permission::all();
                $role->permissions()->sync($allPermissions->pluck('id'));
            }
            // 为老师分配基础权限
            elseif ($role->code === 'teacher') {
                $teacherPermissions = Permission::whereIn('code', [
                    'student_view', 'student_create', 'student_edit'
                ])->get();
                $role->permissions()->sync($teacherPermissions->pluck('id'));
            }
        }

        // 为已创建的用户分配角色
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $superAdminRole = Role::where('code', 'super_admin')->first();
            if ($superAdminRole) {
                $adminUser->roles()->sync([$superAdminRole->id]);
            }
        }

        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser) {
            $teacherRole = Role::where('code', 'teacher')->first();
            if ($teacherRole) {
                $testUser->roles()->sync([$teacherRole->id]);
            }
        }

        $vitoUser = User::where('email', 'vito@example.com')->first();
        if ($vitoUser) {
            $teacherRole = Role::where('code', 'teacher')->first();
            if ($teacherRole) {
                $vitoUser->roles()->syncWithoutDetaching([$teacherRole->id]);
            }
        }

        echo "基础数据创建完成！\n";
    }
}
