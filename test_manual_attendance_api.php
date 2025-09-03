<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Lesson;

// 测试手动点名API的数据准备
echo "=== 手动点名API测试数据准备 ===\n";

// 1. 检查用户
$user = User::first();
if (!$user) {
    echo "❌ 没有找到用户\n";
    exit;
}
echo "✅ 找到用户: {$user->name} (ID: {$user->id})\n";

// 2. 检查班级
$class = ClassModel::first();
if (!$class) {
    echo "❌ 没有找到班级\n";
    exit;
}
echo "✅ 找到班级: {$class->name} (ID: {$class->id})\n";

// 3. 检查学员
$students = Student::whereHas('classes', function ($query) use ($class) {
    $query->where('class_id', $class->id);
})->with('user')->get();

if ($students->isEmpty()) {
    echo "❌ 班级中没有学员\n";
    exit;
}
echo "✅ 找到 {$students->count()} 个学员:\n";
foreach ($students as $student) {
    echo "   - {$student->user->name} (学号: {$student->student_code})\n";
}

// 4. 检查课程内容
$lessons = Lesson::with(['courseUnit.course'])->take(5)->get();
echo "✅ 找到 {$lessons->count()} 个课程内容:\n";
foreach ($lessons as $lesson) {
    echo "   - {$lesson->title} ({$lesson->courseUnit->course->name})\n";
}

echo "\n=== API测试建议 ===\n";
echo "1. 获取班级学员: GET /api/admin/manual-attendance/classes/{$class->id}/students\n";
echo "2. 获取课程内容: GET /api/admin/manual-attendance/lessons\n";
echo "3. 创建手动点名: POST /api/admin/manual-attendance\n";
echo "   请求数据示例:\n";
echo "   {\n";
echo "     \"class_id\": {$class->id},\n";
echo "     \"actual_lesson_time\": \"2025-09-03 10:00:00\",\n";
echo "     \"lesson_id\": " . ($lessons->first() ? $lessons->first()->id : 'null') . ",\n";
echo "     \"lesson_content\": \"测试课程内容\",\n";
echo "     \"students\": [\n";
foreach ($students->take(2) as $index => $student) {
    echo "       {\n";
    echo "         \"student_id\": {$student->id},\n";
    echo "         \"attendance_status\": \"present\",\n";
    echo "         \"deducted_lessons\": 1,\n";
    echo "         \"teacher_notes\": \"测试备注\"\n";
    echo "       }" . ($index < 1 ? "," : "") . "\n";
}
echo "     ]\n";
echo "   }\n";

echo "\n=== 数据库检查 ===\n";
echo "AttendanceRecord 表记录数: " . \App\Models\AttendanceRecord::count() . "\n";
echo "手动点名记录数: " . \App\Models\AttendanceRecord::where('record_type', 'manual')->count() . "\n";
echo "计划点名记录数: " . \App\Models\AttendanceRecord::where('record_type', 'scheduled')->count() . "\n";

echo "\n✅ 测试数据准备完成！\n";
