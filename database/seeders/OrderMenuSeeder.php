<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemMenu;

class OrderMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 添加订单管理菜单
        SystemMenu::create([
            'name' => '订单管理',
            'code' => 'order_management',
            'path' => '/finance/orders',
            'icon' => 'Receipt',
            'parent_id' => 4, // 财务管理的ID
            'sort_order' => 1,
            'status' => 'active',
            'description' => '学员报名订单管理'
        ]);

        echo "订单管理菜单添加成功\n";
    }
}
