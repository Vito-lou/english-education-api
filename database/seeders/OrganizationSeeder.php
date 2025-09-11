<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\Department;
use App\Models\User;

class OrganizationSeeder extends Seeder
{
    public function run()
    {
        // 创建机构
        $institution = Institution::create([
            'name' => '星云英语',
            'code' => 'EETC001',
            'description' => '专业的英语教育培训机构，致力于提供高质量的英语教学服务',
            'contact_person' => '张校长',
            'contact_phone' => '13800138000',
            'contact_email' => 'admin@eetc.com',
            'address' => '江苏省宿迁市泗洪县教育大厦1-3层',
            'business_license' => 'BL123456789',
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

        // 创建泗洪校区
        $chaoyangCampus = Department::create([
            'institution_id' => $institution->id,
            'name' => '泗洪校区',
            'code' => 'CAMPUS_SH',
            'type' => 'campus',
            'description' => '主校区，位于泗洪县教育大厦',
            'manager_name' => '李主任',
            'manager_phone' => '13800138001',
            'address' => '江苏省宿迁市泗洪县教育大厦1-3层',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        // 创建教学部
        $teachingDept = Department::create([
            'institution_id' => $institution->id,
            'parent_id' => $chaoyangCampus->id,
            'name' => '教学部',
            'code' => 'DEPT_TEACH',
            'type' => 'department',
            'description' => '负责教学管理和课程安排',
            'manager_name' => '王老师',
            'manager_phone' => '13800138002',
            'sort_order' => 1,
            'status' => 'active',
        ]);



        // 创建销售部
        Department::create([
            'institution_id' => $institution->id,
            'parent_id' => $chaoyangCampus->id,
            'name' => '销售部',
            'code' => 'DEPT_SALES',
            'type' => 'department',
            'description' => '负责招生和客户服务',
            'manager_name' => '赵经理',
            'manager_phone' => '13800138003',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        // 创建江宁校区
        $haidianCampus = Department::create([
            'institution_id' => $institution->id,
            'name' => '江宁校区',
            'code' => 'CAMPUS_JN',
            'type' => 'campus',
            'description' => '分校区，位于江宁区',
            'manager_name' => '陈主任',
            'manager_phone' => '13800138004',
            'address' => '江苏省南京市江宁区学院路',
            'sort_order' => 2,
            'status' => 'active',
        ]);

        // 创建江宁教学部
        Department::create([
            'institution_id' => $institution->id,
            'parent_id' => $haidianCampus->id,
            'name' => '教学部',
            'code' => 'DEPT_TEACH_JN',
            'type' => 'department',
            'description' => '江宁校区教学部',
            'manager_name' => '刘老师',
            'manager_phone' => '13800138005',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        // 创建测试用户
        User::factory()->create([
            'name' => '测试用户',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'institution_id' => $institution->id,
            'department_id' => $teachingDept->id,
            'status' => 'active',
        ]);

        // 创建管理员用户
        User::factory()->create([
            'name' => '系统管理员',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'institution_id' => $institution->id,
            'department_id' => $teachingDept->id,
            'status' => 'active',
        ]);

        // 创建教师用户
        User::factory()->create([
            'name' => 'vito',
            'email' => 'vito@example.com',
            'password' => bcrypt('password'),
            'institution_id' => $institution->id,
            'department_id' => $teachingDept->id,
            'status' => 'active',
        ]);

        $this->command->info('组织架构数据创建完成！');
    }
}
