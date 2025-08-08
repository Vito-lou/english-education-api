<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use App\Models\Institution;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institution = Institution::first();

        if (!$institution) {
            $this->command->error('请先运行机构数据种子');
            return;
        }

        // 创建测试学员数据
        $students = [
            [
                'name' => '张小明',
                'phone' => '13800001001',
                'gender' => 'male',
                'birth_date' => '2015-03-15',
                'parent_name' => '张女士',
                'parent_phone' => '13800001000',
                'parent_relationship' => 'mother',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'high',
                'source' => '朋友推荐',
                'remarks' => '学习积极性很高，家长很配合',
            ],
            [
                'name' => '李小红',
                'phone' => '13800002001',
                'gender' => 'female',
                'birth_date' => '2014-08-20',
                'parent_name' => '李先生',
                'parent_phone' => '13800002000',
                'parent_relationship' => 'father',
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
                'birth_date' => '2016-01-10',
                'parent_name' => '王女士',
                'parent_phone' => '13800003000',
                'parent_relationship' => 'mother',
                'student_type' => 'potential',
                'follow_up_status' => 'new',
                'intention_level' => 'medium',
                'source' => '路过咨询',
                'remarks' => '刚咨询，需要跟进',
            ],
        ];

        foreach ($students as $studentData) {
            // 创建学员
            $student = Student::create([
                ...$studentData,
                'institution_id' => $institution->id,
                'status' => 'active',
            ]);

            // 为正式学员和试听学员创建家长账号
            if (in_array($student->student_type, ['enrolled', 'trial'])) {
                $user = User::create([
                    'name' => $studentData['parent_name'],
                    'phone' => $studentData['parent_phone'],
                    'email' => $studentData['parent_phone'] . '@parent' . time() . '.local',
                    'password' => Hash::make('123456'),
                    'institution_id' => $institution->id,
                    'status' => 'active',
                ]);

                // 关联学员和用户
                $student->users()->attach($user->id, [
                    'relationship' => $studentData['parent_relationship']
                ]);

                // 更新学员的主要用户ID
                $student->update(['user_id' => $user->id]);
            }
        }

        $this->command->info('学员测试数据创建成功！');
        $this->command->info('家长账号密码统一为：123456');
    }
}
