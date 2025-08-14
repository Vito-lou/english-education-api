<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Course;
use App\Models\CourseLevel;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建英语科目
        $englishSubject = Subject::firstOrCreate(
            ['code' => 'english'],
            [
                'name' => '英语',
                'description' => '英语学科',
                'institution_id' => 1,
                'sort_order' => 1,
                'status' => 'active',
            ]
        );

        // 创建原典法英语课程
        $yuandianCourse = Course::firstOrCreate(
            ['code' => 'yuandian_english'],
            [
                'subject_id' => $englishSubject->id,
                'name' => '原典法英语',
                'description' => '基于原典法的英语教学课程',
                'has_levels' => true,
                'institution_id' => 1,
                'sort_order' => 1,
                'status' => 'active',
            ]
        );

        // 创建课程级别
        $levels = [
            ['name' => 'Pre-A', 'code' => 'pre_a', 'description' => '预备A级', 'sort_order' => 1],
            ['name' => 'A', 'code' => 'a', 'description' => 'A级', 'sort_order' => 2],
            ['name' => 'B', 'code' => 'b', 'description' => 'B级', 'sort_order' => 3],
            ['name' => 'C', 'code' => 'c', 'description' => 'C级', 'sort_order' => 4],
            ['name' => 'D', 'code' => 'd', 'description' => 'D级', 'sort_order' => 5],
            ['name' => 'E', 'code' => 'e', 'description' => 'E级', 'sort_order' => 6],
        ];

        foreach ($levels as $level) {
            CourseLevel::create([
                'course_id' => $yuandianCourse->id,
                'name' => $level['name'],
                'code' => $level['code'],
                'description' => $level['description'],
                'sort_order' => $level['sort_order'],
                'status' => 'active',
            ]);
        }

        // 创建少儿英语课程
        $kidsEnglishCourse = Course::create([
            'subject_id' => $englishSubject->id,
            'name' => '少儿英语',
            'code' => 'kids_english',
            'description' => '适合4-12岁儿童的英语课程',
            'has_levels' => true,
            'institution_id' => 1,
            'sort_order' => 2,
            'status' => 'active',
        ]);

        // 为少儿英语创建级别
        foreach ($levels as $level) {
            CourseLevel::create([
                'course_id' => $kidsEnglishCourse->id,
                'name' => $level['name'],
                'code' => $level['code'],
                'description' => $level['description'],
                'sort_order' => $level['sort_order'],
                'status' => 'active',
            ]);
        }

        // 创建成人英语课程（无级别）
        Course::create([
            'subject_id' => $englishSubject->id,
            'name' => '成人英语',
            'code' => 'adult_english',
            'description' => '成人英语培训课程',
            'has_levels' => false,
            'institution_id' => 1,
            'sort_order' => 3,
            'status' => 'active',
        ]);
    }
}
