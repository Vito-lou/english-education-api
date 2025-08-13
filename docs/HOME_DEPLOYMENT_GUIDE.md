# 🏠 家庭环境部署指南

## 📋 概述

本指南将帮助你在家里的电脑上完整部署英语教育管理系统，确保与当前开发环境的数据完全一致。

## 🛠️ 环境要求

### 必需软件
- **Herd** (推荐) - 包含 PHP 8.2+、MySQL 8.0+、Node.js 18+
- **Composer** - PHP 依赖管理
- **pnpm** - 前端包管理器

### 可选软件
- **Git** - 代码版本控制
- **VS Code** - 代码编辑器

## 🚀 快速部署

### 方法一：一键部署脚本

```bash
# 1. 确保代码已克隆到正确位置
cd ~/Herd
git clone <后端仓库地址> english-education-api
git clone <前端仓库地址> english-education-frontend

# 2. 运行一键部署脚本
cd english-education-api
chmod +x deploy_home.sh
./deploy_home.sh
```

### 方法二：手动部署

#### 步骤 1：后端部署

```bash
# 进入后端目录
cd ~/Herd/english-education-api

# 安装依赖
composer install

# 配置环境
cp .env.example .env
php artisan key:generate

# 创建数据库
mysql -u root -e "CREATE DATABASE english_education;"

# 运行迁移和种子数据
php artisan migrate:fresh --seed

# 确保数据一致性
php reset_user_passwords.php
```

#### 步骤 2：前端部署

```bash
# 进入前端目录
cd ~/Herd/english-education-frontend

# 安装依赖
pnpm install

# 配置环境
echo 'VITE_API_BASE_URL=http://english-education-api.test/api' > .env.local

# 启动开发服务器
pnpm dev
```

## 🔧 详细配置

### 后端配置 (.env)

```env
APP_NAME="英语教育管理系统"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://english-education-api.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=english_education
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### 前端配置 (.env.local)

```env
VITE_API_BASE_URL=http://english-education-api.test/api
VITE_APP_NAME=英语教育管理系统
```

### Herd 站点配置

1. 打开 Herd 应用
2. 点击 "Sites" 标签
3. 添加站点：
   - **目录**: `~/Herd/english-education-api`
   - **域名**: `english-education-api.test`
   - **PHP 版本**: 8.2+

## 🧪 验证部署

### 1. 检查后端 API

```bash
# 健康检查
curl http://english-education-api.test/api/health

# 登录测试
curl -X POST http://english-education-api.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "admin123"}'
```

### 2. 检查前端访问

打开浏览器访问：`http://localhost:5173`

### 3. 检查数据一致性

```bash
cd ~/Herd/english-education-api

# 检查用户数据
php artisan tinker --execute="
\App\Models\User::whereIn('email', ['admin@example.com', 'test@example.com'])
  ->with('roles')
  ->get()
  ->each(function(\$user) {
    echo \$user->name . ' (' . \$user->email . ') - ' . \$user->roles->pluck('name')->join(', ') . PHP_EOL;
  });
"

# 检查菜单数据
php artisan tinker --execute="
echo 'Menus: ' . \App\Models\SystemMenu::count() . PHP_EOL;
echo 'Permissions: ' . \App\Models\Permission::count() . PHP_EOL;
"
```

## 🔑 测试账户

| 用户类型 | 邮箱 | 密码 | 角色 |
|---------|------|------|------|
| 系统管理员 | admin@example.com | admin123 | 超级管理员 |
| 测试用户 | test@example.com | password | 教师 |

## 📋 菜单结构

### 一级菜单
- 仪表盘
- 机构管理
- 教务中心
- 财务管理
- 应用中心

### 二级菜单
**机构管理**
- 组织架构 (`/institution/organization`)
- 账户管理 (`/institution/accounts`)

**教务中心**
- 学员管理 (`/academic/students`)
- 课程管理 (`/academic/courses`)
- 班级管理 (`/academic/classes`)
- 课表管理 (`/academic/schedules`)

**应用中心**
- 菜单管理 (`/apps/menu`)

## 🔄 数据同步

### 重置脚本功能

`reset_user_passwords.php` 脚本会：

1. ✅ 创建/重置 admin 和 test 用户
2. ✅ 分配正确的角色权限
3. ✅ 检查菜单结构完整性
4. ✅ 修复菜单路径匹配问题

### 使用方法

```bash
cd ~/Herd/english-education-api
php reset_user_passwords.php
```

## 🐛 常见问题

### 1. 数据库连接失败
```bash
# 检查 MySQL 服务
brew services list | grep mysql

# 重启 MySQL (如果使用 Herd)
# 在 Herd 应用中重启 MySQL 服务
```

### 2. 权限问题
```bash
# 设置正确的文件权限
chmod -R 755 storage bootstrap/cache
```

### 3. 菜单显示异常
```bash
# 重新运行菜单种子文件
php artisan db:seed --class=SystemMenuSeeder
php artisan db:seed --class=MenuBasedPermissionSeeder
```

### 4. 前端无法连接后端
- 检查 `.env.local` 中的 API 地址
- 确保后端服务正常运行
- 检查 Herd 站点配置

## 📞 技术支持

如果遇到问题，请检查：
1. 所有依赖是否正确安装
2. 环境配置是否正确
3. 数据库是否正常运行
4. 重置脚本是否成功执行

## 🎯 部署检查清单

- [ ] Herd 已安装并运行
- [ ] 代码已克隆到正确位置
- [ ] 后端依赖已安装
- [ ] 数据库已创建
- [ ] 迁移和种子数据已运行
- [ ] 重置脚本已执行
- [ ] 前端依赖已安装
- [ ] 环境变量已配置
- [ ] API 连接测试通过
- [ ] 前端页面可正常访问
- [ ] 测试账户可正常登录
- [ ] 菜单显示正常
