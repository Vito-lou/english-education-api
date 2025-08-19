<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\ClassModel;
use App\Models\StudentClass;

class TestStudentSeeder extends Seeder
{
    /**
     * 运行测试学员数据种子
     */
    public function run(): void
    {
        // 获取机构ID
        $institutionId = 1;

        // 创建测试学员
        $students = [
            [
                'name' => '张小明',
                'phone' => '13800001001',
                'gender' => 'male',
                'birth_date' => '2010-05-15',
                'parent_name' => '张爸爸',
                'parent_phone' => '13800001000',
                'parent_relationship' => 'father',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'high',
                'source' => '朋友推荐',
                'remarks' => '学习积极性很高，基础较好',
            ],
            [
                'name' => '李小红',
                'phone' => '13800002001',
                'gender' => 'female',
                'birth_date' => '2011-08-20',
                'parent_name' => '李妈妈',
                'parent_phone' => '13800002000',
                'parent_relationship' => 'mother',
                'student_type' => 'trial',
                'follow_up_status' => 'contacted',
                'intention_level' => 'medium',
                'source' => '网络广告',
                'remarks' => '试听中，表现不错',
            ],
            [
                'name' => '王小华',
                'phone' => '13800003001',
                'gender' => 'male',
                'birth_date' => '2009-12-10',
                'parent_name' => '王爷爷',
                'parent_phone' => '13800003000',
                'parent_relationship' => 'guardian',
                'student_type' => 'potential',
                'follow_up_status' => 'new',
                'intention_level' => 'low',
                'source' => '路过咨询',
                'remarks' => '还在考虑中',
            ],
            [
                'name' => '赵小美',
                'phone' => '13800004001',
                'gender' => 'female',
                'birth_date' => '2012-03-25',
                'parent_name' => '赵妈妈',
                'parent_phone' => '13800004000',
                'parent_relationship' => 'mother',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'high',
                'source' => '老学员推荐',
                'remarks' => '很有语言天赋',
            ],
            [
                'name' => '陈小强',
                'phone' => '13800005001',
                'gender' => 'male',
                'birth_date' => '2010-11-08',
                'parent_name' => '陈爸爸',
                'parent_phone' => '13800005000',
                'parent_relationship' => 'father',
                'student_type' => 'graduated',
                'follow_up_status' => 'follow_up',
                'intention_level' => 'medium',
                'source' => '学校合作',
                'remarks' => '已完成A级别课程',
            ],
        ];

        $createdStudents = [];
        foreach ($students as $studentData) {
            $studentData['institution_id'] = $institutionId;
            $student = Student::create($studentData);
            $createdStudents[] = $student;
        }

        // 如果有班级，将一些学员分配到班级中
        $classes = ClassModel::where('institution_id', $institutionId)->get();
        if ($classes->count() > 0) {
            $class = $classes->first();
            
            // 将前3个学员分配到第一个班级
            foreach (array_slice($createdStudents, 0, 3) as $index => $student) {
                if ($student->student_type === 'enrolled') {
                    StudentClass::create([
                        'student_id' => $student->id,
                        'class_id' => $class->id,
                        'enrollment_date' => now()->subDays(30 - $index * 10)->format('Y-m-d'),
                        'status' => 'active',
                    ]);
                }
            }
        }

        $this->command->info('测试学员数据创建完成！');
        $this->command->info('创建了 ' . count($students) . ' 个测试学员');
        if ($classes->count() > 0) {
            $this->command->info('部分学员已分配到班级');
        }
    }
}
