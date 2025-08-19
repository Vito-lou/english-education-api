<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Course;
use App\Models\CourseLevel;
use App\Models\CourseUnit;
use App\Models\Lesson;

class YuandianEnglishSeeder extends Seeder
{
    /**
     * 运行原典法英语课程数据种子
     */
    public function run(): void
    {
        // 1. 创建英语科目
        $subject = Subject::create([
            'name' => '英语',
            'code' => 'ENGLISH',
            'description' => '原典法英语教学',
            'institution_id' => 1, // 假设机构ID为1
            'sort_order' => 1,
            'status' => 'active'
        ]);

        // 2. 创建原典法英语课程
        $course = Course::create([
            'subject_id' => $subject->id,
            'name' => '原典法英语',
            'code' => 'YUANDIAN_ENGLISH',
            'description' => '基于原典法理念的英语教学课程，注重听力为先、口语跟进、阅读理解、写作表达的四步骤教学法',
            'has_levels' => true,
            'institution_id' => 1,
            'sort_order' => 1,
            'status' => 'active'
        ]);

        // 3. 创建课程级别 (Pre-A, A, B, C, D)
        $levels = [
            [
                'code' => 'PRE_A',
                'name' => 'Pre-A级别',
                'description' => '英语启蒙阶段，培养语音感知和基础词汇',
                'sort_order' => 1
            ],
            [
                'code' => 'A',
                'name' => 'A级别',
                'description' => '基础阶段，建立基本语言框架',
                'sort_order' => 2
            ],
            [
                'code' => 'B',
                'name' => 'B级别',
                'description' => '进阶阶段，扩展词汇和语法结构',
                'sort_order' => 3
            ],
            [
                'code' => 'C',
                'name' => 'C级别',
                'description' => '中级阶段，提升阅读理解和表达能力',
                'sort_order' => 4
            ],
            [
                'code' => 'D',
                'name' => 'D级别',
                'description' => '高级阶段，培养独立思考和创作能力',
                'sort_order' => 5
            ]
        ];

        $levelModels = [];
        foreach ($levels as $levelData) {
            $level = CourseLevel::create([
                'course_id' => $course->id,
                'name' => $levelData['name'],
                'code' => $levelData['code'],
                'description' => $levelData['description'],
                'sort_order' => $levelData['sort_order'],
                'status' => 'active'
            ]);
            $levelModels[$levelData['code']] = $level;
        }

        // 4. 为每个级别创建单元
        $this->createUnitsForLevel($course->id, $levelModels['PRE_A']->id, 'Pre-A');
        $this->createUnitsForLevel($course->id, $levelModels['A']->id, 'A');
        $this->createUnitsForLevel($course->id, $levelModels['B']->id, 'B');
        $this->createUnitsForLevel($course->id, $levelModels['C']->id, 'C');
        $this->createUnitsForLevel($course->id, $levelModels['D']->id, 'D');

        $this->command->info('原典法英语课程数据创建完成！');
    }

    /**
     * 为指定级别创建单元
     */
    private function createUnitsForLevel(int $courseId, int $levelId, string $levelCode): void
    {
        $unitsData = $this->getUnitsData($levelCode);

        foreach ($unitsData as $index => $unitData) {
            $unit = CourseUnit::create([
                'course_id' => $courseId,
                'level_id' => $levelId,
                'name' => $unitData['name'],
                'description' => $unitData['description'],
                'learning_objectives' => $unitData['objectives'],
                'sort_order' => $index + 1,
                'status' => 'active'
            ]);

            // 为每个单元创建课时
            $this->createLessonsForUnit($unit->id, $unitData['lessons']);
        }
    }

    /**
     * 获取各级别的单元数据
     */
    private function getUnitsData(string $levelCode): array
    {
        $unitsData = [
            'Pre-A' => [
                [
                    'name' => '语音启蒙',
                    'description' => '英语语音系统的初步感知和模仿',
                    'objectives' => '能够识别和模仿基本英语语音；培养语音敏感度；建立听音辨音能力',
                    'lessons' => ['字母发音', '基础音标', '语音游戏', '节奏感知']
                ],
                [
                    'name' => '词汇感知',
                    'description' => '通过多感官体验建立词汇概念',
                    'objectives' => '掌握100个基础词汇；建立词汇与概念的联系；培养词汇记忆策略',
                    'lessons' => ['日常用品', '颜色形状', '数字概念', '身体部位']
                ],
                [
                    'name' => '简单表达',
                    'description' => '基础的日常交流表达',
                    'objectives' => '能进行简单的日常问候；表达基本需求；使用礼貌用语',
                    'lessons' => ['问候用语', '自我介绍', '基本需求', '礼貌表达']
                ]
            ],
            'A' => [
                [
                    'name' => '基础语法',
                    'description' => '英语基本语法结构的学习',
                    'objectives' => '掌握基本句型结构；理解主谓宾概念；能构造简单句子',
                    'lessons' => ['主谓结构', '主谓宾结构', '疑问句', '否定句']
                ],
                [
                    'name' => '词汇扩展',
                    'description' => '扩大词汇量，建立词汇网络',
                    'objectives' => '掌握300个常用词汇；理解词汇分类；建立词汇联想',
                    'lessons' => ['家庭成员', '学校生活', '食物饮料', '动物世界']
                ],
                [
                    'name' => '日常对话',
                    'description' => '基本的日常对话练习',
                    'objectives' => '能进行简单对话；表达个人信息；描述日常活动',
                    'lessons' => ['购物对话', '问路指路', '时间表达', '天气描述']
                ]
            ],
            'B' => [
                [
                    'name' => '语法进阶',
                    'description' => '复杂语法结构的学习和应用',
                    'objectives' => '掌握时态变化；理解复合句结构；能表达复杂概念',
                    'lessons' => ['现在时态', '过去时态', '将来时态', '复合句型']
                ],
                [
                    'name' => '阅读理解',
                    'description' => '短文阅读和理解能力培养',
                    'objectives' => '能阅读简单故事；理解文章大意；掌握阅读策略',
                    'lessons' => ['故事阅读', '信息提取', '推理判断', '词汇猜测']
                ],
                [
                    'name' => '写作入门',
                    'description' => '基础写作技能的培养',
                    'objectives' => '能写简单句子；组织段落结构；表达个人观点',
                    'lessons' => ['句子写作', '段落组织', '描述文写作', '日记写作']
                ]
            ],
            'C' => [
                [
                    'name' => '高级语法',
                    'description' => '复杂语法现象的深入学习',
                    'objectives' => '掌握被动语态；理解虚拟语气；能进行语法分析',
                    'lessons' => ['被动语态', '虚拟语气', '非谓语动词', '从句结构']
                ],
                [
                    'name' => '文学阅读',
                    'description' => '文学作品的阅读和赏析',
                    'objectives' => '能阅读经典文学；理解文学手法；培养文学素养',
                    'lessons' => ['诗歌赏析', '小说阅读', '戏剧理解', '文学批评']
                ],
                [
                    'name' => '议论写作',
                    'description' => '议论文写作技能的培养',
                    'objectives' => '能写议论文；掌握论证方法；表达逻辑观点',
                    'lessons' => ['论点提出', '论据支撑', '逻辑推理', '反驳技巧']
                ]
            ],
            'D' => [
                [
                    'name' => '学术英语',
                    'description' => '学术写作和研究技能',
                    'objectives' => '掌握学术写作规范；能进行文献研究；具备批判思维',
                    'lessons' => ['学术写作', '文献综述', '研究方法', '批判分析']
                ],
                [
                    'name' => '创意表达',
                    'description' => '创造性思维和表达能力',
                    'objectives' => '能进行创意写作；掌握修辞技巧；培养想象力',
                    'lessons' => ['创意写作', '修辞手法', '想象训练', '风格模仿']
                ],
                [
                    'name' => '跨文化交流',
                    'description' => '跨文化理解和交流能力',
                    'objectives' => '理解文化差异；能进行跨文化交流；具备国际视野',
                    'lessons' => ['文化对比', '交流策略', '国际礼仪', '全球视野']
                ]
            ]
        ];

        return $unitsData[$levelCode] ?? [];
    }

    /**
     * 为单元创建课时
     */
    private function createLessonsForUnit(int $unitId, array $lessons): void
    {
        foreach ($lessons as $index => $lessonTitle) {
            Lesson::create([
                'unit_id' => $unitId,
                'name' => $lessonTitle,
                'content' => "第" . ($index + 1) . "课：{$lessonTitle}\n\n通过本课学习，学生将能够掌握{$lessonTitle}相关的知识和技能。\n\n课程内容包括：\n- 听力训练\n- 口语练习\n- 阅读理解\n- 写作表达",
                'duration' => 45,
                'sort_order' => $index + 1,
                'status' => 'active'
            ]);
        }
    }
}
