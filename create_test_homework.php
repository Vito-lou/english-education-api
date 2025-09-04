<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// 数据库配置
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'english_education',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

function now() {
    return date('Y-m-d H:i:s');
}

try {
    echo "开始创建测试作业数据...\n";

    // 获取第一个机构
    $institution = Capsule::table('institutions')->first();
    if (!$institution) {
        echo "❌ 没有找到机构，请先运行种子数据\n";
        exit(1);
    }

    // 获取第一个班级
    $class = Capsule::table('classes')->where('institution_id', $institution->id)->first();
    if (!$class) {
        echo "❌ 没有找到班级，请先创建班级数据\n";
        exit(1);
    }

    // 获取第一个用户作为创建者
    $user = Capsule::table('users')->where('institution_id', $institution->id)->first();
    if (!$user) {
        echo "❌ 没有找到用户，请先创建用户数据\n";
        exit(1);
    }

    // 获取第一个课程单元
    $unit = Capsule::table('course_units')->first();

    // 创建测试作业
    $homeworkData = [
        [
            'title' => '英语阅读理解练习',
            'class_id' => $class->id,
            'unit_id' => $unit ? $unit->id : null,
            'due_date' => date('Y-m-d H:i:s', strtotime('+3 days')),
            'requirements' => "请完成以下任务：\n1. 阅读课本第15-20页的故事\n2. 完成课后练习题1-5\n3. 录制朗读音频（不少于2分钟）\n4. 写一篇不少于100字的读后感",
            'attachments' => json_encode([
                [
                    'name' => '阅读材料.pdf',
                    'path' => 'homework/assignments/reading_material.pdf',
                    'size' => 1024000,
                    'type' => 'application/pdf'
                ],
                [
                    'name' => '练习题.docx',
                    'path' => 'homework/assignments/exercises.docx',
                    'size' => 512000,
                    'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                ]
            ]),
            'status' => 'active',
            'created_by' => $user->id,
            'institution_id' => $institution->id,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => '英语口语练习',
            'class_id' => $class->id,
            'unit_id' => $unit ? $unit->id : null,
            'due_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
            'requirements' => "口语练习要求：\n1. 练习本周学习的对话内容\n2. 录制自我介绍视频（3-5分钟）\n3. 模仿课文中的语音语调\n4. 准备下次课的口语展示",
            'attachments' => json_encode([
                [
                    'name' => '对话示例.mp3',
                    'path' => 'homework/assignments/dialogue_example.mp3',
                    'size' => 2048000,
                    'type' => 'audio/mpeg'
                ]
            ]),
            'status' => 'active',
            'created_by' => $user->id,
            'institution_id' => $institution->id,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => '英语写作练习',
            'class_id' => $class->id,
            'unit_id' => null,
            'due_date' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'requirements' => "写作练习：\n1. 以'My Family'为题写一篇英语作文\n2. 不少于150个单词\n3. 使用本周学习的语法结构\n4. 注意拼写和语法正确性",
            'attachments' => null,
            'status' => 'active',
            'created_by' => $user->id,
            'institution_id' => $institution->id,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'title' => '已过期的作业测试',
            'class_id' => $class->id,
            'unit_id' => null,
            'due_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'requirements' => "这是一个已过期的测试作业，用于测试过期状态显示。",
            'attachments' => null,
            'status' => 'active',
            'created_by' => $user->id,
            'institution_id' => $institution->id,
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'updated_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
        ]
    ];

    foreach ($homeworkData as $homework) {
        $homeworkId = Capsule::table('homework_assignments')->insertGetId($homework);
        echo "✅ 创建作业: {$homework['title']} (ID: $homeworkId)\n";
    }

    // 获取第一个学生
    $student = Capsule::table('students')->where('institution_id', $institution->id)->first();
    if ($student) {
        // 确保学生在班级中
        $studentInClass = Capsule::table('student_classes')
            ->where('student_id', $student->id)
            ->where('class_id', $class->id)
            ->first();

        if (!$studentInClass) {
            // 将学生添加到班级中
            Capsule::table('student_classes')->insert([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'enrollment_date' => date('Y-m-d'),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "✅ 将学生 {$student->name} 添加到班级中\n";
        }

        // 为第一个作业创建提交记录
        $firstHomework = Capsule::table('homework_assignments')
            ->where('class_id', $class->id)
            ->first();

        if ($firstHomework) {
            $submissionId = Capsule::table('homework_submissions')->insertGetId([
                'homework_assignment_id' => $firstHomework->id,
                'student_id' => $student->id,
                'content' => "这是我的作业提交内容。\n\n我已经完成了所有要求的任务：\n1. 阅读了指定的材料\n2. 完成了练习题\n3. 录制了朗读音频\n4. 写了读后感\n\n希望老师能给予指导。",
                'attachments' => json_encode([
                    [
                        'name' => '我的朗读音频.mp3',
                        'path' => 'homework/submissions/student_reading.mp3',
                        'size' => 1536000,
                        'type' => 'audio/mpeg'
                    ],
                    [
                        'name' => '读后感.docx',
                        'path' => 'homework/submissions/reflection.docx',
                        'size' => 256000,
                        'type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    ]
                ]),
                'status' => 'graded',
                'score' => 85.5,
                'max_score' => 100,
                'teacher_feedback' => '作业完成得很好！朗读流利，读后感内容丰富。建议在语法方面再加强练习。继续保持！',
                'submitted_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'graded_at' => now(),
                'graded_by' => $user->id,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => now(),
            ]);
            echo "✅ 创建作业提交记录 (ID: $submissionId)\n";
        }
    }

    // 创建一些知识点数据（如果有单元的话）
    if ($unit) {
        $knowledgePoints = [
            [
                'unit_id' => $unit->id,
                'type' => 'vocabulary',
                'content' => 'family',
                'explanation' => '家庭，家人',
                'example_sentences' => json_encode([
                    'I love my family.',
                    'My family is very important to me.'
                ]),
                'sort_order' => 1,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'unit_id' => $unit->id,
                'type' => 'vocabulary',
                'content' => 'introduce',
                'explanation' => '介绍，引入',
                'example_sentences' => json_encode([
                    'Let me introduce myself.',
                    'I want to introduce you to my friend.'
                ]),
                'sort_order' => 2,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($knowledgePoints as $point) {
            $pointId = Capsule::table('unit_knowledge_points')->insertGetId($point);
            echo "✅ 创建知识点: {$point['content']} (ID: $pointId)\n";

            // 将知识点关联到第一个作业
            $firstHomework = Capsule::table('homework_assignments')
                ->where('class_id', $class->id)
                ->first();

            if ($firstHomework) {
                Capsule::table('homework_knowledge_points')->insert([
                    'homework_assignment_id' => $firstHomework->id,
                    'knowledge_point_id' => $pointId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    echo "\n🎉 测试作业数据创建完成！\n";
    echo "📊 统计信息：\n";
    echo "- 作业数量: " . count($homeworkData) . "\n";
    echo "- 班级ID: {$class->id}\n";
    echo "- 学生ID: " . ($student ? $student->id : '无') . "\n";
    echo "- 机构ID: {$institution->id}\n";

} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    exit(1);
}
