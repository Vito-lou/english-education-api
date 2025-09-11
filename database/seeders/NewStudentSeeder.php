<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Department;
use App\Models\User;
use App\Models\Role;
use App\Models\Institution;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\StudentEnrollment;

class NewStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 获取机构和泗洪校区
        $institution = Institution::where('code', 'EETC001')->first();
        $sihongCampus = Department::where('code', 'CAMPUS_SH')->first();

        if (!$institution || !$sihongCampus) {
            $this->command->error('机构或泗洪校区不存在，请先运行组织架构种子数据');
            return;
        }

        // 获取家长角色
        $parentRole = Role::where('code', 'parent')->first();
        if (!$parentRole) {
            $this->command->error('家长角色不存在，请先运行基础数据种子');
            return;
        }

        // 获取课程和级别
        $course = Course::first(); // 原典法英语
        $level = CourseLevel::first(); // Pre-A级别

        if (!$course || !$level) {
            $this->command->error('课程或级别不存在，请先运行课程种子数据');
            return;
        }

        // 获取销售人员（使用系统管理员）
        $salesPerson = User::where('email', 'admin@example.com')->first();
        if (!$salesPerson) {
            $this->command->error('销售人员不存在，请先运行组织架构种子数据');
            return;
        }

        // 新学生数据
        $students = [
            [
                'name' => '许芯睿',
                'phone' => '13800006001',
                'gender' => 'female',
                'birth_date' => '2012-06-15',
                'parent_name' => '许芯睿家长',
                'parent_phone' => '13800006000',
                'parent_email' => 'xu_parent@example.com',
                'parent_relationship' => 'father',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'high',
                'source' => '朋友推荐',
                'remarks' => '学习认真，表现优秀',
            ],
            [
                'name' => '刘熙予',
                'phone' => '13800007001',
                'gender' => 'female',
                'birth_date' => '2011-09-20',
                'parent_name' => '刘熙予家长',
                'parent_phone' => '13800007000',
                'parent_email' => 'liu_parent@example.com',
                'parent_relationship' => 'mother',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'high',
                'source' => '网络咨询',
                'remarks' => '英语基础扎实',
            ],
            [
                'name' => '周瞳彤',
                'phone' => '13800008001',
                'gender' => 'female',
                'birth_date' => '2013-02-10',
                'parent_name' => '周瞳彤家长',
                'parent_phone' => '13800008000',
                'parent_email' => 'zhou_parent@example.com',
                'parent_relationship' => 'father',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'medium',
                'source' => '老学员推荐',
                'remarks' => '活泼好学，口语表达能力强',
            ],
            [
                'name' => '石恒明',
                'phone' => '13800009001',
                'gender' => 'male',
                'birth_date' => '2010-12-05',
                'parent_name' => '石恒明家长',
                'parent_phone' => '13800009000',
                'parent_email' => 'shi_parent@example.com',
                'parent_relationship' => 'mother',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'high',
                'source' => '学校合作',
                'remarks' => '逻辑思维强，语法掌握好',
            ],
            [
                'name' => '娄梓原',
                'phone' => '13800010001',
                'gender' => 'male',
                'birth_date' => '2012-04-18',
                'parent_name' => '娄梓原家长',
                'parent_phone' => '13800010000',
                'parent_email' => 'lou1_parent@example.com',
                'parent_relationship' => 'father',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'medium',
                'source' => '朋友推荐',
                'remarks' => '学习态度端正，进步明显',
            ],
            [
                'name' => '娄泽林',
                'phone' => '13800011001',
                'gender' => 'male',
                'birth_date' => '2011-07-22',
                'parent_name' => '娄泽林家长',
                'parent_phone' => '13800011000',
                'parent_email' => 'lou2_parent@example.com',
                'parent_relationship' => 'mother',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'high',
                'source' => '朋友推荐',
                'remarks' => '兄弟俩一起学习，互相促进',
            ],
            [
                'name' => '张洛成',
                'phone' => '13800012001',
                'gender' => 'male',
                'birth_date' => '2010-10-30',
                'parent_name' => '张洛成家长',
                'parent_phone' => '13800012000',
                'parent_email' => 'zhang_parent@example.com',
                'parent_relationship' => 'mother',
                'student_type' => 'enrolled',
                'follow_up_status' => 'interested',
                'intention_level' => 'high',
                'source' => '网络广告',
                'remarks' => '学习主动性强，成绩优秀',
            ],
        ];

        $createdStudents = [];
        foreach ($students as $index => $studentData) {
            // 添加机构ID
            $studentData['institution_id'] = $institution->id;

            // 提取家长信息
            $parentName = $studentData['parent_name'];
            $parentPhone = $studentData['parent_phone'];
            $parentEmail = $studentData['parent_email'];
            unset($studentData['parent_email']); // 从学生数据中移除，因为学生表没有这个字段

            // 创建学生
            $student = Student::create($studentData);
            $createdStudents[] = $student;

            // 为每个学生创建对应的家长账户
            $parentUser = User::create([
                'name' => $parentName,
                'email' => $parentEmail,
                'password' => bcrypt('123456'), // 默认密码
                'phone' => $parentPhone,
                'institution_id' => $institution->id,
                'department_id' => $sihongCampus->id,
                'status' => 'active',
            ]);

            // 分配家长角色
            $parentUser->roles()->attach($parentRole->id);

            // 创建报名记录
            $enrollment = StudentEnrollment::create([
                'student_id' => $student->id,
                'institution_id' => $institution->id,
                'campus_id' => $sihongCampus->id,
                'course_id' => $course->id,
                'level_id' => $level->id,
                'enrollment_date' => now()->subDays(30 - $index * 3)->format('Y-m-d'), // 错开报名日期
                'start_date' => now()->subDays(20 - $index * 2)->format('Y-m-d'),
                'total_lessons' => 48, // 48课时
                'used_lessons' => 0,
                'remaining_lessons' => 48,
                'status' => 'active',
                'enrollment_fee' => 4800.00, // 总费用
                'price_per_lesson' => 100.00, // 每课时100元
                'discount_type' => 'none',
                'discount_value' => 0,
                'actual_amount' => 4800.00,
                'paid_amount' => 4800.00,
                'payment_status' => 'paid',
                'sales_person_id' => $salesPerson->id,
                'remarks' => '种子数据生成的报名记录',
            ]);

            $this->command->info("创建学生: {$student->name} 和家长账户: {$parentUser->name}");
            $this->command->info("  - 报名记录ID: {$enrollment->id}，订单号: {$enrollment->order_number}");
        }

        $this->command->info('新学生数据创建完成！');
        $this->command->info('创建了 ' . count($students) . ' 个新学生，都属于泗洪校区');
        $this->command->info('同时创建了对应的家长账户和报名记录');
        $this->command->info('现在可以将这些学生添加到班级中了！');
    }
}
