# API 模块结构设计

## 📁 目录结构

```
app/Http/Controllers/Api/
├── Admin/              # 管理后台API (english-education-frontend)
│   ├── InstitutionController.php
│   ├── DepartmentController.php
│   ├── RoleController.php
│   ├── UserController.php
│   ├── StudentController.php
│   ├── TeacherController.php
│   ├── ClassController.php
│   └── FinanceController.php
├── H5/                 # H5端API (english-education-h5)
│   ├── StudentController.php
│   ├── CourseController.php
│   ├── ProgressController.php
│   └── ParentController.php
└── AuthController.php  # 公共认证API
```

## 🎯 API 路由设计

### 公共API (两端都可使用)
```
POST /api/auth/login          # 登录
POST /api/auth/logout         # 登出
GET  /api/auth/user           # 获取当前用户信息
```

### 管理后台API (`/api/admin/`)
**用于 english-education-frontend 项目**

#### 机构管理
```
GET    /api/admin/institutions                    # 机构列表
POST   /api/admin/institutions                    # 创建机构
GET    /api/admin/institutions/{id}               # 机构详情
PUT    /api/admin/institutions/{id}               # 更新机构
DELETE /api/admin/institutions/{id}               # 删除机构
GET    /api/admin/institutions/{id}/statistics    # 机构统计
```

#### 部门管理
```
GET    /api/admin/departments           # 部门列表
POST   /api/admin/departments           # 创建部门
GET    /api/admin/departments/{id}      # 部门详情
PUT    /api/admin/departments/{id}      # 更新部门
DELETE /api/admin/departments/{id}      # 删除部门
GET    /api/admin/departments/tree      # 部门树形结构
```

#### 角色管理
```
GET    /api/admin/roles                 # 角色列表
POST   /api/admin/roles                 # 创建角色
GET    /api/admin/roles/{id}            # 角色详情
PUT    /api/admin/roles/{id}            # 更新角色
DELETE /api/admin/roles/{id}            # 删除角色
```

#### 用户管理
```
GET    /api/admin/users                      # 用户列表
POST   /api/admin/users                      # 创建用户
GET    /api/admin/users/{id}                 # 用户详情
PUT    /api/admin/users/{id}                 # 更新用户
DELETE /api/admin/users/{id}                 # 删除用户
POST   /api/admin/users/{id}/assign-roles    # 分配角色
```

### H5端API (`/api/h5/`)
**用于 english-education-h5 项目**

#### 学员信息
```
GET /api/h5/students/{id}/profile      # 学员档案
GET /api/h5/students/{id}/progress     # 学习进度
GET /api/h5/students/{id}/class-hours  # 课时信息
```

#### 课程信息
```
GET /api/h5/courses/levels             # 课程级别列表
GET /api/h5/courses/levels/{level}     # 级别详情
```

## 🔐 权限控制

### 管理后台API
- 需要登录认证
- 需要角色权限验证
- 支持数据权限控制（机构、部门级别）

### H5端API
- 需要登录认证
- 只能访问自己相关的数据
- 家长只能查看自己孩子的信息

## 📱 前端项目对应

### english-education-frontend (管理后台)
- 使用 `/api/admin/*` 接口
- 功能：机构管理、用户管理、学员管理、财务管理等
- 用户：校长、教务、老师、销售、财务等

### english-education-h5 (家长端)
- 使用 `/api/h5/*` 接口
- 功能：查看孩子信息、学习进度、课时余额等
- 用户：家长

## 🔄 公共接口

某些接口两端都可能使用：

1. **认证接口** - 登录、登出、获取用户信息
2. **基础数据接口** - 如课程级别、机构信息等
3. **文件上传接口** - 头像、附件等

## 💡 设计优势

1. **清晰的模块分离** - 不同端的API分开管理
2. **便于权限控制** - 不同模块可以有不同的权限策略
3. **便于维护** - 修改一端的API不影响另一端
4. **便于扩展** - 后续可以轻松添加新的端（如小程序、APP等）

## 🚀 下一步开发

1. **完善管理后台API** - 角色管理、用户管理等
2. **开发H5端API** - 学员查询、进度查看等
3. **前端页面开发** - 对应的管理界面
4. **权限中间件** - 实现细粒度权限控制
