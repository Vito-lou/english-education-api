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
        // 先创建组织架构数据（包括机构和用户）
        $this->call(OrganizationSeeder::class);

        // 创建基础数据（角色、权限等）
        $this->call(BasicDataSeeder::class);

        // 创建系统菜单
        $this->call(SystemMenuSeeder::class);

        // 创建家校互动菜单
        $this->call(ParentInteractionMenuSeeder::class);

        // 分配菜单权限
        $this->call(MenuBasedPermissionSeeder::class);

        // 创建原典法英语课程数据
        $this->call(YuandianEnglishSeeder::class);

        // 创建测试学员数据
        $this->call(TestStudentSeeder::class);

        // 创建时间段配置数据
        $this->call(TimeSlotSeeder::class);
    }
}
