<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Models\AttendanceRecord;
use Carbon\Carbon;

// 启动Laravel应用
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        api: __DIR__.'/routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// 学生ID
$studentId = 8;

// 课程名称列表
$courses = [
    '英语基础课程',
    '英语口语练习',
    '英语阅读理解',
    '英语写作训练',
    '英语听力练习',
    '英语语法课',
    '英语词汇课',
    '英语对话课',
    '英语故事课',
    '英语歌曲课'
];

// 教师名称列表
$teachers = [
    '张老师',
    '李老师',
    '王老师',
    '刘老师',
    '陈老师',
    '杨老师',
    '赵老师',
    '黄老师',
    '周老师',
    '吴老师'
];

// 出勤状态列表
$attendanceStatuses = [
    'present',    // 出勤 (80%)
    'present',
    'present',
    'present',
    'late',       // 迟到 (10%)
    'absent',     // 缺勤 (5%)
    'sick_leave', // 病假 (3%)
    'personal_leave' // 事假 (2%)
];

// 时间段列表
$timeSlots = [
    '09:00-10:00',
    '10:00-11:00',
    '14:00-15:00',
    '15:00-16:00',
    '16:00-17:00',
    '19:00-20:00',
    '20:00-21:00',
    '21:00-22:00'
];

// 教师备注列表
$teacherNotes = [
    '表现优秀，积极参与课堂互动',
    '发音有进步，继续加油',
    '课堂表现良好',
    '需要加强词汇练习',
    '听力理解能力有提升',
    '语法掌握较好',
    '口语表达流利',
    '作业完成质量高',
    '课堂纪律良好',
    '学习态度认真',
    null, // 有些记录没有备注
    null,
    null
];

echo "开始创建测试数据...\n";

// 创建30条记录
for ($i = 1; $i <= 30; $i++) {
    // 随机生成日期（过去3个月内）
    $daysAgo = rand(1, 90);
    $lessonDate = Carbon::now()->subDays($daysAgo);

    // 随机选择课程、教师、出勤状态等
    $course = $courses[array_rand($courses)];
    $teacher = $teachers[array_rand($teachers)];
    $status = $attendanceStatuses[array_rand($attendanceStatuses)];
    $timeSlot = $timeSlots[array_rand($timeSlots)];
    $notes = $teacherNotes[array_rand($teacherNotes)];

    // 根据出勤状态设置扣除课时
    $deductedLessons = match($status) {
        'present' => 1.0,
        'late' => 1.0,
        'absent' => 1.0,
        'sick_leave' => 0.5,
        'personal_leave' => 1.0,
        default => 1.0
    };

    // 创建记录
    $record = AttendanceRecord::create([
        'record_type' => 'manual',
        'schedule_id' => null,
        'class_id' => 3, // 使用现有的class_id
        'lesson_id' => null, // 手动记录不关联具体课程
        'actual_lesson_time' => $lessonDate->format('Y-m-d') . ' ' . explode('-', $timeSlot)[0] . ':00',
        'lesson_content' => $course,
        'student_id' => $studentId,
        'attendance_status' => $status,
        'deducted_lessons' => $deductedLessons,
        'check_in_time' => null,
        'absence_reason' => null,
        'makeup_required' => 0,
        'makeup_scheduled' => 0,
        'teacher_notes' => $notes,
        'recorded_by' => 2, // 系统管理员
        'recorded_at' => $lessonDate->addMinutes(30), // 课后30分钟记录
    ]);

    echo "创建记录 {$i}/30: {$course} - {$status} - {$lessonDate->format('Y-m-d H:i')}\n";
}

echo "测试数据创建完成！\n";
echo "总共为学生ID {$studentId} 创建了30条考勤记录。\n";

// 验证数据
$totalRecords = AttendanceRecord::where('student_id', $studentId)->count();
echo "学生 {$studentId} 现在总共有 {$totalRecords} 条考勤记录。\n";
