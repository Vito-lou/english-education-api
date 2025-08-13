<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "重置用户密码脚本\n";
echo "==================\n\n";

// 重置admin用户密码
$adminUser = \App\Models\User::where('email', 'admin@example.com')->first();
if ($adminUser) {
    $adminUser->password = \Illuminate\Support\Facades\Hash::make('admin123');
    $adminUser->status = 'active';
    $adminUser->save();
    echo "✅ Admin用户密码已重置\n";
    echo "   邮箱: admin@example.com\n";
    echo "   密码: admin123\n";

    // 确保admin用户有超级管理员角色
    $superAdminRole = \App\Models\Role::where('code', 'super_admin')->first();
    if ($superAdminRole && !$adminUser->roles()->where('code', 'super_admin')->exists()) {
        $adminUser->roles()->attach($superAdminRole->id);
        echo "   角色: 超级管理员已分配\n";
    }
    echo "\n";
} else {
    // 如果admin用户不存在，创建一个
    $adminUser = \App\Models\User::create([
        'name' => '系统管理员',
        'email' => 'admin@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
        'status' => 'active',
    ]);

    // 分配超级管理员角色
    $superAdminRole = \App\Models\Role::where('code', 'super_admin')->first();
    if ($superAdminRole) {
        $adminUser->roles()->attach($superAdminRole->id);
    }

    echo "✅ Admin用户已创建\n";
    echo "   邮箱: admin@example.com\n";
    echo "   密码: admin123\n";
    echo "   角色: 超级管理员\n\n";
}

// 重置test用户密码
$testUser = \App\Models\User::where('email', 'test@example.com')->first();
if ($testUser) {
    $testUser->password = \Illuminate\Support\Facades\Hash::make('password');
    $testUser->status = 'active';
    $testUser->save();
    echo "✅ Test用户密码已重置\n";
    echo "   邮箱: test@example.com\n";
    echo "   密码: password\n\n";
} else {
    // 如果test用户不存在，创建一个
    $testUser = \App\Models\User::create([
        'name' => '测试用户',
        'email' => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'status' => 'active',
    ]);
    echo "✅ Test用户已创建\n";
    echo "   邮箱: test@example.com\n";
    echo "   密码: password\n\n";
}

// 确保菜单结构正确（如果需要的话重新运行种子文件）
echo "🔧 检查菜单结构...\n";
$menuCount = \App\Models\SystemMenu::count();
if ($menuCount < 10) {
    echo "菜单数量不足，重新创建菜单...\n";
    \Artisan::call('db:seed', ['--class' => 'SystemMenuSeeder']);
    \Artisan::call('db:seed', ['--class' => 'MenuBasedPermissionSeeder']);
    echo "✅ 菜单结构已重建\n";
} else {
    echo "✅ 菜单结构正常\n";
}
echo "\n";

echo "==================\n";
echo "密码重置完成！\n";
echo "现在你可以使用以下账户登录：\n";
echo "管理员: admin@example.com / admin123\n";
echo "测试用户: test@example.com / password\n";
