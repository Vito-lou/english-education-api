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
    echo "开始创建素材中心测试数据...\n";

    // 1. 创建知识标签
    echo "\n=== 创建知识标签 ===\n";

    $tags = [
        // K12标签
        ['tag_name' => 'Grade 1', 'tag_system' => 'k12', 'description' => '一年级水平', 'meta' => json_encode(['grade_level' => '1', 'subject' => 'English'])],
        ['tag_name' => 'Grade 2', 'tag_system' => 'k12', 'description' => '二年级水平', 'meta' => json_encode(['grade_level' => '2', 'subject' => 'English'])],
        ['tag_name' => 'Grade 3', 'tag_system' => 'k12', 'description' => '三年级水平', 'meta' => json_encode(['grade_level' => '3', 'subject' => 'English'])],

        // 剑桥标签
        ['tag_name' => 'A1 Beginner', 'tag_system' => 'cambridge', 'description' => '剑桥A1初级水平', 'meta' => json_encode(['cefr_level' => 'A1', 'exam_type' => 'KET'])],
        ['tag_name' => 'A2 Elementary', 'tag_system' => 'cambridge', 'description' => '剑桥A2基础水平', 'meta' => json_encode(['cefr_level' => 'A2', 'exam_type' => 'KET'])],
        ['tag_name' => 'B1 Intermediate', 'tag_system' => 'cambridge', 'description' => '剑桥B1中级水平', 'meta' => json_encode(['cefr_level' => 'B1', 'exam_type' => 'PET'])],

        // 雅思标签
        ['tag_name' => 'IELTS Listening', 'tag_system' => 'ielts', 'description' => '雅思听力', 'meta' => json_encode(['skill_type' => 'listening', 'difficulty_level' => 'Intermediate'])],
        ['tag_name' => 'IELTS Reading', 'tag_system' => 'ielts', 'description' => '雅思阅读', 'meta' => json_encode(['skill_type' => 'reading', 'difficulty_level' => 'Intermediate'])],
    ];

    $tagIds = [];
    foreach ($tags as $tag) {
        $tagId = Capsule::table('knowledge_tags')->insertGetId([
            'tag_name' => $tag['tag_name'],
            'tag_system' => $tag['tag_system'],
            'description' => $tag['description'],
            'meta' => $tag['meta'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tagIds[$tag['tag_name']] = $tagId;
        echo "✅ 创建标签: {$tag['tag_name']} (ID: $tagId)\n";
    }

    // 2. 创建知识点
    echo "\n=== 创建知识点 ===\n";

    $knowledgePoints = [
        // 词汇类
        [
            'name' => 'apple',
            'type' => 'vocabulary',
            'definition_en' => 'A round fruit with red or green skin',
            'definition_cn' => '苹果，一种圆形的红色或绿色水果',
            'explanation' => 'Apple is a common fruit that grows on trees. It can be eaten raw or used in cooking.',
            'examples' => [
                ['example_en' => 'I eat an apple every day for breakfast.', 'example_cn' => '我每天早餐都吃一个苹果。'],
                ['example_en' => 'The apple tree is full of red apples.', 'example_cn' => '苹果树上结满了红苹果。'],
            ],
            'tags' => ['Grade 1', 'A1 Beginner']
        ],
        [
            'name' => 'beautiful',
            'type' => 'vocabulary',
            'definition_en' => 'Having qualities that give pleasure to the senses',
            'definition_cn' => '美丽的，漂亮的',
            'explanation' => 'Used to describe something that looks very nice or attractive.',
            'examples' => [
                ['example_en' => 'The sunset is very beautiful tonight.', 'example_cn' => '今晚的日落非常美丽。'],
                ['example_en' => 'She has beautiful eyes.', 'example_cn' => '她有一双美丽的眼睛。'],
            ],
            'tags' => ['Grade 2', 'A2 Elementary']
        ],
        [
            'name' => 'adventure',
            'type' => 'vocabulary',
            'definition_en' => 'An exciting or dangerous experience',
            'definition_cn' => '冒险，历险',
            'explanation' => 'An adventure is an exciting experience that often involves some risk or danger.',
            'examples' => [
                ['example_en' => 'Going camping in the mountains was a great adventure.', 'example_cn' => '去山里露营是一次很棒的冒险。'],
                ['example_en' => 'The children love adventure stories.', 'example_cn' => '孩子们喜欢冒险故事。'],
                ['example_en' => 'Life is an adventure waiting to be explored.', 'example_cn' => '生活是一场等待探索的冒险。'],
            ],
            'tags' => ['Grade 3', 'B1 Intermediate']
        ],

        // 语法类
        [
            'name' => 'Present Simple',
            'type' => 'grammar',
            'definition_en' => 'A verb tense used to describe habits, facts, and general truths',
            'definition_cn' => '一般现在时，用于描述习惯、事实和普遍真理',
            'explanation' => 'The present simple is formed with the base form of the verb. For third person singular, add -s or -es.',
            'examples' => [
                ['example_en' => 'She plays tennis every weekend.', 'example_cn' => '她每个周末都打网球。'],
                ['example_en' => 'The sun rises in the east.', 'example_cn' => '太阳从东方升起。'],
                ['example_en' => 'I work in a bank.', 'example_cn' => '我在银行工作。'],
            ],
            'tags' => ['Grade 2', 'A1 Beginner']
        ],
        [
            'name' => 'Past Continuous',
            'type' => 'grammar',
            'definition_en' => 'A verb tense used to describe actions that were ongoing in the past',
            'definition_cn' => '过去进行时，用于描述过去正在进行的动作',
            'explanation' => 'Formed with was/were + verb-ing. Used for actions in progress at a specific time in the past.',
            'examples' => [
                ['example_en' => 'I was reading a book when you called.', 'example_cn' => '你打电话时我正在看书。'],
                ['example_en' => 'They were playing football at 3 PM yesterday.', 'example_cn' => '昨天下午3点他们正在踢足球。'],
            ],
            'tags' => ['Grade 3', 'B1 Intermediate']
        ],

        // 短语类
        [
            'name' => 'look after',
            'type' => 'phrase',
            'definition_en' => 'To take care of someone or something',
            'definition_cn' => '照顾，照料',
            'explanation' => 'A phrasal verb meaning to be responsible for someone or something.',
            'examples' => [
                ['example_en' => 'Can you look after my cat while I\'m away?', 'example_cn' => '我不在的时候你能照顾我的猫吗？'],
                ['example_en' => 'She looks after her elderly parents.', 'example_cn' => '她照顾年迈的父母。'],
            ],
            'tags' => ['A2 Elementary', 'IELTS Reading']
        ],

        // 句型类
        [
            'name' => 'There is/are',
            'type' => 'sentence_pattern',
            'definition_en' => 'A structure used to say that something exists or is present',
            'definition_cn' => '存在句，用于表示某物的存在',
            'explanation' => 'Use "There is" with singular nouns and "There are" with plural nouns.',
            'examples' => [
                ['example_en' => 'There is a book on the table.', 'example_cn' => '桌子上有一本书。'],
                ['example_en' => 'There are many students in the classroom.', 'example_cn' => '教室里有很多学生。'],
                ['example_en' => 'There isn\'t any milk in the fridge.', 'example_cn' => '冰箱里没有牛奶。'],
            ],
            'tags' => ['Grade 1', 'A1 Beginner']
        ],
    ];

    $knowledgePointIds = [];
    foreach ($knowledgePoints as $point) {
        $pointId = Capsule::table('knowledge_points')->insertGetId([
            'name' => $point['name'],
            'type' => $point['type'],
            'definition_en' => $point['definition_en'],
            'definition_cn' => $point['definition_cn'],
            'explanation' => $point['explanation'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 创建例句
        if (isset($point['examples'])) {
            foreach ($point['examples'] as $index => $example) {
                Capsule::table('knowledge_point_examples')->insert([
                    'knowledge_point_id' => $pointId,
                    'example_en' => $example['example_en'],
                    'example_cn' => $example['example_cn'] ?? null,
                    'sequence' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 关联标签
        foreach ($point['tags'] as $tagName) {
            if (isset($tagIds[$tagName])) {
                Capsule::table('knowledge_point_tags')->insert([
                    'knowledge_point_id' => $pointId,
                    'knowledge_tag_id' => $tagIds[$tagName],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $knowledgePointIds[$point['name']] = $pointId;
        echo "✅ 创建知识点: {$point['name']} (ID: $pointId)\n";
    }

    // 3. 创建故事
    echo "\n=== 创建故事 ===\n";

    $stories = [
        [
            'title' => 'The Little Red Hen',
            'description' => 'A classic tale about hard work and sharing',
            'author' => 'Traditional',
            'difficulty_level' => 'A1',
            'has_chapters' => false,
            'content' => 'Once upon a time, there was a little red hen who lived on a farm. She found some wheat seeds and asked her friends to help her plant them. "Not I," said the cat. "Not I," said the dog. "Not I," said the pig. So the little red hen planted the wheat herself. When it was time to harvest, she asked again for help, but no one wanted to help. Finally, when the bread was baked, everyone wanted to eat it, but the little red hen said, "No, I will eat it myself because I did all the work."',
            'knowledge_points' => ['apple', 'Present Simple', 'There is/are']
        ],
        [
            'title' => 'Alice\'s Adventures',
            'description' => 'A young girl\'s magical journey through wonderland',
            'author' => 'Lewis Carroll (Adapted)',
            'difficulty_level' => 'B1',
            'has_chapters' => true,
            'chapters' => [
                [
                    'chapter_number' => 1,
                    'chapter_title' => 'Down the Rabbit Hole',
                    'content' => 'Alice was beginning to get very tired of sitting by her sister on the bank, and of having nothing to do. Once or twice she had peeped into the book her sister was reading, but it had no pictures or conversations in it. "What is the use of a book," thought Alice, "without pictures or conversations?" So she was considering in her own mind, as well as she could, for the hot day made her feel very sleepy and stupid, whether the pleasure of making a daisy-chain would be worth the trouble of getting up and picking the daisies, when suddenly a White Rabbit with pink eyes ran close by her.'
                ],
                [
                    'chapter_number' => 2,
                    'chapter_title' => 'The Pool of Tears',
                    'content' => 'Alice opened the door and found that it led into a small passage, not much larger than a rat-hole. She knelt down and looked along the passage into the loveliest garden you ever saw. How she longed to get out of that dark hall, and wander about among those beds of bright flowers and those cool fountains, but she could not even get her head through the doorway. "Oh, how I wish I could shut up like a telescope! I think I could, if I only knew how to begin."'
                ]
            ],
            'knowledge_points' => ['beautiful', 'adventure', 'Past Continuous', 'look after']
        ],
        [
            'title' => 'The Friendly Robot',
            'description' => 'A modern story about friendship and technology',
            'author' => 'Modern Tales',
            'difficulty_level' => 'A2',
            'has_chapters' => false,
            'content' => 'In a small town, there lived a boy named Tom who loved technology. One day, he found a small robot in his garage. The robot could talk and move around. "Hello, I am Robo," said the robot. "I am here to be your friend." Tom was very excited. He and Robo played games, did homework together, and had many adventures. The robot helped Tom with his studies and Tom taught the robot about human emotions. They became the best of friends and learned that friendship can exist between anyone, even a boy and a robot.',
            'knowledge_points' => ['apple', 'beautiful', 'Present Simple']
        ]
    ];

    foreach ($stories as $story) {
        $storyId = Capsule::table('stories')->insertGetId([
            'title' => $story['title'],
            'description' => $story['description'],
            'author' => $story['author'],
            'difficulty_level' => $story['difficulty_level'],
            'has_chapters' => $story['has_chapters'],
            'content' => $story['has_chapters'] ? null : $story['content'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ 创建故事: {$story['title']} (ID: $storyId)\n";

        // 如果有章节，创建章节
        if ($story['has_chapters'] && isset($story['chapters'])) {
            foreach ($story['chapters'] as $chapter) {
                $chapterId = Capsule::table('story_chapters')->insertGetId([
                    'story_id' => $storyId,
                    'chapter_number' => $chapter['chapter_number'],
                    'chapter_title' => $chapter['chapter_title'],
                    'content' => $chapter['content'],
                    'word_count' => str_word_count($chapter['content']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                echo "  ✅ 创建章节: {$chapter['chapter_title']} (ID: $chapterId)\n";
            }
        }

        // 关联知识点
        foreach ($story['knowledge_points'] as $pointName) {
            if (isset($knowledgePointIds[$pointName])) {
                Capsule::table('story_knowledge_relations')->insert([
                    'story_id' => $storyId,
                    'knowledge_point_id' => $knowledgePointIds[$pointName],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    echo "\n🎉 素材中心测试数据创建完成！\n";
    echo "📊 统计信息：\n";
    echo "- 知识标签: " . count($tags) . " 个\n";
    echo "- 知识点: " . count($knowledgePoints) . " 个\n";
    echo "- 故事: " . count($stories) . " 个\n";
    echo "- 章节: 2 个\n";

} catch (Exception $e) {
    echo "❌ 错误: " . $e->getMessage() . "\n";
    exit(1);
}
