# 学员管理系统 API 文档

## 概述

学员管理系统提供完整的学员生命周期管理功能，包括学员信息管理、跟进状态跟踪、家长账号管理等。

## 数据库设计

### 学员表 (students)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| name | varchar(255) | 学员姓名 |
| phone | varchar(20) | 学员手机号 |
| gender | enum | 性别 (male/female) |
| birth_date | date | 出生日期 |
| parent_name | varchar(255) | 家长姓名 |
| parent_phone | varchar(20) | 家长手机号 |
| parent_relationship | enum | 家长关系 (father/mother/guardian/other) |
| student_type | enum | 学员类型 (potential/trial/enrolled/graduated/suspended) |
| follow_up_status | enum | 跟进状态 (new/contacted/interested/not_interested/follow_up) |
| intention_level | enum | 意向等级 (high/medium/low) |
| user_id | bigint | 关联用户ID |
| institution_id | bigint | 机构ID |
| source | varchar(255) | 来源渠道 |
| remarks | text | 备注信息 |
| status | enum | 状态 (active/inactive) |

### 用户学员关联表 (user_students)

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 主键 |
| user_id | bigint | 用户ID |
| student_id | bigint | 学员ID |
| relationship | enum | 关系类型 (parent/father/mother/guardian/other) |

## API 接口

### 1. 获取学员列表

**接口地址：** `GET /api/admin/students`

**请求参数：**
```json
{
  "page": 1,
  "per_page": 15,
  "search": "搜索关键词",
  "student_type": "potential|trial|enrolled|graduated|suspended",
  "follow_up_status": "new|contacted|interested|not_interested|follow_up",
  "intention_level": "high|medium|low",
  "sort_field": "created_at",
  "sort_direction": "desc"
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "张小明",
        "phone": "13800001001",
        "gender": "male",
        "birth_date": "2015-03-15",
        "parent_name": "张女士",
        "parent_phone": "13800001000",
        "parent_relationship": "mother",
        "student_type": "enrolled",
        "follow_up_status": "interested",
        "intention_level": "high",
        "source": "朋友推荐",
        "remarks": "学习积极性很高",
        "age": 9,
        "student_type_name": "正式学员",
        "follow_up_status_name": "有意向",
        "intention_level_name": "高意向",
        "user": {
          "id": 6,
          "name": "张女士",
          "phone": "13800001000"
        },
        "created_at": "2025-08-08T09:03:34.000000Z"
      }
    ],
    "last_page": 1,
    "per_page": 15,
    "total": 3
  }
}
```

### 2. 创建学员

**接口地址：** `POST /api/admin/students`

**请求参数：**
```json
{
  "name": "学员姓名",
  "phone": "13800000000",
  "gender": "male",
  "birth_date": "2015-01-01",
  "parent_name": "家长姓名",
  "parent_phone": "13800000001",
  "parent_relationship": "mother",
  "student_type": "potential",
  "follow_up_status": "new",
  "intention_level": "medium",
  "source": "来源渠道",
  "remarks": "备注信息",
  "create_parent_account": true
}
```

**响应示例：**
```json
{
  "code": 200,
  "message": "学员创建成功",
  "data": {
    "id": 4,
    "name": "学员姓名",
    // ... 其他字段
  }
}
```

### 3. 获取学员详情

**接口地址：** `GET /api/admin/students/{id}`

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "name": "张小明",
    // ... 完整学员信息
  }
}
```

### 4. 更新学员信息

**接口地址：** `PUT /api/admin/students/{id}`

**请求参数：** 同创建学员（除了create_parent_account字段）

### 5. 删除学员

**接口地址：** `DELETE /api/admin/students/{id}`

**响应示例：**
```json
{
  "code": 200,
  "message": "学员删除成功"
}
```

### 6. 获取学员统计信息

**接口地址：** `GET /api/admin/students/statistics`

**响应示例：**
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "total": 50,
    "by_type": {
      "potential": 15,
      "trial": 10,
      "enrolled": 20,
      "graduated": 3,
      "suspended": 2
    },
    "by_follow_up": {
      "new": 8,
      "contacted": 12,
      "interested": 15,
      "not_interested": 5,
      "follow_up": 10
    },
    "by_intention": {
      "high": 20,
      "medium": 25,
      "low": 5
    }
  }
}
```

## 业务特性

### 1. 学员类型管理
- **潜在学员**：初步接触的潜在客户
- **试听学员**：参与试听课程的学员
- **正式学员**：已报名正式课程的学员
- **已毕业**：完成课程学习的学员
- **暂停学习**：暂时停止学习的学员

### 2. 跟进状态跟踪
- **新学员**：刚添加的学员
- **已联系**：已经联系过的学员
- **有意向**：表现出学习意向的学员
- **无意向**：暂无学习意向的学员
- **跟进中**：正在持续跟进的学员

### 3. 意向等级评估
- **高意向**：学习意愿强烈
- **中意向**：有一定学习意愿
- **低意向**：学习意愿较弱

### 4. 家长账号管理
- 支持为学员创建家长账号
- 支持一个学员关联多个家长
- 支持一个家长管理多个孩子
- 默认密码：123456

## 权限控制

- 所有接口需要认证
- 数据按机构隔离
- 只能操作同机构的学员数据

## 数据验证

- 学员姓名：必填
- 家长姓名：必填
- 家长手机号：必填，格式验证
- 学员手机号：可选，格式验证
- 其他字段：按枚举值验证
