<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemMenu;
use App\Models\Permission;

class SystemMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 创建系统菜单
        $menus = [
            // 一级菜单
            [
                'name' => '仪表盘',
                'code' => 'dashboard',
                'path' => '/dashboard',
                'icon' => 'LayoutDashboard',
                'parent_id' => null,
                'sort_order' => 1,
                'status' => 'active',
                'description' => '系统仪表盘',
            ],
            [
                'name' => '机构管理',
                'code' => 'organization',
                'path' => '/organization',
                'icon' => 'Building2',
                'parent_id' => null,
                'sort_order' => 2,
                'status' => 'active',
                'description' => '机构和部门管理',
            ],
            [
                'name' => '账户管理',
                'code' => 'account',
                'path' => '/account',
                'icon' => 'Users',
                'parent_id' => null,
                'sort_order' => 3,
                'status' => 'active',
                'description' => '用户和角色管理',
            ],
            [
                'name' => '学员管理',
                'code' => 'student',
                'path' => '/student',
                'icon' => 'GraduationCap',
                'parent_id' => null,
                'sort_order' => 4,
                'status' => 'active',
                'description' => '学员信息管理',
            ],
            [
                'name' => '课程管理',
                'code' => 'course',
                'path' => '/course',
                'icon' => 'BookOpen',
                'parent_id' => null,
                'sort_order' => 5,
                'status' => 'active',
                'description' => '课程和班级管理',
            ],
            [
                'name' => '财务管理',
                'code' => 'finance',
                'path' => '/finance',
                'icon' => 'DollarSign',
                'parent_id' => null,
                'sort_order' => 6,
                'status' => 'active',
                'description' => '财务和收费管理',
            ],
            [
                'name' => '系统设置',
                'code' => 'system',
                'path' => '/system',
                'icon' => 'Settings',
                'parent_id' => null,
                'sort_order' => 7,
                'status' => 'active',
                'description' => '系统配置和设置',
            ],
        ];

        foreach ($menus as $menuData) {
            $menu = SystemMenu::firstOrCreate(['code' => $menuData['code']], $menuData);
            
            // 为每个菜单创建对应的权限
            $this->createMenuPermissions($menu);
        }

        // 创建二级菜单（账户管理的子菜单）
        $accountMenu = SystemMenu::where('code', 'account')->first();
        if ($accountMenu) {
            $subMenus = [
                [
                    'name' => '用户管理',
                    'code' => 'user_management',
                    'path' => '/account/users',
                    'icon' => 'User',
                    'parent_id' => $accountMenu->id,
                    'sort_order' => 1,
                    'status' => 'active',
                    'description' => '用户账户管理',
                ],
                [
                    'name' => '角色管理',
                    'code' => 'role_management',
                    'path' => '/account/roles',
                    'icon' => 'Shield',
                    'parent_id' => $accountMenu->id,
                    'sort_order' => 2,
                    'status' => 'active',
                    'description' => '角色权限管理',
                ],
            ];

            foreach ($subMenus as $subMenuData) {
                $subMenu = SystemMenu::firstOrCreate(['code' => $subMenuData['code']], $subMenuData);
                $this->createMenuPermissions($subMenu);
            }
        }

        echo "系统菜单创建完成！\n";
    }

    /**
     * 为菜单创建对应的权限
     */
    private function createMenuPermissions(SystemMenu $menu): void
    {
        // 菜单权限（访问权限）
        $menuPermission = Permission::firstOrCreate(
            ['code' => $menu->code . '_access'],
            [
                'name' => $menu->name . '访问',
                'code' => $menu->code . '_access',
                'type' => 'menu',
                'menu_id' => $menu->id,
                'parent_id' => null,
                'sort_order' => 1,
                'status' => 'active',
                'description' => '访问' . $menu->name . '菜单的权限',
            ]
        );

        // 如果是叶子菜单，创建操作权限
        if ($menu->isLeaf()) {
            $actions = [
                ['name' => '查看', 'code' => 'view', 'type' => 'button'],
                ['name' => '新增', 'code' => 'create', 'type' => 'button'],
                ['name' => '编辑', 'code' => 'edit', 'type' => 'button'],
                ['name' => '删除', 'code' => 'delete', 'type' => 'button'],
            ];

            foreach ($actions as $index => $action) {
                Permission::firstOrCreate(
                    ['code' => $menu->code . '_' . $action['code']],
                    [
                        'name' => $menu->name . $action['name'],
                        'code' => $menu->code . '_' . $action['code'],
                        'type' => $action['type'],
                        'menu_id' => $menu->id,
                        'parent_id' => $menuPermission->id,
                        'sort_order' => $index + 1,
                        'status' => 'active',
                        'description' => $menu->name . $action['name'] . '权限',
                    ]
                );
            }
        }
    }
}
