# 后端项目配置指南

> 英语教育管理系统后端 API 本地开发环境配置
> 技术栈：Laravel 12 + MySQL 8.0 + Laravel Sanctum
> 部署工具：Laravel Herd

## 前置要求

### 环境要求

-   **macOS**: 推荐 macOS 12.0 或更高版本
-   **Laravel Herd**: 最新版本
-   **Git**: 用于代码管理

### 安装 Laravel Herd

1. **下载安装 Herd**

    ```bash
    # 访问官网下载：https://herd.laravel.com/
    # 或使用 Homebrew 安装
    brew install --cask herd
    ```

2. **启动 Herd**
    - 打开 Herd 应用
    - 确保 Herd 正在运行（菜单栏有图标）

## 项目配置步骤

### 1. 克隆项目

```bash
# 克隆项目到本地
git clone <repository-url> english-education-api
cd english-education-api
```

### 2. 配置 Herd 站点

```bash
# 方法一：使用 Herd CLI（推荐）
# 在项目根目录执行
herd link english-education-api

# 方法二：通过 Herd GUI
# 1. 打开 Herd 应用
# 2. 点击 "Add Site"
# 3. 选择项目目录
# 4. 设置域名为 "english-education-api"
```

### 3. 安装 PHP 依赖

```bash
# 使用 Herd 内置的 Composer
~/Library/Application\ Support/Herd/bin/composer install

# 或者如果已配置 PATH
composer install
```

### 4. 环境配置

```bash
# 复制环境配置文件
cp .env.example .env

# 生成应用密钥
PATH="$HOME/Library/Application Support/Herd/bin:$PATH" php artisan key:generate
```

### 5. 配置数据库

编辑 `.env` 文件，配置数据库连接：

```env
# 数据库配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=english_education
DB_USERNAME=root
DB_PASSWORD=

# 应用配置
APP_NAME="英语教育管理系统"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://english-education-api.test

# 其他配置保持默认
```

### 6. 创建数据库

```bash
# 方法一：使用 Herd 内置的 MySQL
# 1. 打开 Herd 应用
# 2. 点击 "Database" 标签
# 3. 点击 "Create Database"
# 4. 输入数据库名：english_education

# 方法二：使用命令行
mysql -u root -e "CREATE DATABASE english_education;"
```

### 7. 运行数据库迁移

```bash
# 运行迁移文件
PATH="$HOME/Library/Application Support/Herd/bin:$PATH" php artisan migrate

# 运行种子文件（创建测试用户）
PATH="$HOME/Library/Application Support/Herd/bin:$PATH" php artisan db:seed --class=UserSeeder
```

### 8. 验证安装

```bash
# 检查站点是否可访问
curl -I http://english-education-api.test

# 测试 API 端点
curl -X GET "http://english-education-api.test/api/auth/user" \
  -H "Accept: application/json"

# 应该返回：{"message":"Unauthenticated."}
```

## 测试账户

系统已预置以下测试账户：

| 角色     | 邮箱              | 密码     | 说明         |
| -------- | ----------------- | -------- | ------------ |
| 普通用户 | test@example.com  | password | 用于功能测试 |
| 管理员   | admin@example.com | admin123 | 管理员权限   |

## 常用命令

### Herd 相关命令

```bash
# 查看所有站点
herd sites

# 重启站点
herd restart english-education-api

# 查看日志
herd logs english-education-api

# 打开站点目录
herd open english-education-api
```

### Laravel 命令

```bash
# 设置 PATH（每次新终端都需要）
export PATH="$HOME/Library/Application Support/Herd/bin:$PATH"

# 或者直接使用完整路径
alias herd-php="$HOME/Library/Application\ Support/Herd/bin/php"
alias herd-composer="$HOME/Library/Application\ Support/Herd/bin/composer"

# 常用 Artisan 命令
php artisan migrate          # 运行迁移
php artisan migrate:fresh    # 重置数据库
php artisan db:seed         # 运行种子文件
php artisan route:list      # 查看路由列表
php artisan tinker          # 进入交互模式
```

## 故障排除

### 常见问题及解决方案

#### 1. 站点无法访问

```bash
# 检查 Herd 是否运行
ps aux | grep herd

# 重启 Herd 服务
herd restart

# 检查站点配置
herd sites
```

#### 2. 数据库连接失败

```bash
# 检查 MySQL 是否运行
herd status

# 测试数据库连接
mysql -u root -e "SHOW DATABASES;"

# 检查 .env 配置
cat .env | grep DB_
```

#### 3. Composer 依赖问题

```bash
# 清除 Composer 缓存
~/Library/Application\ Support/Herd/bin/composer clear-cache

# 重新安装依赖
rm -rf vendor composer.lock
~/Library/Application\ Support/Herd/bin/composer install
```

#### 4. 权限问题

```bash
# 设置正确的目录权限
chmod -R 755 storage bootstrap/cache
chown -R $(whoami) storage bootstrap/cache
```

#### 5. API 路由不工作

```bash
# 清除路由缓存
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# 检查路由列表
php artisan route:list
```

## 开发工作流

### 日常开发流程

1. **启动开发环境**

    ```bash
    # 确保 Herd 运行
    herd status

    # 检查站点状态
    curl -I http://english-education-api.test
    ```

2. **数据库操作**

    ```bash
    # 创建新迁移
    php artisan make:migration create_example_table

    # 运行迁移
    php artisan migrate

    # 回滚迁移
    php artisan migrate:rollback
    ```

3. **创建新功能**

    ```bash
    # 创建控制器
    php artisan make:controller ExampleController --api

    # 创建模型
    php artisan make:model Example -m

    # 创建种子文件
    php artisan make:seeder ExampleSeeder
    ```

### 代码规范

-   **PSR-12** 编码标准
-   **Laravel** 最佳实践
-   **API 资源** 用于数据转换
-   **表单请求** 用于数据验证

### 测试

```bash
# 运行测试
php artisan test

# 创建测试
php artisan make:test ExampleTest
```

## 部署注意事项

### 生产环境配置

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# 数据库配置
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```

### 性能优化

```bash
# 缓存配置
php artisan config:cache

# 缓存路由
php artisan route:cache

# 缓存视图
php artisan view:cache

# 优化自动加载
composer install --optimize-autoloader --no-dev
```

## 团队协作

### Git 工作流

1. **拉取最新代码**

    ```bash
    git pull origin main
    ```

2. **安装/更新依赖**

    ```bash
    composer install
    ```

3. **运行迁移**

    ```bash
    php artisan migrate
    ```

4. **创建功能分支**
    ```bash
    git checkout -b feature/new-feature
    ```

### 代码提交规范

```bash
# 提交格式
git commit -m "feat: 添加用户认证功能"
git commit -m "fix: 修复登录验证问题"
git commit -m "docs: 更新 API 文档"
```

## 相关链接

-   **Laravel 文档**: https://laravel.com/docs
-   **Laravel Herd**: https://herd.laravel.com/
-   **Laravel Sanctum**: https://laravel.com/docs/sanctum
-   **项目前端**: [前端配置指南](frontend-setup.md)
