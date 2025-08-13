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
                'code' => 'institution',
                'path' => null,
                'icon' => 'Building2',
                'parent_id' => null,
                'sort_order' => 2,
                'status' => 'active',
                'description' => '机构和部门管理',
            ],
            [
                'name' => '教务中心',
                'code' => 'academic_center',
                'path' => null,
                'icon' => 'GraduationCap',
                'parent_id' => null,
                'sort_order' => 3,
                'status' => 'active',
                'description' => '教学管理中心',
            ],
            [
                'name' => '财务管理',
                'code' => 'finance',
                'path' => '/finance',
                'icon' => 'DollarSign',
                'parent_id' => null,
                'sort_order' => 4,
                'status' => 'active',
                'description' => '财务和收费管理',
            ],
            [
                'name' => '应用中心',
                'code' => 'app_center',
                'path' => null,
                'icon' => 'Settings',
                'parent_id' => null,
                'sort_order' => 5,
                'status' => 'active',
                'description' => '系统应用和工具',
            ],
        ];

        foreach ($menus as $menuData) {
            $menu = SystemMenu::firstOrCreate(['code' => $menuData['code']], $menuData);

            // 为每个菜单创建对应的权限
            $this->createMenuPermissions($menu);
        }

        // 创建二级菜单

        // 机构管理下的子菜单
        $institutionMenu = SystemMenu::where('code', 'institution')->first();
        if ($institutionMenu) {
            $institutionSubMenus = [
                [
                    'name' => '组织架构',
                    'code' => 'organization',
                    'path' => '/institution/organization',
                    'icon' => 'Building2',
                    'parent_id' => $institutionMenu->id,
                    'sort_order' => 1,
                    'status' => 'active',
                    'description' => '机构和部门管理',
                ],
                [
                    'name' => '账户管理',
                    'code' => 'account',
                    'path' => '/institution/accounts',
                    'icon' => 'Users',
                    'parent_id' => $institutionMenu->id,
                    'sort_order' => 2,
                    'status' => 'active',
                    'description' => '用户和角色管理',
                ],
            ];

            foreach ($institutionSubMenus as $subMenuData) {
                $subMenu = SystemMenu::firstOrCreate(['code' => $subMenuData['code']], $subMenuData);
                $this->createMenuPermissions($subMenu);
            }
        }

        // 教务中心下的子菜单
        $academicMenu = SystemMenu::where('code', 'academic_center')->first();
        if ($academicMenu) {
            $academicSubMenus = [
                [
                    'name' => '学员管理',
                    'code' => 'student_management',
                    'path' => '/academic/students',
                    'icon' => 'User',
                    'parent_id' => $academicMenu->id,
                    'sort_order' => 1,
                    'status' => 'active',
                    'description' => '学员信息管理',
                ],
                [
                    'name' => '课程管理',
                    'code' => 'course_management',
                    'path' => '/academic/courses',
                    'icon' => 'BookOpen',
                    'parent_id' => $academicMenu->id,
                    'sort_order' => 2,
                    'status' => 'active',
                    'description' => '课程和班级管理',
                ],
                [
                    'name' => '班级管理',
                    'code' => 'class_management',
                    'path' => '/academic/classes',
                    'icon' => 'Users',
                    'parent_id' => $academicMenu->id,
                    'sort_order' => 3,
                    'status' => 'active',
                    'description' => '班级信息管理',
                ],
                [
                    'name' => '课表管理',
                    'code' => 'schedule_management',
                    'path' => '/academic/schedules',
                    'icon' => 'Calendar',
                    'parent_id' => $academicMenu->id,
                    'sort_order' => 4,
                    'status' => 'active',
                    'description' => '课程时间安排',
                ],
            ];

            foreach ($academicSubMenus as $subMenuData) {
                $subMenu = SystemMenu::firstOrCreate(['code' => $subMenuData['code']], $subMenuData);
                $this->createMenuPermissions($subMenu);
            }
        }

        // 应用中心下的子菜单
        $appCenterMenu = SystemMenu::where('code', 'app_center')->first();
        if ($appCenterMenu) {
            $appSubMenus = [
                [
                    'name' => '菜单管理',
                    'code' => 'menu_management',
                    'path' => '/apps/menu',
                    'icon' => 'Settings',
                    'parent_id' => $appCenterMenu->id,
                    'sort_order' => 1,
                    'status' => 'active',
                    'description' => '系统菜单管理',
                ],
            ];

            foreach ($appSubMenus as $subMenuData) {
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
