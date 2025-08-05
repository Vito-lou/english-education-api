<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemMenu;
use App\Models\Permission;
use App\Models\Role;

class CompetitorMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 清理现有菜单和权限
        \DB::table('role_permissions')->delete();
        Permission::query()->delete();
        SystemMenu::query()->delete();

        // 创建一级菜单
        $mainMenus = [
            [
                'name' => '中心首页',
                'code' => 'dashboard',
                'path' => '/dashboard',
                'icon' => 'LayoutDashboard',
                'sort_order' => 1,
                'description' => '系统首页和数据概览',
            ],
            [
                'name' => '教务中心',
                'code' => 'academic_center',
                'path' => '/academic',
                'icon' => 'GraduationCap',
                'sort_order' => 2,
                'description' => '教学管理核心功能',
            ],
            [
                'name' => '家校互动',
                'code' => 'family_school',
                'path' => '/family-school',
                'icon' => 'Users',
                'sort_order' => 3,
                'description' => '家长学校沟通平台',
            ],
            [
                'name' => '营销中心',
                'code' => 'marketing_center',
                'path' => '/marketing',
                'icon' => 'BarChart3',
                'sort_order' => 4,
                'description' => '招生推广管理',
            ],
            [
                'name' => '财务中心',
                'code' => 'finance_center',
                'path' => '/finance',
                'icon' => 'DollarSign',
                'sort_order' => 5,
                'description' => '财务收费管理',
            ],
            [
                'name' => '机构设置',
                'code' => 'institution_settings',
                'path' => '/institution',
                'icon' => 'Settings',
                'sort_order' => 6,
                'description' => '机构配置管理',
            ],
            [
                'name' => '应用中心',
                'code' => 'app_center',
                'path' => '/apps',
                'icon' => 'FileText',
                'sort_order' => 7,
                'description' => '扩展应用功能',
            ],
        ];

        $createdMenus = [];
        foreach ($mainMenus as $menuData) {
            $menu = SystemMenu::create([
                'name' => $menuData['name'],
                'code' => $menuData['code'],
                'path' => $menuData['path'],
                'icon' => $menuData['icon'],
                'parent_id' => null,
                'sort_order' => $menuData['sort_order'],
                'status' => 'active',
                'description' => $menuData['description'],
            ]);
            $createdMenus[$menuData['code']] = $menu;
        }

        // 创建教务中心的二级菜单
        $academicSubMenus = [
            ['name' => '学员管理', 'code' => 'student_management', 'path' => '/academic/students'],
            ['name' => '班级管理', 'code' => 'class_management', 'path' => '/academic/classes'],
            ['name' => '课表管理', 'code' => 'schedule_management', 'path' => '/academic/schedules'],
            ['name' => '教师管理', 'code' => 'teacher_management', 'path' => '/academic/teachers'],
            ['name' => '上课记录', 'code' => 'class_records', 'path' => '/academic/records'],
        ];
        $this->createSubMenus($createdMenus['academic_center'], $academicSubMenus);

        // 创建家校互动的二级菜单
        $familySchoolSubMenus = [
            ['name' => '课后作业', 'code' => 'homework', 'path' => '/family-school/homework'],
            ['name' => '课后点评', 'code' => 'class_review', 'path' => '/family-school/reviews'],
            ['name' => '成绩单', 'code' => 'report_card', 'path' => '/family-school/reports'],
            ['name' => '成长档案', 'code' => 'growth_record', 'path' => '/family-school/growth'],
        ];
        $this->createSubMenus($createdMenus['family_school'], $familySchoolSubMenus);

        // 创建机构设置的二级菜单
        $institutionSubMenus = [
            ['name' => '组织架构', 'code' => 'organization', 'path' => '/institution/organization'],
            ['name' => '账户管理', 'code' => 'account_management', 'path' => '/institution/accounts'],
            ['name' => '机构展示', 'code' => 'institution_display', 'path' => '/institution/display'],
        ];
        $this->createSubMenus($createdMenus['institution_settings'], $institutionSubMenus);

        // 创建应用中心的二级菜单
        $appCenterSubMenus = [
            ['name' => '菜单管理', 'code' => 'menu_management', 'path' => '/apps/menu'],
        ];
        $this->createSubMenus($createdMenus['app_center'], $appCenterSubMenus);

        // 为所有叶子菜单创建权限
        $this->createPermissionsForLeafMenus();

        // 重新分配角色权限
        $this->assignRolePermissions();

        echo "竞品菜单结构创建完成！\n";
    }

    private function createSubMenus(SystemMenu $parentMenu, array $subMenus): void
    {
        foreach ($subMenus as $index => $subMenuData) {
            SystemMenu::create([
                'name' => $subMenuData['name'],
                'code' => $subMenuData['code'],
                'path' => $subMenuData['path'],
                'icon' => 'FileText',
                'parent_id' => $parentMenu->id,
                'sort_order' => $index + 1,
                'status' => 'active',
                'description' => $subMenuData['name'] . '功能',
            ]);
        }
    }

    private function createPermissionsForLeafMenus(): void
    {
        // 获取所有叶子菜单（没有子菜单的菜单）
        $allMenus = SystemMenu::all();

        foreach ($allMenus as $menu) {
            $hasChildren = $allMenus->where('parent_id', $menu->id)->count() > 0;

            if (!$hasChildren) {
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
        }
    }

    private function assignRolePermissions(): void
    {
        // 超级管理员：拥有所有权限
        $superAdminRole = Role::where('code', 'super_admin')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::all();
            $superAdminRole->permissions()->sync($allPermissions->pluck('id'));
        }

        // 校长：拥有大部分权限（除了应用中心和菜单管理）
        $principalRole = Role::where('code', 'principal')->first();
        if ($principalRole) {
            $principalPermissions = Permission::whereNotIn('code', ['app_center', 'menu_management'])->get();
            $principalRole->permissions()->sync($principalPermissions->pluck('id'));
        }

        // 老师：只有教务中心和家校互动权限
        $teacherRole = Role::where('code', 'teacher')->first();
        if ($teacherRole) {
            $teacherPermissions = Permission::whereIn('code', [
                'dashboard',
                'academic_center',
                'student_management',
                'class_management',
                'schedule_management',
                'class_records',
                'family_school',
                'homework',
                'class_review',
                'report_card',
                'growth_record'
            ])->get();
            $teacherRole->permissions()->sync($teacherPermissions->pluck('id'));
        }

        echo "角色权限分配完成！\n";
    }
}
