<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\SystemMenu;
use Illuminate\Support\Facades\DB;

class MenuBasedPermissionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // 清空现有的角色菜单关联
        DB::table('role_menus')->truncate();

        echo "开始分配角色菜单权限...\n";

        // 获取所有角色
        $superAdminRole = Role::where('code', 'super_admin')->first();
        $principalRole = Role::where('code', 'principal')->first();
        $teacherRole = Role::where('code', 'teacher')->first();
        $parentRole = Role::where('code', 'parent')->first();

        if (!$superAdminRole || !$principalRole || !$teacherRole || !$parentRole) {
            echo "角色不存在，请先运行角色种子数据\n";
            return;
        }

        // 获取所有菜单
        $allMenus = SystemMenu::all();

        // 超级管理员：拥有所有菜单权限
        $this->assignMenusToRole($superAdminRole, $allMenus);
        echo "超级管理员权限分配完成！\n";

        // 校长：除了应用中心外的所有菜单（包括素材中心）
        $principalMenus = $allMenus->filter(function ($menu) {
            // 排除应用中心及其子菜单
            if ($menu->code === 'app_center') {
                return false;
            }
            // 排除应用中心的子菜单
            if ($menu->parent_id) {
                $parentMenu = SystemMenu::find($menu->parent_id);
                if ($parentMenu && $parentMenu->code === 'app_center') {
                    return false;
                }
            }
            return true;
        });
        $this->assignMenusToRole($principalRole, $principalMenus);
        echo "校长权限分配完成！\n";

        // 老师：教务中心、家校互动和素材中心相关菜单
        $teacherMenuCodes = [
            'dashboard',                        // 中心首页
            'academic_center',                  // 教务中心（父菜单）
            'student_management',               // 学员管理
            'class_management',                 // 班级管理
            'teacher_management',               // 教师管理
            'class_records',                    // 上课记录
            'family_school',                    // 家校互动（父菜单）
            'homework',                         // 课后作业
            'class_review',                     // 课后点评
            'report_card',                      // 成绩单
            'growth_record',                    // 成长档案
            'material_center',                    // 素材中心（父菜单）
            'material_center.stories',            // 故事管理
            'material_center.knowledge_points',   // 知识点管理
            'material_center.knowledge_tags',     // 知识标签
        ];

        $teacherMenus = $allMenus->whereIn('code', $teacherMenuCodes);
        $this->assignMenusToRole($teacherRole, $teacherMenus);
        echo "老师权限分配完成！\n";

        // 家长：只有家校互动相关菜单
        $parentMenuCodes = [
            'dashboard',                            // 中心首页
            'family_school',                        // 家校互动（父菜单）
            'homework',                             // 课后作业
            'class_review',                         // 课后点评
            'report_card',                          // 成绩单
            'growth_record',                        // 成长档案
        ];

        $parentMenus = $allMenus->whereIn('code', $parentMenuCodes);
        $this->assignMenusToRole($parentRole, $parentMenus);
        echo "家长权限分配完成！\n";

        echo "角色菜单权限分配完成！\n";
    }

    /**
     * 为角色分配菜单权限
     */
    private function assignMenusToRole(Role $role, $menus): void
    {
        $menuIds = $menus->pluck('id')->toArray();
        $role->menus()->sync($menuIds);
    }
}
