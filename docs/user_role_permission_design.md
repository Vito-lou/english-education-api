# 用户角色权限系统设计文档

## 1. 设计概述

### 1.1 设计原则

-   **角色与组织架构分离**：角色专注于功能和权限，组织架构专注于层级管理
-   **角色机构化**：每个机构拥有独立的角色体系，支持个性化管理
-   **权限可配置**：功能权限和数据权限均支持灵活配置
-   **扩展性优先**：设计支持未来功能扩展，避免大规模重构

### 1.2 核心概念

-   **系统角色**：系统预定义的基础角色模板
-   **机构角色**：基于系统角色创建的机构专属角色
-   **功能权限**：控制用户可以访问的页面和操作
-   **数据权限**：控制用户可以查看的数据范围

## 2. 业务场景分析

### 2.1 角色分类示例

```
系统角色模板：
- 超级管理员：全功能权限 + 全数据权限
- 机构管理员：机构管理功能 + 本机构数据权限
- 校长：教学管理功能 + 本机构数据权限
- 老师：教学相关功能 + 分配学员数据权限
- 教务：学务管理功能 + 本部门数据权限
- 销售主管：销售功能 + 本部门及下级数据权限
- 家长：查看功能 + 仅个人孩子数据权限

机构自定义角色：
- 各机构可基于系统角色创建个性化角色
- 如：A机构的"班主任"角色，B机构的"课程顾问"角色
```

### 2.2 用户创建流程

```
机构内用户：
- 基础信息：姓名 + 手机号 + 密码
- 组织信息：所属机构 + 所属部门
- 权限信息：分配角色（从该机构的角色列表中选择）

外部用户（如家长）：
- 基础信息：姓名 + 手机号 + 密码
- 权限信息：分配系统角色（无需机构部门信息）
```

## 3. 数据库设计

### 3.1 核心表结构

#### 用户表 (users)

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '姓名',
    phone VARCHAR(20) UNIQUE NOT NULL COMMENT '手机号',
    email VARCHAR(100) UNIQUE COMMENT '邮箱',
    password VARCHAR(255) NOT NULL COMMENT '密码',
    avatar VARCHAR(500) COMMENT '头像',
    institution_id BIGINT COMMENT '所属机构ID（外部用户为null）',
    department_id BIGINT COMMENT '所属部门ID（外部用户为null）',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' COMMENT '状态',
    last_login_at TIMESTAMP COMMENT '最后登录时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (institution_id) REFERENCES institutions(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    INDEX idx_institution_id (institution_id),
    INDEX idx_department_id (department_id),
    INDEX idx_phone (phone)
);
```

#### 角色表 (roles)

```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '角色名称',
    code VARCHAR(50) NOT NULL COMMENT '角色代码',
    description TEXT COMMENT '角色描述',
    institution_id BIGINT COMMENT '所属机构ID（系统角色为null）',
    is_system BOOLEAN DEFAULT FALSE COMMENT '是否系统角色',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT '状态',
    sort_order INT DEFAULT 0 COMMENT '排序',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (institution_id) REFERENCES institutions(id),
    UNIQUE KEY uk_institution_code (institution_id, code),
    INDEX idx_institution_id (institution_id),
    INDEX idx_is_system (is_system)
);
```

#### 用户角色关联表 (user_roles)

```sql
CREATE TABLE user_roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL COMMENT '用户ID',
    role_id BIGINT NOT NULL COMMENT '角色ID',
    granted_by BIGINT COMMENT '分配人ID',
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '分配时间',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id),
    UNIQUE KEY uk_user_role (user_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_role_id (role_id)
);
```

### 3.2 权限系统表结构

#### 功能权限表 (permissions)

```sql
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '权限名称',
    code VARCHAR(100) UNIQUE NOT NULL COMMENT '权限代码',
    type ENUM('menu', 'button', 'api') NOT NULL COMMENT '权限类型',
    parent_id BIGINT COMMENT '父权限ID',
    resource VARCHAR(100) COMMENT '资源标识',
    action VARCHAR(50) COMMENT '操作标识',
    description TEXT COMMENT '权限描述',
    sort_order INT DEFAULT 0 COMMENT '排序',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT '状态',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (parent_id) REFERENCES permissions(id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_type (type),
    INDEX idx_code (code)
);
```

#### 数据权限表 (data_permissions)

```sql
CREATE TABLE data_permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '数据权限名称',
    code VARCHAR(100) UNIQUE NOT NULL COMMENT '数据权限代码',
    resource_type VARCHAR(50) NOT NULL COMMENT '资源类型（student, class, schedule等）',
    scope_type ENUM('all', 'partial') NOT NULL COMMENT '权限范围：全部/部分',
    description TEXT COMMENT '权限描述',
    sort_order INT DEFAULT 0 COMMENT '排序',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT '状态',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_resource_type (resource_type),
    INDEX idx_scope_type (scope_type)
);

-- 示例数据权限
INSERT INTO data_permissions (name, code, resource_type, scope_type, description, sort_order) VALUES
('学员数据-全部', 'student:all', 'student', 'all', '可以查看所有学员数据', 1),
('学员数据-部分', 'student:partial', 'student', 'partial', '只能查看分配给自己的学员', 2),
('班级数据-全部', 'class:all', 'class', 'all', '可以查看所有班级数据', 3),
('班级数据-部分', 'class:partial', 'class', 'partial', '只能查看自己负责的班级', 4),
('课表数据-全部', 'schedule:all', 'schedule', 'all', '可以查看所有课表', 5),
('课表数据-部分', 'schedule:partial', 'schedule', 'partial', '只能查看自己的课表', 6),
('上课记录-全部', 'lesson:all', 'lesson', 'all', '可以查看所有上课记录', 7),
('上课记录-部分', 'lesson:partial', 'lesson', 'partial', '只能查看相关的上课记录', 8),
('缺课补课-全部', 'makeup:all', 'makeup', 'all', '可以查看所有缺课补课记录', 9),
('缺课补课-部分', 'makeup:partial', 'makeup', 'partial', '只能查看负责学员的缺课补课', 10);
```

#### 角色功能权限关联表 (role_permissions)

```sql
CREATE TABLE role_permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    role_id BIGINT NOT NULL COMMENT '角色ID',
    permission_id BIGINT NOT NULL COMMENT '功能权限ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    UNIQUE KEY uk_role_permission (role_id, permission_id),
    INDEX idx_role_id (role_id),
    INDEX idx_permission_id (permission_id)
);
```

#### 角色数据权限关联表 (role_data_permissions)

```sql
CREATE TABLE role_data_permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    role_id BIGINT NOT NULL COMMENT '角色ID',
    data_permission_id BIGINT NOT NULL COMMENT '数据权限ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (data_permission_id) REFERENCES data_permissions(id) ON DELETE CASCADE,
    UNIQUE KEY uk_role_data_permission (role_id, data_permission_id),
    INDEX idx_role_id (role_id),
    INDEX idx_data_permission_id (data_permission_id)
);
```

## 4. 业务逻辑设计

### 4.1 角色管理逻辑

#### 系统角色

-   系统预定义，所有机构共享
-   不可删除，提供基础角色模板
-   `is_system = true, institution_id = null`

#### 机构角色

-   属于特定机构的自定义角色
-   可以自定义权限配置
-   `is_system = false, institution_id = 具体机构ID`

### 4.2 权限判断逻辑

#### 功能权限判断

```php
// 检查用户是否有某个功能权限
public function hasPermission($user, $permissionCode) {
    return $user->roles()
        ->whereHas('permissions', function($query) use ($permissionCode) {
            $query->where('code', $permissionCode)
                  ->where('status', 'active');
        })
        ->exists();
}
```

#### 数据权限判断

```php
// 根据数据权限过滤查询
public function applyDataPermission($query, $user, $resourceType) {
    // 检查是否有全部数据权限
    if ($user->hasDataPermission("{$resourceType}:all")) {
        return $query; // 不过滤，返回全部数据
    }

    // 检查是否有部分数据权限
    if ($user->hasDataPermission("{$resourceType}:partial")) {
        // 根据资源类型应用不同的过滤规则
        switch ($resourceType) {
            case 'student':
                return $query->where('assigned_teacher_id', $user->id);
            case 'class':
                return $query->where('teacher_id', $user->id);
            case 'schedule':
                return $query->where('teacher_id', $user->id);
            case 'lesson':
                return $query->whereHas('class', function($q) use ($user) {
                    $q->where('teacher_id', $user->id);
                });
            case 'makeup':
                return $query->whereHas('student', function($q) use ($user) {
                    $q->where('assigned_teacher_id', $user->id);
                });
            default:
                return $query->where('created_by', $user->id);
        }
    }

    // 没有任何数据权限，返回空结果
    return $query->whereRaw('1 = 0');
}
```

### 4.3 缓存策略

#### 用户权限缓存

```php
// 缓存用户的所有权限
Cache::remember("user_permissions_{$userId}", 3600, function() use ($userId) {
    $user = User::with(['roles.permissions', 'roles.dataPermissions'])->find($userId);
    return [
        'permissions' => $user->getAllPermissions(),
        'data_permissions' => $user->getAllDataPermissions()
    ];
});

// 权限变更时清除缓存
Cache::forget("user_permissions_{$userId}");
```

## 5. API 设计要点

### 5.1 角色管理 API

-   `GET /api/admin/institutions/{id}/roles` - 获取机构角色列表
-   `POST /api/admin/institutions/{id}/roles` - 创建机构角色
-   `PUT /api/admin/roles/{id}` - 更新角色
-   `DELETE /api/admin/roles/{id}` - 删除角色

### 5.2 权限配置 API

-   `GET /api/admin/permissions` - 获取功能权限树
-   `GET /api/admin/data-permissions` - 获取数据权限列表
-   `PUT /api/admin/roles/{id}/permissions` - 配置角色权限

### 5.3 用户管理 API

-   `GET /api/admin/institutions/{id}/users` - 获取机构用户列表
-   `POST /api/admin/institutions/{id}/users` - 创建机构用户
-   `PUT /api/admin/users/{id}/roles` - 分配用户角色

## 6. 前端交互设计

### 6.1 整体布局设计

#### 账户管理模块 (Tab 形式)

角色管理和用户管理统一放在"账户管理"菜单下，使用 Tab 标签页形式展示：

```
┌─────────────────────────────────────────────────────────────┐
│ 账户管理                                                    │
├─────────────────────────────────────────────────────────────┤
│ [用户管理] [角色管理]                                       │
├─────────────────────────────────────────────────────────────┤
│ 对应的管理内容区域                                          │
└─────────────────────────────────────────────────────────────┘
```

### 6.2 角色管理页面设计

#### 左右分栏布局 (替代弹窗设计)

采用左侧角色列表 + 右侧编辑区的布局，提供更好的用户体验：

```
┌─────────────────┬───────────────────────────────────────────┐
│ 角色列表 (30%)   │ 角色编辑区 (70%)                          │
├─────────────────┼───────────────────────────────────────────┤
│ [+ 新建角色]     │ 角色名称: [超级管理员            ]        │
│                 │                                           │
│ ▼ 自定义角色     │ 角色描述: [拥有系统所有权限        ]        │
│   ● 校长        │                                           │
│   ○ 教务主管    │ ┌─────────────────────────────────────┐   │
│   ○ 老师        │ │ 功能权限                            │   │
│   ○ 销售        │ │ 决定角色可以看到哪些页面或使用哪些操作 │   │
│                 │ │                                     │   │
│ ▼ 系统角色      │ │ ☑ 用户管理                         │   │
│   ○ 超级管理员  │ │   ☑ 查看  ☑ 新增  ☑ 编辑  ☑ 删除 │   │
│   ○ 机构管理员  │ │ ☑ 学员管理                         │   │
│                 │ │   ☑ 查看  ☑ 新增  ☑ 编辑  ☐ 删除 │   │
│                 │ │ ☐ 财务管理                         │   │
│                 │ │   ☐ 查看  ☐ 新增  ☐ 编辑  ☐ 删除 │   │
│                 │ └─────────────────────────────────────┘   │
│                 │                                           │
│                 │ ┌─────────────────────────────────────┐   │
│                 │ │ 数据权限                            │   │
│                 │ │ 决定员工可查看多少数据或内容        │   │
│                 │ │                                     │   │
│                 │ │ 学员数据: ● 全部  ○ 部分(仅分配的) │   │
│                 │ │ 班级数据: ● 全部  ○ 部分(仅负责的) │   │
│                 │ │ 课表数据: ○ 全部  ● 部分(仅自己的) │   │
│                 │ │ 上课记录: ● 全部  ○ 部分(仅相关的) │   │
│                 │ │ 缺课补课: ● 全部  ○ 部分(仅负责的) │   │
│                 │ └─────────────────────────────────────┘   │
│                 │                                           │
│                 │                    [保存更改] [重置]      │
└─────────────────┴───────────────────────────────────────────┘
```

#### 设计要点

**左侧角色列表**：

-   使用 Collapse 组件分组显示
-   自定义角色和系统角色分别分组
-   支持角色搜索和筛选
-   选中角色高亮显示
-   默认选中第一个角色

**右侧编辑区**：

-   角色基础信息编辑
-   功能权限树形结构配置
-   数据权限分类配置
-   实时保存或手动保存

**权限配置说明**：

-   **功能权限**：决定角色可以看到哪些页面或使用哪些操作
-   **数据权限**：决定员工可查看多少数据或内容

#### 交互逻辑

1. **默认状态**：进入页面时自动选中第一个角色
2. **角色切换**：点击左侧角色，右侧立即显示该角色的权限配置
3. **权限编辑**：
    - 功能权限支持树形结构的批量勾选
    - 数据权限每个资源类型只能选择"全部"或"部分"
4. **系统角色保护**：系统角色只读，不允许编辑
5. **保存机制**：权限变更后显示保存按钮，或支持自动保存

#### 响应式设计

**桌面端 (>1200px)**：

-   左侧 30%，右侧 70%

**平板端 (768px-1200px)**：

-   左侧 35%，右侧 65%

**移动端 (<768px)**：

-   改为上下布局或抽屉式侧边栏

### 6.3 用户管理页面

#### 用户管理功能增强

**用户列表页面**：

-   显示用户基础信息（姓名、手机号、所属机构、部门）
-   显示用户角色标签
-   支持按机构、部门、角色筛选
-   支持用户状态管理（启用/禁用）

**用户创建/编辑**：

-   基础信息：姓名、手机号、密码
-   组织信息：所属机构、所属部门
-   角色分配：支持多角色选择（只显示当前机构的角色）
-   用户权限预览：显示用户通过角色获得的有效权限

#### 用户权限预览界面

```
┌─────────────────────────────────────────────────────────────┐
│ 用户权限预览 - 张老师                                       │
├─────────────────────────────────────────────────────────────┤
│ 角色：[老师] [班主任]                                       │
├─────────────────────────────────────────────────────────────┤
│ 功能权限：                                                  │
│ ✓ 学员管理 (查看、新增、编辑)                               │
│ ✓ 班级管理 (查看、编辑)                                     │
│ ✓ 课表管理 (查看)                                           │
│ ✗ 财务管理                                                  │
├─────────────────────────────────────────────────────────────┤
│ 数据权限：                                                  │
│ • 学员数据：部分 (仅分配给自己的学员)                       │
│ • 班级数据：部分 (仅负责的班级)                             │
│ • 课表数据：部分 (仅自己的课表)                             │
└─────────────────────────────────────────────────────────────┘
```

### 6.4 前端开发技术要点

#### 组件结构设计

```
src/pages/admin/account/
├── index.tsx                    # 账户管理主页面 (Tab容器)
├── components/
│   ├── UserManagement/
│   │   ├── index.tsx           # 用户管理主组件
│   │   ├── UserList.tsx        # 用户列表
│   │   ├── UserForm.tsx        # 用户创建/编辑表单
│   │   └── UserPermissionPreview.tsx  # 用户权限预览
│   └── RoleManagement/
│       ├── index.tsx           # 角色管理主组件
│       ├── RoleList.tsx        # 左侧角色列表
│       ├── RoleEditor.tsx      # 右侧角色编辑器
│       ├── PermissionTree.tsx  # 功能权限树
│       └── DataPermissionGroup.tsx    # 数据权限分组
```

#### 状态管理

```typescript
// 角色管理状态
interface RoleManagementState {
    selectedRoleId: string | null;
    roles: Role[];
    permissions: Permission[];
    dataPermissions: Record<string, DataPermission[]>;
    loading: boolean;
    saving: boolean;
    hasUnsavedChanges: boolean;
}

// 用户管理状态
interface UserManagementState {
    users: User[];
    roles: Role[];
    institutions: Institution[];
    departments: Department[];
    selectedUser: User | null;
    loading: boolean;
}
```

#### API 调用示例

```typescript
// 获取角色列表
const fetchRoles = async (institutionId?: string) => {
    const params = institutionId ? { institution_id: institutionId } : {};
    const response = await api.get("/admin/roles", { params });
    return response.data;
};

// 获取所有权限
const fetchAllPermissions = async () => {
    const response = await api.get("/admin/permissions/all");
    return response.data;
};

// 更新角色权限
const updateRolePermissions = async (
    roleId: string,
    data: {
        permission_ids: number[];
        data_permission_ids: number[];
    }
) => {
    const response = await api.put(`/admin/roles/${roleId}`, data);
    return response.data;
};
```

#### 权限配置组件设计

**功能权限树组件**：

```typescript
interface PermissionTreeProps {
    permissions: Permission[];
    selectedIds: number[];
    onChange: (selectedIds: number[]) => void;
    disabled?: boolean;
}
```

**数据权限组件**：

```typescript
interface DataPermissionGroupProps {
    dataPermissions: Record<string, DataPermission[]>;
    selectedIds: number[];
    onChange: (selectedIds: number[]) => void;
    disabled?: boolean;
}
```

## 7. 扩展性考虑

### 7.1 临时权限支持

未来如需支持临时权限，可增加以下表：

```sql
CREATE TABLE user_temp_permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    permission_id BIGINT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    granted_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 7.2 角色模板支持

未来如需支持基于系统角色创建机构角色的功能，可在角色表增加：

```sql
ALTER TABLE roles ADD COLUMN parent_role_id BIGINT COMMENT '基于哪个系统角色创建';
```

### 7.3 权限委托支持

未来如需支持权限委托，可增加委托关系表和委托权限表。

### 7.4 审批流程支持

未来如需支持权限申请审批，可增加权限申请表和审批流程表。

## 8. 安全考虑

### 8.1 权限校验

-   所有 API 接口必须进行权限校验
-   数据权限在查询层面进行过滤
-   敏感操作需要二次验证

### 8.2 日志记录

-   记录所有权限变更操作
-   记录用户登录和关键操作日志
-   支持权限变更审计

---

**文档版本**: v1.0  
**创建时间**: 2025-08-04  
**最后更新**: 2025-08-04  
**维护人员**: 开发团队
