<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\ClassSchedule;
use App\Models\TimeSlot;
use App\Models\Institution;

class A1ClassScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取A1班级
        $class = ClassModel::where('name', 'A1')->first();
        if (!$class) {
            $this->command->error('A1班级不存在，请先运行班级种子数据');
            return;
        }

        // 获取机构
        $institution = Institution::first();
        if (!$institution) {
            $this->command->error('机构不存在，请先运行组织架构种子数据');
            return;
        }

        // 获取时间段（14:00-15:30）
        $timeSlot = TimeSlot::where('start_time', '14:00:00')
            ->where('end_time', '15:30:00')
            ->where('institution_id', $institution->id)
            ->first();

        if (!$timeSlot) {
            $this->command->error('时间段(14:00-15:30)不存在，请先运行时间段种子数据');
            return;
        }

        // 要创建的排课日期
        $scheduleDates = [
            '2024-08-29', // 8月29日
            '2024-08-30', // 8月30日
            '2024-09-05', // 9月5日
            '2024-09-06', // 9月6日
            '2024-09-13', // 9月13日
            '2024-09-14', // 9月14日
            '2024-09-20', // 9月20日
            '2024-09-21', // 9月21日
        ];

        foreach ($scheduleDates as $date) {
            // 检查是否已存在该日期的排课
            $existingSchedule = ClassSchedule::where('class_id', $class->id)
                ->where('schedule_date', $date)
                ->where('time_slot_id', $timeSlot->id)
                ->first();

            if (!$existingSchedule) {
                // 获取创建人（使用班级的老师作为创建人）
                $creator = $class->teacher;

                // 创建排课记录
                $schedule = ClassSchedule::create([
                    'class_id' => $class->id,
                    'course_id' => $class->course_id,
                    'teacher_id' => $class->teacher_id,
                    'time_slot_id' => $timeSlot->id,
                    'schedule_date' => $date,
                    'lesson_id' => null, // 暂不指定具体课时
                    'lesson_content' => null, // 暂不指定上课内容
                    'classroom' => null, // 暂不指定教室
                    'status' => 'scheduled',
                    'created_by' => $creator->id,
                ]);

                $this->command->info("创建A1班排课: {$date} {$timeSlot->time_range}");
            } else {
                $this->command->info("排课已存在: {$date} {$timeSlot->time_range}");
            }
        }

        $this->command->info('A1班级排课数据创建完成！');
        $this->command->info('共创建了 ' . count($scheduleDates) . ' 个排课记录');
    }
}
