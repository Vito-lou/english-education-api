<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// 数据库配置
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'english_education',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // 创建机构
    $institutionId = Capsule::table('institutions')->insertGetId([
        'name' => '英语教育培训中心',
        'code' => 'EETC001',
        'description' => '专业的英语教育培训机构，致力于提供高质量的英语教学服务',
        'contact_person' => '张校长',
        'contact_phone' => '13800138000',
        'contact_email' => 'admin@eetc.com',
        'address' => '北京市朝阳区教育大厦1-3层',
        'business_license' => 'BL123456789',
        'business_hours' => json_encode([
            'monday' => ['09:00', '21:00'],
            'tuesday' => ['09:00', '21:00'],
            'wednesday' => ['09:00', '21:00'],
            'thursday' => ['09:00', '21:00'],
            'friday' => ['09:00', '21:00'],
            'saturday' => ['09:00', '18:00'],
            'sunday' => ['09:00', '18:00'],
        ]),
        'settings' => json_encode([
            'max_class_size' => 12,
            'booking_advance_days' => 7,
            'cancellation_hours' => 24,
        ]),
        'status' => 'active',
        'established_at' => '2020-01-01',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "机构创建成功，ID: $institutionId\n";

    // 创建朝阳校区
    $chaoyangCampusId = Capsule::table('departments')->insertGetId([
        'institution_id' => $institutionId,
        'parent_id' => null,
        'name' => '朝阳校区',
        'code' => 'CAMPUS_CY',
        'type' => 'campus',
        'description' => '主校区，位于朝阳区教育大厦',
        'manager_name' => '李主任',
        'manager_phone' => '13800138001',
        'address' => '北京市朝阳区教育大厦1-3层',
        'sort_order' => 1,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "朝阳校区创建成功，ID: $chaoyangCampusId\n";

    // 创建教学部
    $teachingDeptId = Capsule::table('departments')->insertGetId([
        'institution_id' => $institutionId,
        'parent_id' => $chaoyangCampusId,
        'name' => '教学部',
        'code' => 'DEPT_TEACH',
        'type' => 'department',
        'description' => '负责教学管理和课程安排',
        'manager_name' => '王老师',
        'manager_phone' => '13800138002',
        'sort_order' => 1,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "教学部创建成功，ID: $teachingDeptId\n";

    // 创建教室A
    $roomAId = Capsule::table('departments')->insertGetId([
        'institution_id' => $institutionId,
        'parent_id' => $teachingDeptId,
        'name' => '教室A',
        'code' => 'ROOM_A',
        'type' => 'classroom',
        'description' => '多媒体教室，适合小班教学',
        'capacity' => 12,
        'facilities' => json_encode(['投影仪', '白板', '音响', '空调']),
        'sort_order' => 1,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "教室A创建成功，ID: $roomAId\n";

    // 创建教室B
    $roomBId = Capsule::table('departments')->insertGetId([
        'institution_id' => $institutionId,
        'parent_id' => $teachingDeptId,
        'name' => '教室B',
        'code' => 'ROOM_B',
        'type' => 'classroom',
        'description' => '标准教室，适合中班教学',
        'capacity' => 16,
        'facilities' => json_encode(['投影仪', '白板', '音响']),
        'sort_order' => 2,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "教室B创建成功，ID: $roomBId\n";

    // 创建销售部
    $salesDeptId = Capsule::table('departments')->insertGetId([
        'institution_id' => $institutionId,
        'parent_id' => $chaoyangCampusId,
        'name' => '销售部',
        'code' => 'DEPT_SALES',
        'type' => 'department',
        'description' => '负责招生和客户服务',
        'manager_name' => '赵经理',
        'manager_phone' => '13800138003',
        'sort_order' => 2,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "销售部创建成功，ID: $salesDeptId\n";

    // 创建海淀校区
    $haidianCampusId = Capsule::table('departments')->insertGetId([
        'institution_id' => $institutionId,
        'parent_id' => null,
        'name' => '海淀校区',
        'code' => 'CAMPUS_HD',
        'type' => 'campus',
        'description' => '分校区，位于海淀区',
        'manager_name' => '陈主任',
        'manager_phone' => '13800138004',
        'address' => '北京市海淀区学院路',
        'sort_order' => 2,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "海淀校区创建成功，ID: $haidianCampusId\n";

    // 创建海淀教学部
    $haidianTeachingDeptId = Capsule::table('departments')->insertGetId([
        'institution_id' => $institutionId,
        'parent_id' => $haidianCampusId,
        'name' => '教学部',
        'code' => 'DEPT_TEACH_HD',
        'type' => 'department',
        'description' => '海淀校区教学部',
        'manager_name' => '刘老师',
        'manager_phone' => '13800138005',
        'sort_order' => 1,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "海淀教学部创建成功，ID: $haidianTeachingDeptId\n";

    // 更新测试用户，分配到机构
    $updated = Capsule::table('users')
        ->where('email', 'admin@example.com')
        ->update([
            'institution_id' => $institutionId,
            'department_id' => $teachingDeptId,
            'updated_at' => now(),
        ]);

    if ($updated) {
        echo "测试用户更新成功，已分配到机构\n";
    } else {
        echo "测试用户更新失败或用户不存在\n";
    }

    echo "所有测试数据创建完成！\n";

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

function now() {
    return date('Y-m-d H:i:s');
}
