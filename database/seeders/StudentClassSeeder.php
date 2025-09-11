<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\StudentClass;

class StudentClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 根据真实数据创建学生班级关系
        $studentClassData = [
            // Pre-A1班级的学生
            [
                'class_name' => 'Pre-A1',
                'students' => [
                    ['name' => '娄泽林', 'enrollment_date' => '2025-09-11', 'status' => 'active'],
                    ['name' => '张洛成', 'enrollment_date' => '2025-09-11', 'status' => 'active'],
                ]
            ],
            // A1班级的学生
            [
                'class_name' => 'A1',
                'students' => [
                    ['name' => '许芯睿', 'enrollment_date' => '2025-09-11', 'status' => 'active'],
                    ['name' => '刘熙予', 'enrollment_date' => '2025-09-11', 'status' => 'active'],
                    ['name' => '周瞳彤', 'enrollment_date' => '2025-09-11', 'status' => 'active'],
                    ['name' => '石恒明', 'enrollment_date' => '2025-09-11', 'status' => 'active'],
                    ['name' => '娄梓原', 'enrollment_date' => '2025-09-11', 'status' => 'active'],
                ]
            ],
        ];

        foreach ($studentClassData as $classData) {
            // 查找班级
            $class = ClassModel::where('name', $classData['class_name'])->first();
            if (!$class) {
                $this->command->warn("找不到班级: {$classData['class_name']}");
                continue;
            }

            foreach ($classData['students'] as $studentData) {
                // 查找学生
                $student = Student::where('name', $studentData['name'])->first();
                if (!$student) {
                    $this->command->warn("找不到学生: {$studentData['name']}");
                    continue;
                }

                // 检查是否已存在关系
                $existingRelation = StudentClass::where('student_id', $student->id)
                    ->where('class_id', $class->id)
                    ->first();

                if (!$existingRelation) {
                    // 创建学生班级关系
                    StudentClass::create([
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'enrollment_date' => $studentData['enrollment_date'],
                        'status' => $studentData['status'],
                    ]);

                    $this->command->info("添加学生 {$student->name} 到班级 {$class->name}");
                } else {
                    $this->command->info("学生 {$student->name} 已在班级 {$class->name} 中");
                }
            }
        }

        $this->command->info('学生班级关系种子数据创建完成！');
    }
}
