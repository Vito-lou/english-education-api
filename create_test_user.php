<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 创建测试用户
$user = \App\Models\User::firstOrCreate(
    ['email' => 'admin@test.com'],
    [
        'name' => 'Test Admin',
        'password' => bcrypt('password'),
        'status' => 'active'
    ]
);

// 生成token
$token = $user->createToken('test-token')->plainTextToken;

echo "用户创建成功！\n";
echo "Email: admin@test.com\n";
echo "Password: password\n";
echo "Token: $token\n";
