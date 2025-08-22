# 功能设计文档：班级点名与课时扣减

**版本**: 1.0
**日期**: 2025-08-22

## 1. 概述

本功能旨在为班级管理系统提供核心的点名（考勤）和课时扣减能力，形成“排课-上课-点名-课消”的业务闭环，是确认机构实际收入的关键步骤。

**核心业务规则**:

-   **到课状态**: 分为 `到课`, `迟到`, `请假`, `未到` 四种。
-   **默认扣费**: 只有 `到课` 状态默认扣除 **1** 课时，其余三种状态 (`迟到`, `请假`, `未到`) 默认 **不扣课时** (0)。
-   **教师权限**: 教师拥有最终决定权，可以手动修改任意学员在任意状态下的扣除课时数（可改为 1 或 0）。

## 2. 业务流程

1.  **入口**：教师在“班级详情”页的“课程表”中，找到对应的待上课或已结束的课程安排。
2.  **操作**：点击“点名”按钮，进入点名界面。
3.  **点名**：教师在点名界面填写课程信息，并为班级内的每一位学员选择到课状态、确认扣除课时数。
4.  **提交**：教师确认信息无误后，提交点名记录。
5.  **后台处理**：
    -   系统记录或更新每个学员本次课程的考勤状态。
    -   系统根据考勤记录，精确扣减学员报名订单中的剩余课时。
6.  **结果**：课时扣减完成，课消（收入）得到确认。教师可以随时返回查看或修改点名记录。

## 3. 数据库设计变更

为了支持灵活的课时扣减，需要对现有的 `attendance_records` 表进行扩展。

**表名**: `attendance_records`

**操作**: 新增一个字段

| 字段名             | 类型            | 默认值 | 注释                       |
| :----------------- | :-------------- | :----- | :------------------------- |
| `deducted_lessons` | `decimal(8, 2)` | `1.00` | 本次考勤实际扣除的课时数。 |

**理由**: 该字段允许为每个学生的单次出勤记录精确的课时消耗，支持半节课等特殊业务场景，并将考勤状态与课时扣减解耦，增强了系统的灵活性。

## 4. API 接口设计

### 4.1. 获取指定课程的点名详情

此接口用于获取某次课程的学员列表及他们已有的点名记录（如果有的话）。

-   **Endpoint**: `GET /api/admin/class-schedules/{schedule_id}/attendance`
-   **认证**: 需要
-   **响应 (Success 200)**:

```json
{
    "code": 200,
    "message": "获取成功",
    "data": {
        "schedule_info": {
            "id": 101,
            "class_id": 12,
            "class_name": "周六上午英语一班",
            "lesson_date": "2025-08-23",
            "start_time": "10:00",
            "end_time": "11:30",
            "duration": 1.5, // 默认授课课时
            "teacher_name": "王老师",
            "subject": "自然拼读 L1"
        },
        "lesson_content": "学习 Chapter 5...", // 上次填写的上课内容
        "students": [
            {
                "student_id": 1,
                "student_name": "张小明",
                "enrollment_id": 201, // 关联的报名ID
                "course_name": "自然拼读 L1",
                "remaining_lessons": 30.5,
                "attendance_status": "present", // 上次记录的状态
                "deducted_lessons": 1.0, // 上次记录的扣除课时
                "remarks": "表现很好"
            },
            {
                "student_id": 2,
                "student_name": "李华",
                "enrollment_id": 202,
                "course_name": "自然拼读 L1",
                "remaining_lessons": 25.0,
                "attendance_status": null, // 还未点名
                "deducted_lessons": 1.5, // 默认为课程时长
                "remarks": ""
            }
        ]
    }
}
```

### 4.2. 批量提交点名记录

此接口用于创建或更新指定课程的所有学员的点名记录。

-   **Endpoint**: `POST /api/admin/class-schedules/{schedule_id}/attendance`
-   **认证**: 需要
-   **请求体**:

```json
{
    "lesson_content": "学习 Chapter 5, 完成了随堂练习。",
    "students": [
        {
            "student_id": 1,
            "enrollment_id": 201,
            "attendance_status": "present", // 'present', 'late', 'absent', 'leave'
            "deducted_lessons": 1.0,
            "remarks": "表现很好，积极回答问题。"
        },
        {
            "student_id": 2,
            "enrollment_id": 202,
            "attendance_status": "personal_leave",
            "deducted_lessons": 0,
            "remarks": "事假，已提前沟通。"
        }
    ]
}
```

-   **响应 (Success 200)**:

```json
{
    "code": 200,
    "message": "点名成功"
}
```

## 5. 前端界面设计 (概要)

点名功能将作为一个弹窗 (Dialog) 在班级详情页的课程表上触发。

### 5.1. 界面构成

1.  **课程基本信息区**:

    -   显示上课日期、时间、班级、老师等信息（不可编辑）。
    -   **授课课时** (number input): 默认为课程安排的时长，可修改，会影响所有“到课”学员的默认扣除课时。
    -   **上课内容** (textarea): 教师填写本次课的教学内容。

2.  **学员列表区 (表格)**:
    -   **学员姓名** (只读)。
    -   **消耗课程** (只读): 学员当前班级关联的课程报名。
    -   **剩余课时** (只读): 实时显示学员该课程的剩余课时。
    -   **到课状态** (下拉框): 选项包括 `到课`, `迟到`, `早退`, `缺勤`, `请假`。
    -   **扣除课时** (number input): 根据“到课状态”和“授课课时”自动填充，但允许教师手动修改。
    -   **备注** (text input): 教师为单个学员填写备注。

### 5.2. 交互逻辑

-   打开点名弹窗时，调用 `GET` 接口加载学员列表和历史点名数据（若有）。
-   修改“授课课时”或学员的“到课状态”时，对应学员的“扣除课时”输入框的值会联动更新。
-   点击“确认点名”时，收集所有学员的点名数据，调用 `POST` 接口提交。
-   提交成功后，关闭弹窗并刷新课程表和学员信息。

## 6. 后端核心逻辑

1.  **接收请求**: `POST /api/admin/class-schedules/{schedule_id}/attendance` 接收到请求数据。
2.  **数据校验**: 验证 `student_id`, `enrollment_id` 的有效性，以及 `deducted_lessons` 是否为非负数。
3.  **事务处理**: 启动数据库事务。
4.  **更新考勤记录**: 遍历 `students` 数组，使用 `updateOrCreate` 方法，根据 `schedule_id` 和 `student_id` 更新或创建 `attendance_records` 表的记录。
5.  **更新上课内容**: 将请求中的 `lesson_content` 保存到 `class_schedules` 表的对应字段中。
6.  **扣减课时**: (核心) 遍历 `students` 数组，对每一个需要扣课时（`deducted_lessons > 0`）的记录，找到其对应的 `student_enrollments` 记录，更新 `used_lessons` 和 `remaining_lessons` 字段。**注意：** 此处需要处理“修改点名”的场景，即本次扣除的课时应为 `新值 - 旧值` 的差额，防止重复扣减。
7.  **提交事务**: 所有操作成功后，提交事务。
8.  **返回响应**: 返回成功消息。
