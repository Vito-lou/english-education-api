# 数据库设计文档

## 核心表结构设计

### 1. 用户相关表

#### users (用户表)
```sql
- id: 主键
- name: 姓名
- email: 邮箱
- phone: 手机号
- password: 密码
- role: 角色 (admin/teacher/student/parent)
- avatar: 头像
- status: 状态 (active/inactive)
- created_at/updated_at: 时间戳
```

#### students (学生表)
```sql
- id: 主键
- user_id: 关联用户表
- student_code: 学生编号
- grade: 年级
- school: 学校
- level: 课程等级 (pre-a/a/b/c/d)
- total_hours: 总课时
- used_hours: 已用课时
- parent_id: 关联家长
- teacher_id: 关联教师
- enrollment_date: 入学日期
- created_at/updated_at
```

#### teachers (教师表)
```sql
- id: 主键
- user_id: 关联用户表
- teacher_code: 教师编号
- specialization: 专业领域
- experience_years: 教学经验
- created_at/updated_at
```

#### parents (家长表)
```sql
- id: 主键
- user_id: 关联用户表
- relationship: 与学生关系
- created_at/updated_at
```

### 2. 课程相关表

#### courses (课程表)
```sql
- id: 主键
- name: 课程名称
- level: 等级 (pre-a/a/b/c/d)
- description: 描述
- objectives: 学习目标
- duration_weeks: 持续周数
- total_lessons: 总课时数
- status: 状态
- created_by: 创建者
- created_at/updated_at
```

#### lessons (课时表)
```sql
- id: 主键
- course_id: 关联课程
- title: 课时标题
- content: 课时内容
- objectives: 学习目标
- materials: 教学材料
- homework: 作业内容
- order: 排序
- duration_minutes: 时长
- created_at/updated_at
```

#### lesson_materials (课时材料表)
```sql
- id: 主键
- lesson_id: 关联课时
- type: 类型 (audio/video/document/image)
- title: 标题
- file_path: 文件路径
- file_size: 文件大小
- order: 排序
- created_at/updated_at
```

### 3. 学习记录表

#### student_lessons (学生课时记录)
```sql
- id: 主键
- student_id: 学生ID
- lesson_id: 课时ID
- teacher_id: 教师ID
- scheduled_at: 计划时间
- started_at: 开始时间
- completed_at: 完成时间
- status: 状态 (scheduled/in_progress/completed/cancelled)
- attendance: 出勤状态
- notes: 课堂笔记
- homework_status: 作业状态
- rating: 评分
- created_at/updated_at
```

#### learning_progress (学习进度)
```sql
- id: 主键
- student_id: 学生ID
- lesson_id: 课时ID
- skill_type: 技能类型 (listening/speaking/reading/writing)
- progress_percentage: 进度百分比
- mastery_level: 掌握程度 (1-5)
- last_practiced_at: 最后练习时间
- created_at/updated_at
```

### 4. 知识点管理表

#### knowledge_points (知识点表)
```sql
- id: 主键
- name: 知识点名称
- type: 类型 (vocabulary/grammar/sentence_pattern)
- level: 等级
- grade: 年级
- description: 描述
- examples: 例句
- created_at/updated_at
```

#### lesson_knowledge_points (课时知识点关联)
```sql
- id: 主键
- lesson_id: 课时ID
- knowledge_point_id: 知识点ID
- importance: 重要程度 (1-5)
- created_at/updated_at
```

### 5. 评估与反馈表

#### assessments (评估表)
```sql
- id: 主键
- student_id: 学生ID
- lesson_id: 课时ID
- type: 评估类型 (quiz/test/homework)
- score: 得分
- max_score: 满分
- feedback: 反馈
- assessed_at: 评估时间
- created_at/updated_at
```

#### student_achievements (学生成就)
```sql
- id: 主键
- student_id: 学生ID
- achievement_type: 成就类型
- title: 成就标题
- description: 描述
- earned_at: 获得时间
- is_shared: 是否已分享
- created_at/updated_at
```

## 索引设计

### 主要索引
- students: user_id, teacher_id, parent_id
- lessons: course_id
- student_lessons: student_id, lesson_id, scheduled_at
- learning_progress: student_id, lesson_id
- assessments: student_id, lesson_id

## 数据关系

### 一对多关系
- Teacher -> Students
- Parent -> Students  
- Course -> Lessons
- Student -> StudentLessons
- Student -> LearningProgress

### 多对多关系
- Lessons <-> KnowledgePoints
- Students <-> Lessons (通过 student_lessons)

## 扩展性考虑

### 预留字段
- 各表预留 metadata JSON 字段用于存储扩展信息
- 支持未来AI功能的数据结构

### 分表策略
- 学习记录表按月分表
- 大文件存储考虑OSS