<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// 数据库配置
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'english_education',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

function now() {
    return date('Y-m-d H:i:s');
}

try {
    echo "开始创建素材中心菜单...\n";

    // 检查是否已存在素材中心菜单
    $existingMenu = Capsule::table('system_menus')
        ->where('code', 'material_center')
        ->first();

    if ($existingMenu) {
        echo "❌ 素材中心菜单已存在，跳过创建\n";
        exit(0);
    }

    // 获取最大排序号
    $maxSort = Capsule::table('system_menus')
        ->whereNull('parent_id')
        ->max('sort_order') ?? 0;

    // 创建素材中心主菜单
    $materialCenterMenuId = Capsule::table('system_menus')->insertGetId([
        'name' => '素材中心',
        'code' => 'material_center',
        'path' => '/material-center',
        'icon' => 'Archive',
        'parent_id' => null,
        'sort_order' => $maxSort + 1,
        'status' => 'active',
        'description' => '故事与知识点管理模块',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "✅ 创建素材中心主菜单 (ID: $materialCenterMenuId)\n";

    // 创建对应的权限
    $materialCenterPermissionId = Capsule::table('permissions')->insertGetId([
        'name' => '素材中心',
        'code' => 'material_center',
        'type' => 'menu',
        'menu_id' => $materialCenterMenuId,
        'parent_id' => null,
        'sort_order' => $maxSort + 1,
        'status' => 'active',
        'description' => '访问素材中心的权限',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "✅ 创建素材中心权限 (ID: $materialCenterPermissionId)\n";

    // 创建子菜单
    $subMenus = [
        [
            'name' => '故事管理',
            'code' => 'story_management',
            'path' => '/material-center/stories',
            'icon' => 'BookOpen',
            'description' => '管理故事内容和章节',
        ],
        [
            'name' => '知识点管理',
            'code' => 'knowledge_point_management',
            'path' => '/material-center/knowledge-points',
            'icon' => 'Brain',
            'description' => '管理词汇、语法等知识点',
        ],
        [
            'name' => '标签管理',
            'code' => 'knowledge_tag_management',
            'path' => '/material-center/knowledge-tags',
            'icon' => 'Tags',
            'description' => '管理知识点标签体系',
        ],
    ];

    foreach ($subMenus as $index => $subMenu) {
        // 创建子菜单
        $subMenuId = Capsule::table('system_menus')->insertGetId([
            'name' => $subMenu['name'],
            'code' => $subMenu['code'],
            'path' => $subMenu['path'],
            'icon' => $subMenu['icon'],
            'parent_id' => $materialCenterMenuId,
            'sort_order' => $index + 1,
            'status' => 'active',
            'description' => $subMenu['description'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ 创建子菜单: {$subMenu['name']} (ID: $subMenuId)\n";

        // 创建对应的权限
        $subPermissionId = Capsule::table('permissions')->insertGetId([
            'name' => $subMenu['name'],
            'code' => $subMenu['code'],
            'type' => 'menu',
            'menu_id' => $subMenuId,
            'parent_id' => $materialCenterPermissionId,
            'sort_order' => $index + 1,
            'status' => 'active',
            'description' => '访问' . $subMenu['name'] . '的权限',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ 创建子权限: {$subMenu['name']} (ID: $subPermissionId)\n";
    }

    // 为超级管理员角色分配权限
    $superAdminRole = Capsule::table('roles')
        ->where('name', '超级管理员')
        ->orWhere('name', 'Super Admin')
        ->orWhere('code', 'super_admin')
        ->first();

    if ($superAdminRole) {
        // 获取所有新创建的权限ID
        $allPermissionIds = Capsule::table('permissions')
            ->where('code', 'material_center')
            ->orWhere('code', 'story_management')
            ->orWhere('code', 'knowledge_point_management')
            ->orWhere('code', 'knowledge_tag_management')
            ->pluck('id')
            ->toArray();

        foreach ($allPermissionIds as $permissionId) {
            // 检查是否已存在关联
            $exists = Capsule::table('role_permissions')
                ->where('role_id', $superAdminRole->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$exists) {
                Capsule::table('role_permissions')->insert([
                    'role_id' => $superAdminRole->id,
                    'permission_id' => $permissionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "✅ 为超级管理员角色分配素材中心权限\n";
    } else {
        echo "⚠️  未找到超级管理员角色，请手动分配权限\n";
    }

    echo "\n🎉 素材中心菜单创建完成！\n";
    echo "📊 统计信息：\n";
    echo "- 主菜单: 1个\n";
    echo "- 子菜单: " . count($subMenus) . "个\n";
    echo "- 权限: " . (count($subMenus) + 1) . "个\n";

} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    exit(1);
}
