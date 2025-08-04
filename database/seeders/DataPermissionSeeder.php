<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataPermissions = [
            // 学员数据权限
            ['name' => '学员数据-全部', 'code' => 'student:all', 'resource_type' => 'student', 'scope_type' => 'all', 'description' => '可以查看所有学员数据', 'sort_order' => 1],
            ['name' => '学员数据-部分', 'code' => 'student:partial', 'resource_type' => 'student', 'scope_type' => 'partial', 'description' => '只能查看分配给自己的学员', 'sort_order' => 2],

            // 班级数据权限
            ['name' => '班级数据-全部', 'code' => 'class:all', 'resource_type' => 'class', 'scope_type' => 'all', 'description' => '可以查看所有班级数据', 'sort_order' => 3],
            ['name' => '班级数据-部分', 'code' => 'class:partial', 'resource_type' => 'class', 'scope_type' => 'partial', 'description' => '只能查看自己负责的班级', 'sort_order' => 4],

            // 课表数据权限
            ['name' => '课表数据-全部', 'code' => 'schedule:all', 'resource_type' => 'schedule', 'scope_type' => 'all', 'description' => '可以查看所有课表', 'sort_order' => 5],
            ['name' => '课表数据-部分', 'code' => 'schedule:partial', 'resource_type' => 'schedule', 'scope_type' => 'partial', 'description' => '只能查看自己的课表', 'sort_order' => 6],

            // 上课记录数据权限
            ['name' => '上课记录-全部', 'code' => 'lesson:all', 'resource_type' => 'lesson', 'scope_type' => 'all', 'description' => '可以查看所有上课记录', 'sort_order' => 7],
            ['name' => '上课记录-部分', 'code' => 'lesson:partial', 'resource_type' => 'lesson', 'scope_type' => 'partial', 'description' => '只能查看相关的上课记录', 'sort_order' => 8],

            // 缺课补课数据权限
            ['name' => '缺课补课-全部', 'code' => 'makeup:all', 'resource_type' => 'makeup', 'scope_type' => 'all', 'description' => '可以查看所有缺课补课记录', 'sort_order' => 9],
            ['name' => '缺课补课-部分', 'code' => 'makeup:partial', 'resource_type' => 'makeup', 'scope_type' => 'partial', 'description' => '只能查看负责学员的缺课补课', 'sort_order' => 10],
        ];

        foreach ($dataPermissions as $permission) {
            \App\Models\DataPermission::create($permission);
        }
    }
}
