<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\Department;
use App\Models\User;
use App\Models\Institution;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取第一个机构
        $institution = Institution::first();
        if (!$institution) {
            $this->command->warn('没有找到机构，请先运行机构种子数据');
            return;
        }

        // 获取该机构的校区（部门）
        $campuses = Department::where('institution_id', $institution->id)
            ->where('type', 'campus')
            ->get();

        if ($campuses->isEmpty()) {
            $this->command->warn('没有找到校区，请先运行组织架构种子数据');
            return;
        }

        // 获取该机构的课程
        $courses = Course::where('institution_id', $institution->id)->get();
        if ($courses->isEmpty()) {
            $this->command->warn('没有找到课程，请先运行课程种子数据');
            return;
        }

        // 获取该机构的用户（作为教师）
        $teachers = User::where('institution_id', $institution->id)->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('没有找到用户，请先运行用户种子数据');
            return;
        }

        // 创建示例班级
        $classesData = [
            [
                'name' => 'Pre-A级周末班',
                'course' => '原典法英语',
                'level' => 'Pre-A',
                'max_students' => 15,
                'total_lessons' => 48,
                'status' => 'active',
                'remarks' => '适合4-6岁初学者，周末上课',
            ],
            [
                'name' => 'A级平日班',
                'course' => '原典法英语',
                'level' => 'A',
                'max_students' => 12,
                'total_lessons' => 60,
                'status' => 'active',
                'remarks' => '适合有一定基础的学员，平日晚上上课',
            ],
            [
                'name' => 'B级强化班',
                'course' => '原典法英语',
                'level' => 'B',
                'max_students' => 10,
                'total_lessons' => 72,
                'status' => 'active',
                'remarks' => '强化训练班，提升口语和听力',
            ],
            [
                'name' => 'C级精品班',
                'course' => '原典法英语',
                'level' => 'C',
                'max_students' => 8,
                'total_lessons' => 80,
                'status' => 'graduated',
                'remarks' => '小班教学，已结业班级',
            ],
        ];

        foreach ($classesData as $classData) {
            // 查找对应的课程
            $course = $courses->where('name', $classData['course'])->first();
            if (!$course) {
                continue;
            }

            // 查找对应的级别
            $level = null;
            if ($classData['level']) {
                $level = CourseLevel::where('course_id', $course->id)
                    ->where('code', strtolower(str_replace('-', '_', $classData['level'])))
                    ->first();
            }

            // 随机选择校区和教师
            $campus = $campuses->random();
            $teacher = $teachers->random();

            // 创建班级
            $class = ClassModel::create([
                'name' => $classData['name'],
                'campus_id' => $campus->id,
                'course_id' => $course->id,
                'level_id' => $level?->id,
                'max_students' => $classData['max_students'],
                'teacher_id' => $teacher->id,
                'total_lessons' => $classData['total_lessons'],
                'status' => $classData['status'],
                'start_date' => now()->subDays(rand(30, 180))->toDateString(),
                'end_date' => $classData['status'] === 'graduated' ? now()->subDays(rand(1, 30))->toDateString() : null,
                'remarks' => $classData['remarks'],
                'institution_id' => $institution->id,
            ]);

            $this->command->info("创建班级: {$class->name}");
        }

        $this->command->info('班级种子数据创建完成！');
    }
}
