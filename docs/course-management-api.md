# 课程管理系统 API 文档

## 基础信息
- **Base URL**: `/api/admin`
- **认证方式**: Bearer Token
- **响应格式**: JSON

## 通用响应格式
```json
{
  "code": 200,
  "message": "success",
  "data": {...}
}
```

## 1. 课程管理 (Courses)

### 1.1 获取课程列表
```http
GET /admin/courses
```

**查询参数**:
- `subject_id` (可选): 科目ID筛选

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": [
    {
      "id": 1,
      "subject_id": 1,
      "name": "原典法英语",
      "code": "yuandian_english",
      "description": "基于原典法的英语教学课程",
      "has_levels": true,
      "sort_order": 1,
      "status": "active",
      "subject": {
        "id": 1,
        "name": "英语"
      },
      "levels": [...],
      "units": [...]
    }
  ]
}
```

### 1.2 创建课程
```http
POST /admin/courses
```

**请求体**:
```json
{
  "subject_id": 1,
  "name": "原典法英语",
  "code": "yuandian_english",
  "description": "基于原典法的英语教学课程",
  "has_levels": true,
  "sort_order": 1
}
```

### 1.3 获取课程详情
```http
GET /admin/courses/{id}
```

**响应**: 包含完整的课程信息，包括级别、单元、课时的嵌套数据

### 1.4 更新课程
```http
PUT /admin/courses/{id}
```

### 1.5 删除课程
```http
DELETE /admin/courses/{id}
```

### 1.6 获取科目列表
```http
GET /admin/subjects
```

## 2. 级别管理 (Course Levels)

### 2.1 获取级别列表
```http
GET /admin/course-levels
```

**查询参数**:
- `course_id` (可选): 课程ID筛选

### 2.2 创建级别
```http
POST /admin/course-levels
```

**请求体**:
```json
{
  "course_id": 1,
  "name": "Pre-A",
  "code": "pre_a",
  "description": "入门级别",
  "sort_order": 1
}
```

**验证规则**:
- `course_id`: 必填，必须存在
- `name`: 必填，最大255字符
- `code`: 必填，最大100字符，同一课程下唯一
- `description`: 可选
- `sort_order`: 可选，整数

### 2.3 获取级别详情
```http
GET /admin/course-levels/{id}
```

### 2.4 更新级别
```http
PUT /admin/course-levels/{id}
```

### 2.5 删除级别
```http
DELETE /admin/course-levels/{id}
```

**限制**: 如果级别下有单元，无法删除

## 3. 单元管理 (Course Units)

### 3.1 获取单元列表
```http
GET /admin/course-units
```

**查询参数**:
- `course_id` (可选): 课程ID筛选
- `level_id` (可选): 级别ID筛选，支持 `null` 查询无级别单元

### 3.2 创建单元
```http
POST /admin/course-units
```

**请求体**:
```json
{
  "course_id": 1,
  "level_id": 1,
  "name": "Unit 1 - Basic Greetings",
  "description": "学习基本的问候语和自我介绍",
  "learning_objectives": "1. 掌握常用问候语\n2. 能够进行简单自我介绍",
  "sort_order": 1
}
```

**验证规则**:
- `course_id`: 必填，必须存在
- `level_id`: 可选，如果提供必须属于该课程
- `name`: 必填，最大255字符
- `description`: 必填
- `learning_objectives`: 可选
- `sort_order`: 可选，整数

### 3.3 获取单元详情
```http
GET /admin/course-units/{id}
```

### 3.4 更新单元
```http
PUT /admin/course-units/{id}
```

### 3.5 删除单元
```http
DELETE /admin/course-units/{id}
```

**限制**: 如果单元下有课时，无法删除

## 4. 课时管理 (Lessons)

### 4.1 获取课时列表
```http
GET /admin/lessons
```

**查询参数**:
- `unit_id` (可选): 单元ID筛选

### 4.2 创建课时
```http
POST /admin/lessons
```

**请求体**:
```json
{
  "unit_id": 1,
  "name": "Lesson 1 - Hello World",
  "content": "本课时学习基本的问候语...",
  "duration": 15,
  "sort_order": 1
}
```

**验证规则**:
- `unit_id`: 必填，必须存在
- `name`: 必填，最大255字符
- `content`: 必填
- `duration`: 可选，整数，分钟数
- `sort_order`: 可选，整数

### 4.3 获取课时详情
```http
GET /admin/lessons/{id}
```

### 4.4 更新课时
```http
PUT /admin/lessons/{id}
```

### 4.5 删除课时
```http
DELETE /admin/lessons/{id}
```

## 5. 数据关系

### 5.1 层级结构
```
Course (课程)
├── CourseLevel (级别) - 可选
│   └── CourseUnit (单元)
│       └── Lesson (课时)
└── CourseUnit (单元) - 无级别
    └── Lesson (课时)
```

### 5.2 关键约束
1. **级别删除**: 有单元的级别不能删除
2. **单元删除**: 有课时的单元不能删除
3. **级别归属**: 单元的级别必须属于同一课程
4. **代码唯一**: 同一课程下级别代码唯一

## 6. 错误响应

### 6.1 验证错误 (400)
```json
{
  "code": 400,
  "message": "验证失败",
  "errors": {
    "name": ["课程名称不能为空"],
    "code": ["课程代码已存在"]
  }
}
```

### 6.2 资源不存在 (404)
```json
{
  "code": 404,
  "message": "资源不存在"
}
```

### 6.3 业务逻辑错误 (400)
```json
{
  "code": 400,
  "message": "该级别下还有课程单元，无法删除"
}
```

## 7. 权限要求

### 7.1 所需权限
- **课程管理权限** (`course_management`): 访问所有课程管理接口

### 7.2 权限验证
所有接口都需要通过中间件验证用户权限，确保用户具有 `course_management` 权限。

## 8. 使用示例

### 8.1 创建完整课程结构
```javascript
// 1. 创建课程
const course = await api.post('/admin/courses', {
  subject_id: 1,
  name: '原典法英语',
  code: 'yuandian_english',
  has_levels: true
});

// 2. 创建级别
const level = await api.post('/admin/course-levels', {
  course_id: course.data.id,
  name: 'Pre-A',
  code: 'pre_a'
});

// 3. 创建单元
const unit = await api.post('/admin/course-units', {
  course_id: course.data.id,
  level_id: level.data.id,
  name: 'Unit 1 - Greetings',
  description: '学习基本问候语'
});

// 4. 创建课时
const lesson = await api.post('/admin/lessons', {
  unit_id: unit.data.id,
  name: 'Lesson 1 - Hello',
  content: '学习Hello的用法',
  duration: 15
});
```

### 8.2 查询特定级别的单元
```javascript
const units = await api.get('/admin/course-units', {
  params: {
    course_id: 1,
    level_id: 1
  }
});
```

## 9. 注意事项

1. **数据完整性**: 删除操作会检查关联数据，确保数据完整性
2. **排序**: 所有列表接口都按 `sort_order` 排序
3. **状态管理**: 所有实体都有 `status` 字段，支持启用/禁用
4. **机构隔离**: 所有数据都与机构关联，确保数据隔离
5. **事务处理**: 复杂操作使用数据库事务确保一致性
