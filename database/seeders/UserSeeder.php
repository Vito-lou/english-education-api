<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建测试用户
        User::create([
            'name' => '测试用户',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // 创建管理员用户
        User::create([
            'name' => '管理员',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
        ]);
    }
}
