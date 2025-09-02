# 知识点管理系统开发日志

**开发日期**: 2025 年 9 月 2 日  
**开发者**: AI Assistant  
**版本**: V1.0

## 📋 项目概述

本次开发完成了完整的知识点管理系统，包括单元编辑器增强、作业布置功能扩展，以及数据库结构优化。该系统允许教师在单元中管理词汇、句型、语法等知识点，并在布置作业时精确选择要练习的知识点。

## 🎯 核心功能

### 1. 单元知识点管理

-   支持三种知识点类型：词汇(vocabulary)、句型(sentence_pattern)、语法(grammar)
-   智能文本选择标记功能
-   自动知识点提取功能
-   实时高亮预览
-   拖拽排序支持

### 2. 作业布置增强

-   班级 → 单元 → 知识点的级联选择
-   历史布置情况提示
-   智能推荐未布置的知识点
-   批量选择操作

### 3. 语音 API 集成准备

-   移除手动音标输入
-   移除音频文件上传
-   为浏览器原生语音 API 做准备

## 🗄️ 数据库变更

### 新增表结构

#### `unit_knowledge_points` - 单元知识点表

```sql
CREATE TABLE unit_knowledge_points (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    unit_id BIGINT NOT NULL COMMENT '关联单元',
    type ENUM('vocabulary', 'sentence_pattern', 'grammar') COMMENT '知识点类型',
    content VARCHAR(255) NOT NULL COMMENT '知识点内容',
    image_url VARCHAR(255) NULL COMMENT '配图URL',
    explanation TEXT NULL COMMENT '解释说明',
    example_sentences JSON NULL COMMENT '例句（JSON格式）',
    sort_order INT DEFAULT 0 COMMENT '排序',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT '状态',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (unit_id) REFERENCES course_units(id) ON DELETE CASCADE,
    INDEX idx_unit_type (unit_id, type),
    INDEX idx_unit_sort (unit_id, sort_order)
);
```

#### `homework_knowledge_points` - 作业知识点关联表

```sql
CREATE TABLE homework_knowledge_points (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    homework_assignment_id BIGINT NOT NULL COMMENT '关联作业',
    knowledge_point_id BIGINT NOT NULL COMMENT '关联知识点',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (homework_assignment_id) REFERENCES homework_assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (knowledge_point_id) REFERENCES unit_knowledge_points(id) ON DELETE CASCADE,
    UNIQUE KEY homework_knowledge_unique (homework_assignment_id, knowledge_point_id)
);
```

### 表结构修改

#### `course_units` - 添加故事内容字段

```sql
ALTER TABLE course_units ADD COLUMN story_content LONGTEXT NULL COMMENT '单元故事文本内容' AFTER learning_objectives;
```

#### `homework_assignments` - 添加单元关联

```sql
ALTER TABLE homework_assignments ADD COLUMN unit_id BIGINT NULL COMMENT '关联单元' AFTER class_id;
ALTER TABLE homework_assignments ADD FOREIGN KEY (unit_id) REFERENCES course_units(id) ON DELETE SET NULL;
ALTER TABLE homework_assignments ADD INDEX idx_class_unit (class_id, unit_id);
```

## 🔧 后端开发

### 新增模型

#### `UnitKnowledgePoint` 模型

-   完整的 CRUD 操作
-   关联关系：belongsTo(CourseUnit), belongsToMany(HomeworkAssignment)
-   作用域查询：byType, byUnit, byStatus
-   类型名称获取：getTypeNameAttribute

#### 模型关系扩展

-   `CourseUnit::knowledgePoints()` - hasMany 关系
-   `HomeworkAssignment::unit()` - belongsTo 关系
-   `HomeworkAssignment::knowledgePoints()` - belongsToMany 关系

### 新增控制器

#### `UnitKnowledgePointController`

-   `index()` - 获取知识点列表（支持筛选）
-   `store()` - 创建知识点（支持图片上传）
-   `show()` - 获取知识点详情
-   `update()` - 更新知识点
-   `destroy()` - 删除知识点（自动清理文件）
-   `updateSort()` - 批量排序

#### `HomeworkAssignmentController` 扩展

-   `getUnitsForClass()` - 获取班级可用单元
-   `getKnowledgePointsForUnit()` - 获取单元知识点（含历史布置信息）
-   `getUnitHomeworkHistory()` - 获取单元作业历史
-   更新 create/update 方法支持知识点关联

#### `CourseUnitController` 扩展

-   支持故事内容字段
-   支持知识点批量创建/更新
-   智能处理新增/编辑/删除知识点

### API 路由

```php
// 知识点管理
Route::post('unit-knowledge-points/update-sort', [UnitKnowledgePointController::class, 'updateSort']);
Route::apiResource('unit-knowledge-points', UnitKnowledgePointController::class);

// 作业管理扩展
Route::get('homework-assignments/classes/{classId}/units', [HomeworkAssignmentController::class, 'getUnitsForClass']);
Route::get('homework-assignments/units/{unitId}/knowledge-points', [HomeworkAssignmentController::class, 'getKnowledgePointsForUnit']);
Route::get('homework-assignments/classes/{classId}/units/{unitId}/history', [HomeworkAssignmentController::class, 'getUnitHomeworkHistory']);
```

## 🎨 前端开发

### 单元编辑器重构 (`UnitEditor.tsx`)

#### 新增功能

-   **Tab 式界面**：基本信息、故事内容、知识点管理
-   **智能文本选择**：选择文字 → 点击按钮 → 自动创建知识点
-   **实时高亮预览**：在预览区域高亮显示已标记的知识点
-   **智能提取**：自动分析故事文本，提取可能的知识点
-   **可视化管理**：统计卡片、最近添加列表

#### 核心组件

```typescript
interface KnowledgePoint {
    id?: number;
    unit_id?: number;
    type: "vocabulary" | "sentence_pattern" | "grammar";
    content: string;
    image_url?: string;
    explanation?: string;
    example_sentences?: string[];
    sort_order: number;
    status: "active" | "inactive";
}
```

#### 关键功能实现

-   `handleTextSelection()` - 文本选择处理
-   `markSelectedText()` - 标记选中文本为知识点
-   `intelligentExtract()` - 智能提取知识点
-   `renderHighlightedText()` - 渲染高亮文本

### 作业布置增强 (`HomeworkAssignments.tsx`)

#### 新增功能

-   **级联选择**：班级 → 单元 → 知识点
-   **历史提示**：显示已布置过的知识点（橙色图标）
-   **批量操作**：全选/清空知识点
-   **智能回显**：编辑时正确显示之前的选择

#### 数据流程

1. 选择班级 → 查询该班级课程级别的单元
2. 选择单元 → 查询该单元的知识点 + 历史布置情况
3. 选择知识点 → 多选需要练习的知识点
4. 提交作业 → 创建作业并关联知识点

## 🔄 数据库迁移记录

### 执行的迁移文件

1. `2025_09_02_080846_create_unit_knowledge_points_table.php`
2. `2025_09_02_080957_create_homework_knowledge_points_table.php`
3. `2025_09_02_081031_add_story_content_to_course_units_table.php`
4. `2025_09_02_085401_add_unit_id_to_homework_assignments_table.php`
5. `2025_09_02_094701_remove_pronunciation_and_audio_from_unit_knowledge_points_table.php`

### 迁移顺序

```bash
php artisan migrate
```

## 🐛 问题修复记录

### 1. Select 组件空值错误

**问题**: `A <Select.Item /> must have a value prop that is not an empty string`
**解决**: 使用"none"作为占位值，在处理时转换为空字符串

### 2. 模型关系类型错误

**问题**: `Return value must be of type BelongsToMany`
**解决**: 添加正确的`use Illuminate\Database\Eloquent\Relations\BelongsToMany;`导入

### 3. 作业更新不保存单元和知识点

**问题**: 后端 update 方法没有处理 unit_id 和 knowledge_point_ids
**解决**: 完善验证规则和更新逻辑，使用 sync()方法同步知识点关联

### 4. 编辑回显问题

**问题**: 编辑作业时不能正确回显单元和知识点选择
**解决**: 添加 useEffect 监听，手动触发数据查询，智能保持知识点选择

## 🎯 核心设计思路

### 1. 知识点标记方式演进

-   **❌ 原方案**: 特殊标记语法 `[vocabulary]word[/vocabulary]`
-   **✅ 新方案**: 选择文字 → 点击按钮 → 自动标记

### 2. 语音功能优化

-   **❌ 原方案**: 手动输入音标 + 上传音频文件
-   **✅ 新方案**: 浏览器原生 Speech API + 在线语音服务

### 3. 作业关联设计

-   **选择**: 关联单元 > 关联课时
-   **原因**: 知识点按单元组织，支持同单元多课时重复练习

## 🚀 技术亮点

### 1. 智能文本处理

-   正则表达式提取英文单词
-   过滤常见词汇，只保留生词
-   自动识别疑问句、感叹句等句型

### 2. 高效的数据同步

-   使用 Eloquent 的 sync()方法处理多对多关系
-   事务保证数据一致性
-   智能区分新增/更新/删除操作

### 3. 用户体验优化

-   实时高亮预览
-   智能级联选择
-   历史布置提示
-   批量操作支持

## 📝 使用说明

### 教师操作流程

#### 1. 创建/编辑单元

1. 填写基本信息（名称、描述、学习目标）
2. 在故事内容 Tab 中输入完整故事
3. 选择文字 → 点击标记按钮 → 创建知识点
4. 在知识点管理 Tab 中完善详细信息

#### 2. 布置作业

1. 选择班级
2. 选择单元（可选）
3. 从知识点列表中多选要练习的内容
4. 填写作业要求和截止时间
5. 提交作业

### 开发者注意事项

#### 1. 数据库一致性

-   所有迁移文件已按顺序执行
-   外键约束确保数据完整性
-   软删除支持数据恢复

#### 2. API 设计原则

-   RESTful 风格
-   统一的响应格式
-   完善的权限检查
-   详细的错误处理

#### 3. 前端组件复用

-   知识点选择器可复用
-   文件上传组件标准化
-   表单验证统一处理

## 🔮 后续开发建议

### 第三阶段：家长端 H5 开发

1. 知识点展示界面
2. 语音播放功能（使用 Web Speech API）
3. 学习进度跟踪
4. 互动练习功能

### 功能扩展建议

1. 知识点难度等级
2. 学习路径推荐
3. 个性化练习生成
4. 学习效果分析

### 技术优化建议

1. 缓存热门知识点
2. 异步处理大量数据
3. 图片压缩和 CDN
4. 搜索功能优化

## 💻 关键代码示例

### 1. 智能知识点提取算法

```javascript
// 前端智能提取实现
const intelligentExtract = () => {
  const text = formData.story_content || '';
  if (!text.trim()) return;

  const extractedPoints = [];
  let currentId = Date.now();

  // 1. 提取英文词汇（过滤常见词）
  const englishWords = text.match(/\b[A-Za-z]{3,}\b/g) || [];
  const uniqueWords = [...new Set(englishWords.map(w => w.toLowerCase()))];
  const commonWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', ...];

  const potentialVocabulary = uniqueWords.filter(word =>
    word.length >= 4 &&
    !commonWords.includes(word) &&
    !knowledgePoints.some(p => p.content.toLowerCase() === word)
  ).slice(0, 10);

  // 2. 提取句型（疑问句、感叹句）
  const questionPatterns = text.match(/[A-Z][^.!?]*\?/g) || [];
  const exclamationPatterns = text.match(/[A-Z][^.!?]*!/g) || [];

  // 生成知识点对象...
};
```

### 2. 后端知识点同步逻辑

```php
// CourseUnitController 中的知识点处理
DB::beginTransaction();
try {
    // 更新单元基本信息
    $courseUnit->update($basicData);

    // 智能处理知识点
    if ($request->has('knowledge_points')) {
        $existingIds = [];

        foreach ($request->knowledge_points as $index => $pointData) {
            if (isset($pointData['id']) && $pointData['id']) {
                // 更新现有知识点
                $knowledgePoint = UnitKnowledgePoint::find($pointData['id']);
                if ($knowledgePoint && $knowledgePoint->unit_id === $courseUnit->id) {
                    $knowledgePoint->update($pointData);
                    $existingIds[] = $pointData['id'];
                }
            } else {
                // 创建新知识点
                $newPoint = UnitKnowledgePoint::create([
                    'unit_id' => $courseUnit->id,
                    ...$pointData,
                    'status' => 'active',
                ]);
                $existingIds[] = $newPoint->id;
            }
        }

        // 删除不在列表中的知识点
        UnitKnowledgePoint::where('unit_id', $courseUnit->id)
            ->whereNotIn('id', $existingIds)
            ->delete();
    }

    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### 3. 前端级联选择实现

```typescript
// 作业布置中的智能级联选择
const { data: unitsData, refetch: refetchUnits } = useQuery({
    queryKey: ["class-units", formData.class_id],
    queryFn: async () => {
        if (!formData.class_id) return { data: [] };
        const response = await api.get(
            `/admin/homework-assignments/classes/${formData.class_id}/units`
        );
        return response.data;
    },
    enabled: dialogOpen && !!formData.class_id,
});

// 编辑时确保数据正确加载
useEffect(() => {
    if (editingHomework && dialogOpen) {
        if (formData.unit_id && (!unitsData || unitsData.data.length === 0)) {
            refetchUnits();
        }
    }
}, [editingHomework, dialogOpen, formData.unit_id]);
```

## 🔍 测试用例

### 1. 单元知识点管理测试

```php
// 测试创建知识点
public function test_create_knowledge_point()
{
    $unit = CourseUnit::factory()->create();

    $response = $this->postJson('/api/admin/unit-knowledge-points', [
        'unit_id' => $unit->id,
        'type' => 'vocabulary',
        'content' => 'apple',
        'explanation' => 'A red fruit',
        'example_sentences' => ['I eat an apple.'],
        'sort_order' => 0,
        'status' => 'active',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure(['code', 'message', 'data']);
}

// 测试知识点关联作业
public function test_homework_knowledge_point_association()
{
    $homework = HomeworkAssignment::factory()->create();
    $knowledgePoint = UnitKnowledgePoint::factory()->create();

    $homework->knowledgePoints()->attach($knowledgePoint->id);

    $this->assertTrue($homework->knowledgePoints->contains($knowledgePoint));
}
```

### 2. 前端组件测试

```typescript
// 测试知识点选择器
describe("KnowledgePointSelector", () => {
    it("should display knowledge points correctly", () => {
        const mockPoints = [
            {
                id: 1,
                type: "vocabulary",
                content: "apple",
                previously_assigned: false,
            },
            {
                id: 2,
                type: "grammar",
                content: "present tense",
                previously_assigned: true,
            },
        ];

        render(<KnowledgePointSelector points={mockPoints} />);

        expect(screen.getByText("apple")).toBeInTheDocument();
        expect(screen.getByText("present tense")).toBeInTheDocument();
        expect(screen.getByTitle("之前已布置过")).toBeInTheDocument();
    });
});
```

## 📊 性能优化记录

### 1. 数据库查询优化

```php
// 使用预加载避免N+1查询
$assignments = HomeworkAssignment::with([
    'class.course',
    'class.level',
    'unit.course',
    'knowledgePoints' => function($query) {
        $query->orderBy('sort_order');
    }
])->paginate(15);

// 添加必要的索引
Schema::table('unit_knowledge_points', function (Blueprint $table) {
    $table->index(['unit_id', 'type']);
    $table->index(['unit_id', 'sort_order']);
});
```

### 2. 前端性能优化

```typescript
// 使用React.memo优化知识点列表渲染
const KnowledgePointItem = React.memo(({ point, onEdit, onDelete }) => {
    return <div className="knowledge-point-item">{/* 组件内容 */}</div>;
});

// 使用useMemo缓存计算结果
const filteredPoints = useMemo(() => {
    return knowledgePoints.filter(
        (point) => point.type === selectedType || selectedType === "all"
    );
}, [knowledgePoints, selectedType]);
```

## 🔐 安全考虑

### 1. 权限控制

```php
// 确保用户只能访问自己机构的数据
public function index(Request $request): JsonResponse
{
    $user = Auth::user();

    $query = UnitKnowledgePoint::with(['unit.course'])
        ->whereHas('unit.course', function ($q) use ($user) {
            $q->where('institution_id', $user->institution_id);
        });

    return response()->json($query->paginate());
}
```

### 2. 数据验证

```php
// 严格的输入验证
$validated = $request->validate([
    'unit_id' => 'required|exists:course_units,id',
    'type' => 'required|in:vocabulary,sentence_pattern,grammar',
    'content' => 'required|string|max:255|regex:/^[\w\s\-\.\,\!\?]+$/u',
    'explanation' => 'nullable|string|max:1000',
    'example_sentences' => 'nullable|array|max:5',
    'example_sentences.*' => 'string|max:500',
]);
```

## 📈 监控和日志

### 1. 关键操作日志

```php
// 记录知识点操作日志
Log::info('Knowledge point created', [
    'user_id' => Auth::id(),
    'unit_id' => $validated['unit_id'],
    'type' => $validated['type'],
    'content' => $validated['content'],
    'ip' => $request->ip(),
]);
```

### 2. 性能监控

```php
// 监控查询性能
DB::listen(function ($query) {
    if ($query->time > 1000) { // 超过1秒的查询
        Log::warning('Slow query detected', [
            'sql' => $query->sql,
            'time' => $query->time,
            'bindings' => $query->bindings,
        ]);
    }
});
```

---

**文档版本**: V1.0
**最后更新**: 2025 年 9 月 2 日
**维护者**: 开发团队

## 📞 联系信息

如有问题或建议，请联系开发团队：

-   技术问题：通过 GitHub Issues 提交
-   功能建议：产品需求文档
-   紧急问题：开发团队群组
