# 学员管理前端开发文档

## 概述

学员管理前端提供完整的学员信息管理界面，包括学员列表、新增编辑、筛选搜索、统计展示等功能。

## 页面结构

### 主页面 (`/academic/students`)

```
学员管理
├─ 页面标题和描述
├─ 统计卡片区域 (4个统计卡片)
├─ 筛选和搜索区域
└─ 学员列表表格
   ├─ 表格标题和新增按钮
   ├─ 数据表格
   └─ 分页组件
```

## 组件文件

### 1. 主页面组件
**文件：** `src/pages/academic/Students.tsx`

**功能：**
- 学员列表展示（表格形式）
- 多维度筛选（类型、状态、意向等级）
- 实时搜索（姓名、电话、家长信息）
- 统计信息展示
- 分页功能
- 学员操作（新增、编辑、删除）

**状态管理：**
```typescript
const [search, setSearch] = useState('');
const [studentType, setStudentType] = useState<string>('all');
const [followUpStatus, setFollowUpStatus] = useState<string>('all');
const [intentionLevel, setIntentionLevel] = useState<string>('all');
const [currentPage, setCurrentPage] = useState(1);
const [editorOpen, setEditorOpen] = useState(false);
const [editingStudent, setEditingStudent] = useState<Student | null>(null);
```

### 2. 学员编辑器组件
**文件：** `src/components/academic/StudentEditor.tsx`

**功能：**
- 学员信息表单（新增/编辑）
- 表单验证
- 支持同时创建家长账号
- 响应式布局

**表单字段：**
- 学员基本信息：姓名、电话、性别、出生日期
- 家长信息：姓名、电话、关系
- 学员状态：类型、跟进状态、意向等级
- 其他信息：来源渠道、备注

## 界面设计

### 1. 统计卡片
```
┌─────────────┬─────────────┬─────────────┬─────────────┐
│ 总学员数    │ 正式学员    │ 试听学员    │ 潜在学员    │
│ 50         │ 20         │ 10         │ 15         │
└─────────────┴─────────────┴─────────────┴─────────────┘
```

### 2. 筛选区域
```
┌─────────────────────────────────────────────────────────────┐
│ [搜索框] [学员类型▼] [跟进状态▼] [意向等级▼]                │
└─────────────────────────────────────────────────────────────┘
```

### 3. 数据表格
```
┌─────────────────────────────────────────────────────────────┐
│ 学员列表                                    [+ 新增学员]    │
├─────────────────────────────────────────────────────────────┤
│ 姓名 │ 联系方式 │ 家长信息 │ 类型 │ 状态 │ 意向 │ 来源 │ 操作 │
├─────────────────────────────────────────────────────────────┤
│ 张小明│ 138xxx  │ 张女士   │ 正式 │ 有意向│ 高   │ 推荐 │ ⋮   │
│ 李小红│ 139xxx  │ 李先生   │ 试听 │ 联系中│ 中   │ 广告 │ ⋮   │
└─────────────────────────────────────────────────────────────┘
[上一页] [1] [2] [3] [4] [5] [下一页]
共 50 条记录，第 1 / 4 页
```

## 技术实现

### 1. 数据获取
使用 React Query 进行数据管理：
```typescript
const { data: studentsData, isLoading } = useQuery({
  queryKey: ['students', search, studentType, followUpStatus, intentionLevel, currentPage],
  queryFn: async () => {
    const params = new URLSearchParams({
      page: currentPage.toString(),
      per_page: '15',
    });
    // 添加筛选参数
    if (search) params.append('search', search);
    if (studentType && studentType !== 'all') params.append('student_type', studentType);
    // ...
    const response = await api.get(`/admin/students?${params}`);
    return response.data;
  },
});
```

### 2. 分页处理
```typescript
const pagination = studentsData?.data || {
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
};

const handlePageChange = (page: number) => {
  setCurrentPage(page);
};
```

### 3. 筛选重置
当筛选条件改变时，自动重置到第1页：
```typescript
const handleSearchChange = (value: string) => {
  setSearch(value);
  setCurrentPage(1);
};
```

### 4. 状态标签
使用不同颜色的Badge组件显示状态：
```typescript
const getStudentTypeBadge = (type: string) => {
  const variants = {
    potential: 'outline',
    trial: 'secondary',
    enrolled: 'default',
    graduated: 'secondary',
    suspended: 'destructive',
  };
  return variants[type] || 'outline';
};
```

## 用户体验优化

### 1. 加载状态
- 数据加载时显示"加载中..."
- 空状态友好提示

### 2. 表单验证
- 必填字段验证
- 手机号格式验证
- 实时错误提示

### 3. 操作反馈
- 成功/失败Toast通知
- 删除确认对话框
- 按钮禁用状态

### 4. 响应式设计
- 表格在小屏幕上可横向滚动
- 筛选器在移动端垂直排列
- 统计卡片自适应布局

## 样式设计

### 1. 布局样式
```css
/* 主容器 */
.space-y-6 /* 垂直间距 */

/* 表格容器 */
.min-h-[600px] .flex .flex-col /* 最小高度和flex布局 */

/* 分页固定在底部 */
.flex-1 /* 表格区域可增长 */
```

### 2. 滚动处理
- Layout组件：`overflow-y-auto` 支持页面滚动
- 表格容器：flex布局确保分页组件可见

## 数据流

### 1. 查询流程
```
用户操作 → 更新状态 → 触发查询 → API请求 → 更新数据 → 重新渲染
```

### 2. 编辑流程
```
点击编辑 → 打开编辑器 → 填写表单 → 提交数据 → API请求 → 更新列表 → 关闭编辑器
```

### 3. 删除流程
```
点击删除 → 确认对话框 → 确认删除 → API请求 → 更新列表 → 显示通知
```

## 已知问题和解决方案

### 1. Select组件空值问题
**问题：** Radix UI Select不允许空字符串作为value
**解决：** 使用"all"、"none"等有意义的值替代空字符串

### 2. 分页组件不显示
**问题：** 数据不足时分页组件不显示
**解决：** 当数据不足一页时，分页组件确实不应该显示

### 3. 页面滚动问题
**问题：** Layout使用overflow-hidden导致无法滚动
**解决：** 改为overflow-y-auto支持垂直滚动

## 后续优化方向

1. **性能优化**：虚拟滚动处理大量数据
2. **功能增强**：批量操作、导入导出
3. **用户体验**：拖拽排序、快捷键支持
4. **数据可视化**：图表展示统计信息
