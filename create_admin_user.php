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
    echo "开始创建管理员用户...\n";

    // 检查用户是否已存在
    $existingUser = Capsule::table('users')
        ->where('email', 'admin@example.com')
        ->first();

    if ($existingUser) {
        echo "✅ 用户 admin@example.com 已存在 (ID: {$existingUser->id})\n";
        $userId = $existingUser->id;
    } else {
        // 创建管理员用户
        $userId = Capsule::table('users')->insertGetId([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✅ 创建用户: admin@example.com (ID: $userId)\n";
    }

    // 检查超级管理员角色是否存在
    $existingRole = Capsule::table('roles')
        ->where('code', 'super_admin')
        ->first();

    if ($existingRole) {
        echo "✅ 超级管理员角色已存在 (ID: {$existingRole->id})\n";
        $roleId = $existingRole->id;
    } else {
        // 创建超级管理员角色
        $roleId = Capsule::table('roles')->insertGetId([
            'name' => '超级管理员',
            'code' => 'super_admin',
            'description' => '系统超级管理员，拥有所有权限',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✅ 创建角色: 超级管理员 (ID: $roleId)\n";
    }

    // 检查用户角色关联是否存在
    $existingUserRole = Capsule::table('user_roles')
        ->where('user_id', $userId)
        ->where('role_id', $roleId)
        ->first();

    if (!$existingUserRole) {
        // 分配角色给用户
        Capsule::table('user_roles')->insert([
            'user_id' => $userId,
            'role_id' => $roleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✅ 为用户分配超级管理员角色\n";
    } else {
        echo "✅ 用户已拥有超级管理员角色\n";
    }

    // 获取所有权限并分配给角色
    $permissions = Capsule::table('permissions')->get();
    if ($permissions->count() > 0) {
        // 清除现有权限关联
        Capsule::table('role_permissions')->where('role_id', $roleId)->delete();
        
        // 重新分配所有权限
        foreach ($permissions as $permission) {
            Capsule::table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        echo "✅ 为超级管理员角色分配 {$permissions->count()} 个权限\n";
    } else {
        echo "⚠️  未找到权限数据\n";
    }

    echo "\n🎉 管理员用户创建完成！\n";
    echo "📋 登录信息：\n";
    echo "- 邮箱: admin@example.com\n";
    echo "- 密码: password\n";
    echo "- 角色: 超级管理员\n";

} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    exit(1);
}
