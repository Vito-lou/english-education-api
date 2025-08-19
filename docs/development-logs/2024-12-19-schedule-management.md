# 排课管理功能开发记录

> **开发日期**: 2024年12月19日  
> **开发者**: AI Assistant  
> **功能模块**: 排课管理系统  
> **涉及文件**: 30+ 个文件

## 📋 功能概述

本次开发完成了完整的排课管理功能，包括：
- ✅ 时间段管理（独立页面）
- ✅ 班级排课管理（集成在班级详情页）
- ✅ 一键批量排课功能
- ✅ 排课冲突检测
- ✅ 权限控制和数据隔离

## 🗄️ 数据库变更

### 1. 新增数据表

#### `time_slots` - 时间段配置表
```sql
CREATE TABLE time_slots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    institution_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL COMMENT '时间段名称',
    start_time TIME NOT NULL COMMENT '开始时间',
    end_time TIME NOT NULL COMMENT '结束时间',
    duration_minutes INT NOT NULL COMMENT '时长(分钟)',
    is_active BOOLEAN DEFAULT TRUE COMMENT '是否启用',
    sort_order INT DEFAULT 0 COMMENT '排序',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_institution_active (institution_id, is_active),
    INDEX idx_sort_order (sort_order),
    FOREIGN KEY (institution_id) REFERENCES institutions(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### `class_schedules` - 课程安排表
```sql
CREATE TABLE class_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    time_slot_id BIGINT UNSIGNED NOT NULL,
    schedule_date DATE NOT NULL COMMENT '上课日期',
    lesson_content VARCHAR(100) NULL COMMENT '上课内容',
    classroom VARCHAR(50) NULL COMMENT '教室',
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    UNIQUE KEY uk_class_date_time (class_id, schedule_date, time_slot_id),
    INDEX idx_teacher_date_time (teacher_id, schedule_date, time_slot_id),
    INDEX idx_schedule_date (schedule_date),
    INDEX idx_status (status),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id),
    FOREIGN KEY (time_slot_id) REFERENCES time_slots(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### `attendance_records` - 考勤记录表（预留）
```sql
CREATE TABLE attendance_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_schedule_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    status ENUM('present', 'absent', 'late', 'leave') NOT NULL,
    check_in_time TIMESTAMP NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY uk_schedule_student (class_schedule_id, student_id),
    INDEX idx_student_date (student_id, created_at),
    FOREIGN KEY (class_schedule_id) REFERENCES class_schedules(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

#### `actual_lesson_records` - 实际上课记录表（预留）
```sql
CREATE TABLE actual_lesson_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_schedule_id BIGINT UNSIGNED NOT NULL,
    actual_start_time TIMESTAMP NULL,
    actual_end_time TIMESTAMP NULL,
    actual_content TEXT NULL,
    teaching_notes TEXT NULL,
    homework_assigned TEXT NULL,
    next_lesson_plan TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    UNIQUE KEY uk_schedule_record (class_schedule_id),
    FOREIGN KEY (class_schedule_id) REFERENCES class_schedules(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### 2. 数据库迁移文件
- `2024_12_19_000001_create_time_slots_table.php`
- `2024_12_19_000002_create_class_schedules_table.php`
- `2024_12_19_000003_create_attendance_records_table.php`
- `2024_12_19_000004_create_actual_lesson_records_table.php`

### 3. 种子数据
- `TimeSlotSeeder.php` - 创建6个预设时间段

## 🏗️ 后端开发

### 1. 模型文件
- `app/Models/TimeSlot.php` - 时间段模型
- `app/Models/ClassSchedule.php` - 课程安排模型
- `app/Models/AttendanceRecord.php` - 考勤记录模型（预留）
- `app/Models/ActualLessonRecord.php` - 实际上课记录模型（预留）

### 2. 控制器文件
- `app/Http/Controllers/Api/Admin/TimeSlotController.php` - 时间段管理API
- `app/Http/Controllers/Api/Admin/ClassScheduleController.php` - 排课管理API

### 3. API路由
在 `routes/api.php` 中添加：
```php
// 时间段管理
Route::apiResource('time-slots', TimeSlotController::class);

// 排课管理
Route::apiResource('class-schedules', ClassScheduleController::class);
Route::post('schedules/batch-create', [ClassScheduleController::class, 'batchCreate']);
```

### 4. 核心功能实现

#### 时间段管理
- ✅ CRUD操作（创建、读取、更新、删除）
- ✅ 机构数据隔离
- ✅ 排序功能
- ✅ 启用/禁用状态

#### 排课管理
- ✅ 单个排课创建
- ✅ 批量排课创建
- ✅ 冲突检测（班级时间冲突、教师时间冲突）
- ✅ 权限控制
- ✅ 数据验证

## 🎨 前端开发

### 1. 页面组件
- `src/pages/academic/TimeSlots.tsx` - 时间段管理页面
- `src/components/academic/ClassScheduleManagement.tsx` - 排课管理组件

### 2. 路由配置
更新 `src/App.tsx`：
```typescript
// 时间段管理路由
<Route path="/academic/schedules" element={<TimeSlots />} />
```

### 3. 集成到班级详情页
更新 `src/pages/academic/ClassDetail.tsx`：
- 导入排课管理组件
- 替换"排课信息"Tab的占位内容

### 4. 核心功能实现

#### 时间段管理页面
- ✅ 时间段列表展示
- ✅ 新增/编辑时间段
- ✅ 删除时间段
- ✅ 状态切换
- ✅ 排序管理

#### 排课管理组件
- ✅ 班级排课列表
- ✅ 一键排课功能
- ✅ 日历多选日期
- ✅ 批量创建排课
- ✅ 删除排课
- ✅ 跳转时间段设置

## 🔧 技术规范修正

### 1. API调用规范统一
**问题**: 初始开发时使用了直接的 `fetch` 调用，没有遵循项目的API客户端规范

**修正**: 
- ❌ `fetch('/api/admin/users', { headers: {...} })`
- ✅ `api.get('/admin/users')`

**影响文件**:
- `src/components/academic/ClassScheduleManagement.tsx`
- `src/pages/academic/TimeSlots.tsx`

### 2. Toast通知系统统一
**问题**: 初始开发时使用了shadcn/ui的toast，没有使用项目自定义的toast系统

**修正**:
- ❌ `import { useToast } from '@/hooks/use-toast'`
- ✅ `import { useToast } from '@/components/ui/toast'`
- ❌ `toast({ variant: 'destructive' })`
- ✅ `addToast({ type: 'error' })`

**影响文件**:
- `src/components/academic/ClassScheduleManagement.tsx`
- `src/pages/academic/TimeSlots.tsx`

## 📚 文档更新

### 1. 前端开发规范
更新 `english-education-frontend/README.md`，添加：
- 🔌 API调用规范
- 🔔 Toast通知规范
- 🎨 组件开发规范
- 🔄 状态管理规范
- 🚨 常见问题解决

### 2. 后端开发规范
更新 `english-education-api/README.md`，添加：
- 🔌 API响应格式规范
- 📄 分页响应格式
- 🔐 认证规范
- 🏗️ Controller开发规范
- 🗄️ Model开发规范
- ⚠️ 常见错误避免

## 🎯 功能特性

### 1. 权限控制
- ✅ 基于机构的数据隔离（institution_id）
- ✅ 用户角色权限控制
- ✅ API级别的权限验证

### 2. 数据验证
- ✅ 表单数据验证
- ✅ 业务逻辑验证
- ✅ 冲突检测

### 3. 用户体验
- ✅ 加载状态显示
- ✅ 错误提示
- ✅ 成功反馈
- ✅ 友好的空状态

### 4. 扩展性设计
- ✅ 预留考勤记录表
- ✅ 预留实际上课记录表
- ✅ 支持未来功能扩展

## 🐛 问题解决记录

### 1. 401认证错误
**问题**: API调用返回401无权限错误
**原因**: 
- 使用了错误的token存储键名
- 没有使用统一的API客户端

**解决**: 
- 统一使用 `localStorage.getItem("auth_token")`
- 使用项目的api客户端自动处理认证

### 2. Toast不显示
**问题**: Toast通知不显示
**原因**: 使用了错误的toast系统

**解决**: 使用项目自定义的toast系统

### 3. 课程和教师选项为空
**问题**: 一键排课弹窗中课程和教师选择不到
**原因**: API路径错误

**解决**: 
- 课程API: `/api/admin/courses` → `/api/admin/courses-options`
- 教师API: 添加 `?role=teacher` 参数

## 📊 开发统计

- **总文件数**: 30+ 个
- **新增文件**: 15 个
- **修改文件**: 15+ 个
- **代码行数**: 2000+ 行
- **开发时间**: 1 天

## 🚀 后续计划

1. **考勤管理**: 基于排课记录实现点名功能
2. **上课记录**: 记录实际教学内容和反馈
3. **统计报表**: 排课统计和教学分析
4. **移动端适配**: 响应式设计优化
5. **通知提醒**: 上课提醒和变更通知

## 📝 经验总结

1. **遵循项目规范**: 开发前要仔细了解项目的技术规范
2. **统一API调用**: 使用项目配置的API客户端
3. **统一UI组件**: 使用项目自定义的UI组件系统
4. **权限控制**: 所有API都要进行机构级别的权限检查
5. **文档维护**: 及时更新开发规范文档，避免重复错误
