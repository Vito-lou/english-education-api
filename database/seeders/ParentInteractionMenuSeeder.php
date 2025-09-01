<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemMenu;

class ParentInteractionMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 检查是否已存在家校互动菜单，避免重复创建
        $existingMenu = SystemMenu::where('code', 'parent_interaction')->first();
        if ($existingMenu) {
            $this->command->info('家校互动菜单已存在，跳过创建');
            return;
        }

        // 创建家校互动主菜单
        $parentMenu = SystemMenu::create([
            'name' => '家校互动',
            'code' => 'parent_interaction',
            'path' => '/parent-interaction',
            'icon' => 'Users',
            'parent_id' => null,
            'sort_order' => 30,
            'status' => 'active',
            'description' => '家校互动管理功能',
        ]);

        $this->command->info("创建家校互动主菜单，ID: {$parentMenu->id}");

        // 创建子菜单
        $subMenus = [
            [
                'name' => '课程安排',
                'code' => 'lesson_arrangements',
                'path' => '/parent-interaction/lesson-arrangements',
                'icon' => 'Calendar',
                'sort_order' => 1,
                'description' => '管理每次课的具体教学内容',
            ],
            [
                'name' => '课后作业',
                'code' => 'homework_assignments',
                'path' => '/parent-interaction/homework',
                'icon' => 'BookOpen',
                'sort_order' => 2,
                'description' => '布置和管理学生课后作业',
            ],
            [
                'name' => '课后点评',
                'code' => 'lesson_comments',
                'path' => '/parent-interaction/comments',
                'icon' => 'MessageSquare',
                'sort_order' => 3,
                'description' => '对学生课堂表现进行点评',
            ],
        ];

        foreach ($subMenus as $menuData) {
            $menu = SystemMenu::create([
                'name' => $menuData['name'],
                'code' => $menuData['code'],
                'path' => $menuData['path'],
                'icon' => $menuData['icon'],
                'parent_id' => $parentMenu->id,
                'sort_order' => $menuData['sort_order'],
                'status' => 'active',
                'description' => $menuData['description'],
            ]);

            $this->command->info("创建子菜单: {$menu->name}, ID: {$menu->id}");
        }

        $this->command->info('✅ 家校互动菜单创建完成！');
    }
}
