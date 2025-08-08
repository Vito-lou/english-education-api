# 课程管理前端组件指南

## 组件架构

### 页面组件
```
src/pages/academic/
├── Courses.tsx              # 课程列表页面
└── CourseDetail.tsx         # 课程详情页面
```

### 业务组件
```
src/components/academic/
├── CourseEditor.tsx         # 课程编辑器
├── CourseContentManager.tsx # 课程内容管理器
├── LevelEditor.tsx          # 级别编辑器
├── UnitEditor.tsx           # 单元编辑器
└── LessonEditor.tsx         # 课时编辑器
```

## 1. 课程列表页面 (Courses.tsx)

### 功能特性
- 课程卡片式展示
- 按科目筛选
- 课程的增删改查操作
- 跳转到课程详情

### 关键状态
```typescript
const [editorOpen, setEditorOpen] = useState(false);
const [editingCourse, setEditingCourse] = useState<Course | null>(null);
const [selectedSubject, setSelectedSubject] = useState<number | null>(null);
```

### 主要操作
```typescript
// 新建课程
const handleCreateCourse = () => {
  setEditingCourse(null);
  setEditorOpen(true);
};

// 编辑课程
const handleEditCourse = (course: Course) => {
  setEditingCourse(course);
  setEditorOpen(true);
};

// 查看详情
const handleViewDetail = (courseId: number) => {
  navigate(`/academic/courses/${courseId}`);
};
```

## 2. 课程详情页面 (CourseDetail.tsx)

### Tab结构
- **级别管理Tab**: 管理课程级别的基本信息
- **课程内容Tab**: 一体化的内容管理界面

### 关键组件集成
```typescript
// 级别管理
<LevelEditor
  open={levelEditorOpen}
  onClose={handleCloseLevelEditor}
  onSave={handleSaveLevel}
  level={editingLevel}
  courseId={parseInt(id!)}
  loading={saveLevelMutation.isPending}
/>

// 课程内容管理
<CourseContentManager
  courseId={parseInt(id!)}
  levels={course?.levels || []}
/>
```

## 3. 课程内容管理器 (CourseContentManager.tsx)

### 设计理念
- **级别选择器**: 顶部按钮切换不同级别
- **专注视图**: 一次只显示一个级别的内容
- **一体化管理**: 单元和课时在同一界面

### 核心状态
```typescript
// 当前选中的级别
const [selectedLevelId, setSelectedLevelId] = useState<number | null>(
  levels.length > 0 ? levels[0].id : null
);

// 展开的单元
const [expandedUnits, setExpandedUnits] = useState<Set<number>>(new Set());

// 编辑器状态
const [unitEditorOpen, setUnitEditorOpen] = useState(false);
const [lessonEditorOpen, setLessonEditorOpen] = useState(false);
```

### 数据查询
```typescript
// 获取当前级别的单元
const { data: unitsData } = useQuery({
  queryKey: ['course-units', courseId, selectedLevelId],
  queryFn: async () => {
    const params = new URLSearchParams({
      course_id: courseId.toString(),
    });
    if (selectedLevelId) {
      params.append('level_id', selectedLevelId.toString());
    }
    const response = await api.get(`/admin/course-units?${params}`);
    return response.data;
  },
  enabled: !!courseId,
});
```

### 级别切换逻辑
```typescript
const handleLevelChange = (levelId: number) => {
  setSelectedLevelId(levelId);
  setExpandedUnits(new Set()); // 清空展开状态
};
```

## 4. 编辑器组件

### 4.1 通用编辑器模式
所有编辑器组件都遵循相同的模式：

```typescript
interface EditorProps {
  open: boolean;                    // 是否打开
  onClose: () => void;             // 关闭回调
  onSave: (data: Partial<T>) => void; // 保存回调
  item?: T | null;                 // 编辑的项目（null表示新建）
  loading?: boolean;               // 加载状态
  // 其他特定属性...
}
```

### 4.2 表单状态管理
```typescript
const [formData, setFormData] = useState<Partial<T>>({
  // 默认值
});

const [errors, setErrors] = useState<Record<string, string>>({});

// 重置表单
const resetForm = () => {
  if (item) {
    setFormData({ ...item }); // 编辑模式
  } else {
    setFormData({ /* 默认值 */ }); // 新建模式
  }
  setErrors({});
};
```

### 4.3 表单验证
```typescript
const validateForm = (): boolean => {
  const newErrors: Record<string, string> = {};

  if (!formData.name?.trim()) {
    newErrors.name = '名称不能为空';
  }

  setErrors(newErrors);
  return Object.keys(newErrors).length === 0;
};
```

## 5. 状态管理模式

### 5.1 React Query使用
```typescript
// 数据查询
const { data, isLoading } = useQuery({
  queryKey: ['resource', id],
  queryFn: () => api.get(`/admin/resource/${id}`),
});

// 数据变更
const mutation = useMutation({
  mutationFn: (data) => api.post('/admin/resource', data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ['resource'] });
    toast({ title: '操作成功' });
  },
  onError: (error) => {
    toast({ title: '操作失败', variant: 'destructive' });
  },
});
```

### 5.2 本地状态管理
```typescript
// 编辑器状态
const [editorOpen, setEditorOpen] = useState(false);
const [editingItem, setEditingItem] = useState<T | null>(null);

// 确认对话框状态
const [showConfirmDialog, setShowConfirmDialog] = useState(false);
const [itemToDelete, setItemToDelete] = useState<T | null>(null);
```

## 6. 用户交互模式

### 6.1 CRUD操作流程
```typescript
// 新建
const handleCreate = () => {
  setEditingItem(null);
  setEditorOpen(true);
};

// 编辑
const handleEdit = (item: T) => {
  setEditingItem(item);
  setEditorOpen(true);
};

// 删除
const handleDelete = (item: T) => {
  setItemToDelete(item);
  setShowConfirmDialog(true);
};

// 保存
const handleSave = (data: Partial<T>) => {
  mutation.mutate(data);
};
```

### 6.2 展开/折叠状态
```typescript
const [expandedItems, setExpandedItems] = useState<Set<number>>(new Set());

const toggleExpanded = (itemId: number) => {
  setExpandedItems(prev => {
    const newSet = new Set(prev);
    if (newSet.has(itemId)) {
      newSet.delete(itemId);
    } else {
      newSet.add(itemId);
    }
    return newSet;
  });
};
```

## 7. 样式和布局

### 7.1 弹窗样式规范
```typescript
// 标准弹窗样式
<DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
  <DialogHeader>
    <DialogTitle>{item ? '编辑' : '新建'}</DialogTitle>
  </DialogHeader>
  
  <div className="space-y-4">
    {/* 表单内容 */}
  </div>
  
  <DialogFooter>
    <Button variant="outline" onClick={onClose}>取消</Button>
    <Button onClick={handleSave}>保存</Button>
  </DialogFooter>
</DialogContent>
```

### 7.2 卡片布局
```typescript
// 可折叠卡片
<Card className="overflow-hidden">
  {/* 卡片头部 */}
  <div className="p-4 border-b bg-gray-50">
    <div className="flex items-center justify-between">
      <div className="flex items-center gap-3">
        <Button onClick={() => toggleExpanded(item.id)}>
          {isExpanded ? <ChevronDown /> : <ChevronRight />}
        </Button>
        <h4>{item.name}</h4>
      </div>
      <div className="flex gap-2">
        {/* 操作按钮 */}
      </div>
    </div>
  </div>
  
  {/* 可折叠内容 */}
  {isExpanded && (
    <div className="p-4">
      {/* 子项目列表 */}
    </div>
  )}
</Card>
```

## 8. 错误处理

### 8.1 表单验证错误
```typescript
// 显示字段错误
{errors.name && (
  <p className="text-sm text-red-500 mt-1">{errors.name}</p>
)}

// 输入框错误样式
<Input
  className={errors.name ? 'border-red-500' : ''}
  {...props}
/>
```

### 8.2 API错误处理
```typescript
const mutation = useMutation({
  mutationFn: apiCall,
  onError: (error: any) => {
    const message = error.response?.data?.message || '操作失败';
    toast({
      title: '错误',
      description: message,
      variant: 'destructive',
    });
  },
});
```

## 9. 性能优化

### 9.1 查询优化
```typescript
// 条件查询
const { data } = useQuery({
  queryKey: ['units', courseId, levelId],
  queryFn: () => fetchUnits(courseId, levelId),
  enabled: !!courseId && !!levelId, // 只在有必要参数时查询
});

// 查询失效
queryClient.invalidateQueries({ 
  queryKey: ['units', courseId] // 精确失效相关查询
});
```

### 9.2 组件优化
```typescript
// 使用 React.memo 优化重渲染
const UnitCard = React.memo(({ unit, onEdit, onDelete }) => {
  // 组件内容
});

// 使用 useCallback 优化回调函数
const handleEdit = useCallback((unit) => {
  setEditingUnit(unit);
  setEditorOpen(true);
}, []);
```

## 10. 开发建议

### 10.1 组件设计原则
1. **单一职责**: 每个组件只负责一个功能
2. **可复用性**: 编辑器组件设计为可复用
3. **状态提升**: 共享状态提升到合适的父组件
4. **错误边界**: 添加错误处理和边界情况

### 10.2 代码组织
1. **类型定义**: 统一的 TypeScript 接口定义
2. **常量提取**: 将魔法数字和字符串提取为常量
3. **工具函数**: 复用的逻辑提取为工具函数
4. **样式一致**: 使用统一的样式规范

### 10.3 测试建议
1. **单元测试**: 测试组件的核心逻辑
2. **集成测试**: 测试组件间的交互
3. **E2E测试**: 测试完整的用户流程
4. **错误场景**: 测试各种错误和边界情况
