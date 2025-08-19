<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;
use App\Models\Institution;

class TimeSlotSeeder extends Seeder
{
    /**
     * 运行时间段配置种子数据
     */
    public function run(): void
    {
        // 获取机构ID
        $institution = Institution::first();
        if (!$institution) {
            $this->command->error('没有找到机构，请先运行 OrganizationSeeder');
            return;
        }

        // 时间段配置数据
        $timeSlots = [
            [
                'name' => '上午第一节',
                'start_time' => '09:00:00',
                'end_time' => '10:30:00',
                'duration_minutes' => 90,
                'sort_order' => 1,
            ],
            [
                'name' => '上午第二节',
                'start_time' => '10:45:00',
                'end_time' => '12:15:00',
                'duration_minutes' => 90,
                'sort_order' => 2,
            ],
            [
                'name' => '下午第一节',
                'start_time' => '14:00:00',
                'end_time' => '15:30:00',
                'duration_minutes' => 90,
                'sort_order' => 3,
            ],
            [
                'name' => '下午第二节',
                'start_time' => '15:45:00',
                'end_time' => '17:15:00',
                'duration_minutes' => 90,
                'sort_order' => 4,
            ],
            [
                'name' => '晚上第一节',
                'start_time' => '18:30:00',
                'end_time' => '20:00:00',
                'duration_minutes' => 90,
                'sort_order' => 5,
            ],
            [
                'name' => '晚上第二节',
                'start_time' => '20:15:00',
                'end_time' => '21:45:00',
                'duration_minutes' => 90,
                'sort_order' => 6,
            ],
        ];

        // 创建时间段
        foreach ($timeSlots as $timeSlotData) {
            TimeSlot::create([
                'institution_id' => $institution->id,
                'name' => $timeSlotData['name'],
                'start_time' => $timeSlotData['start_time'],
                'end_time' => $timeSlotData['end_time'],
                'duration_minutes' => $timeSlotData['duration_minutes'],
                'is_active' => true,
                'sort_order' => $timeSlotData['sort_order'],
            ]);
        }

        $this->command->info('时间段配置数据创建完成！');
        $this->command->info('创建了 ' . count($timeSlots) . ' 个时间段');
    }
}
