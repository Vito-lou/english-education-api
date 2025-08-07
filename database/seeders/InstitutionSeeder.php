<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\Department;
use App\Models\Role;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // 创建示例机构
        $institution = Institution::create([
            'name' => '英语教育培训中心',
            'code' => 'EETC001',
            'description' => '专业的英语教育培训机构',
            'contact_person' => '张校长',
            'contact_phone' => '13800138000',
            'contact_email' => 'admin@eetc.com',
            'address' => '北京市朝阳区教育大厦',
            'business_hours' => [
                'monday' => ['09:00', '21:00'],
                'tuesday' => ['09:00', '21:00'],
                'wednesday' => ['09:00', '21:00'],
                'thursday' => ['09:00', '21:00'],
                'friday' => ['09:00', '21:00'],
                'saturday' => ['09:00', '18:00'],
                'sunday' => ['09:00', '18:00'],
            ],
            'settings' => [
                'max_class_size' => 12,
                'booking_advance_days' => 7,
                'cancellation_hours' => 24,
            ],
            'status' => 'active',
            'established_at' => '2020-01-01',
        ]);

        // 创建校区
        $mainCampus = Department::create([
            'institution_id' => $institution->id,
            'name' => '朝阳校区',
            'code' => 'CAMPUS_CY',
            'type' => 'campus',
            'description' => '主校区',
            'manager_name' => '李主任',
            'manager_phone' => '13800138001',
            'address' => '北京市朝阳区教育大厦1-3层',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        // 创建部门
        $teachingDept = Department::create([
            'institution_id' => $institution->id,
            'parent_id' => $mainCampus->id,
            'name' => '教学部',
            'code' => 'DEPT_TEACH',
            'type' => 'department',
            'description' => '负责教学管理',
            'manager_name' => '王老师',
            'manager_phone' => '13800138002',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $salesDept = Department::create([
            'institution_id' => $institution->id,
            'parent_id' => $mainCampus->id,
            'name' => '销售部',
            'code' => 'DEPT_SALES',
            'type' => 'department',
            'description' => '负责招生和销售',
            'manager_name' => '赵经理',
            'manager_phone' => '13800138003',
            'sort_order' => 2,
            'status' => 'active',
        ]);



        // 创建角色
        $roles = [
            [
                'name' => '超级管理员',
                'code' => 'super_admin',
                'description' => '系统超级管理员，拥有所有权限',
                'type' => 'system',
                'permissions' => ['*'],
                'data_scope' => ['all'],
                'sort_order' => 1,
            ],
            [
                'name' => '校长',
                'code' => 'principal',
                'description' => '校长，负责整体管理',
                'type' => 'system',
                'permissions' => [
                    'institutions.*', 'departments.*', 'users.*',
                    'students.*', 'teachers.*', 'finance.*'
                ],
                'data_scope' => ['institution'],
                'sort_order' => 2,
            ],
            [
                'name' => '教务主任',
                'code' => 'academic_director',
                'description' => '教务主任，负责教学管理',
                'type' => 'system',
                'permissions' => [
                    'students.*', 'classes.*', 'teachers.view',
                    'schedules.*', 'attendance.*'
                ],
                'data_scope' => ['department'],
                'sort_order' => 3,
            ],
            [
                'name' => '老师',
                'code' => 'teacher',
                'description' => '任课老师',
                'type' => 'system',
                'permissions' => [
                    'classes.view', 'students.view', 'attendance.*',
                    'homework.*', 'grades.*'
                ],
                'data_scope' => ['own_classes'],
                'sort_order' => 4,
            ],
            [
                'name' => '销售',
                'code' => 'sales',
                'description' => '销售人员',
                'type' => 'system',
                'permissions' => [
                    'leads.*', 'students.create', 'students.view',
                    'contracts.*', 'payments.view'
                ],
                'data_scope' => ['own_data'],
                'sort_order' => 5,
            ],
            [
                'name' => '财务',
                'code' => 'finance',
                'description' => '财务人员',
                'type' => 'system',
                'permissions' => [
                    'finance.*', 'payments.*', 'refunds.*',
                    'reports.finance', 'students.view'
                ],
                'data_scope' => ['institution'],
                'sort_order' => 6,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::create(array_merge($roleData, [
                'institution_id' => $institution->id,
                'status' => 'active',
            ]));
        }
    }
}
