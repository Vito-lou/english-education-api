<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 创建测试用户
        User::factory()->create([
            'name' => '测试用户',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);

        // 创建管理员用户
        User::factory()->create([
            'name' => '系统管理员',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
            'status' => 'active',
        ]);

        // 创建组织架构数据
        $this->call(OrganizationSeeder::class);

        // 创建基础数据（角色、权限等）
        $this->call(BasicDataSeeder::class);

        // 创建系统菜单
        $this->call(SystemMenuSeeder::class);

        // 分配菜单权限
        $this->call(MenuBasedPermissionSeeder::class);
    }
}
