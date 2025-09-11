<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemMenu;

class MaterialCenterMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建素材中心主菜单
        $materialCenterMenu = SystemMenu::create([
            'name' => '素材中心',
            'code' => 'material_center',
            'path' => null, // 主菜单不需要路径
            'icon' => 'folder',
            'parent_id' => null,
            'sort_order' => 6, // 排在应用中心之后
            'status' => 'active',
            'description' => '故事与知识点管理模块',
        ]);

        echo "创建素材中心主菜单，ID: {$materialCenterMenu->id}\n";

        // 创建故事管理子菜单
        $storyMenu = SystemMenu::create([
            'name' => '故事管理',
            'code' => 'material_center.stories',
            'path' => '/material-center/stories',
            'icon' => 'book',
            'parent_id' => $materialCenterMenu->id,
            'sort_order' => 1,
            'status' => 'active',
            'description' => '管理短篇故事和长篇分章故事',
        ]);

        echo "创建子菜单: 故事管理, ID: {$storyMenu->id}\n";

        // 创建知识点管理子菜单
        $knowledgePointsMenu = SystemMenu::create([
            'name' => '知识点管理',
            'code' => 'material_center.knowledge_points',
            'path' => '/material-center/knowledge-points',
            'icon' => 'lightbulb',
            'parent_id' => $materialCenterMenu->id,
            'sort_order' => 2,
            'status' => 'active',
            'description' => '管理词汇、语法、短语等知识点',
        ]);

        echo "创建子菜单: 知识点管理, ID: {$knowledgePointsMenu->id}\n";

        // 创建知识标签子菜单
        $tagsMenu = SystemMenu::create([
            'name' => '知识标签',
            'code' => 'material_center.knowledge_tags',
            'path' => '/material-center/knowledge-tags',
            'icon' => 'tag',
            'parent_id' => $materialCenterMenu->id,
            'sort_order' => 3,
            'status' => 'active',
            'description' => '管理知识标签体系',
        ]);

        echo "创建子菜单: 知识标签, ID: {$tagsMenu->id}\n";

        echo "✅ 素材中心菜单创建完成！\n";
    }
}
