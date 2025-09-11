# 英语教育管理系统 - 后端 API

> 当前版本：v2.0 - 双系统架构设计
> 最后更新：2024 年 12 月 19 日

## 项目简介

英语教育管理系统的后端 API 服务，提供原典法线下教学管理和标准化线上课程分发的数据接口。

## 项目架构

### 前后端分离架构

```
┌─────────────────────────────────────────────────────────┐
│                    前端项目                              │
│            React 18 + TypeScript + Vite                │
│              shadcn/ui + Tailwind CSS                  │
│                   (独立仓库)                            │
├─────────────────────────────────────────────────────────┤
│                    后端 API                             │
│                Laravel 12 + MySQL                      │
│                  (当前项目)                             │
└─────────────────────────────────────────────────────────┘
```

### 相关仓库

-   **前端项目**: `english-education-frontend` (需要创建)
-   **后端项目**: `english-education-backend` (当前项目)
-   **文档**: [产品设计文档](docs/product-versions/README.md)

## 技术栈

### 后端 (当前项目)

-   **框架**: Laravel 12 + MySQL 8.0
-   **认证**: Laravel Sanctum
-   **API**: RESTful API
-   **队列**: Redis + Laravel Queue
-   **存储**: 本地存储 + 阿里云 OSS

### 前端 (独立项目)

-   **框架**: React 18 + TypeScript + Vite
-   **包管理**: pnpm (强制使用)
-   **状态管理**: Zustand + React Query
-   **UI 库**: shadcn/ui + Tailwind CSS
-   **HTTP 客户端**: Axios
-   **图标**: Lucide React

## 快速开始

### 后端开发

```bash
# 安装依赖
composer install

# 环境配置
cp .env.example .env
php artisan key:generate

# 数据库迁移
php artisan migrate

# 启动开发服务器
php artisan serve  # 运行在 http://localhost:8000
```

## 🗄️ 数据库同步方案

本项目使用真实数据进行开发，为确保多台电脑之间的数据一致性，我们提供了完整的数据库备份和还原方案。

### 📋 使用场景

-   **主开发电脑**：添加真实数据后，需要备份并同步到其他电脑
-   **协同开发电脑**：需要获取最新的真实数据进行开发
-   **新环境搭建**：快速获取完整的开发数据

### 🛠️ 可用工具

#### 方法 1：Artisan 命令（推荐）

```bash
# 备份数据库
php artisan db:backup

# 还原数据库
php artisan db:backup --restore
```

#### 方法 2：Shell 脚本

```bash
# 备份数据库
./scripts/backup-database.sh

# 还原数据库
./scripts/restore-database.sh
```

### 📋 完整工作流程

#### 🖥️ 主开发电脑（添加新数据后）

1. **备份数据库**

    ```bash
    php artisan db:backup
    ```

2. **提交备份到 Git**
    ```bash
    git add database/backups/latest.sql
    git commit -m "更新数据库备份 - 添加新的学生和排课数据"
    git push
    ```

#### 🏠 其他电脑（同步数据）

1. **拉取最新代码**

    ```bash
    git pull
    ```

2. **还原数据库**

    ```bash
    php artisan db:backup --restore
    ```

    系统会显示备份文件信息并询问确认：

    ```
    开始还原数据库...
    📁 备份文件: /path/to/database/backups/latest.sql
    📊 文件大小: 128.64 KB
    📅 修改时间: 2025-09-11 02:54:36

    ⚠️  警告：此操作将覆盖当前数据库数据，是否继续？ (yes/no) [no]:
    ```

    输入 `yes` 确认还原。

3. **验证数据同步**

    ```bash
    # 验证学生数据
    php artisan tinker --execute="echo '学生总数: ' . App\Models\Student::count() . PHP_EOL;"

    # 验证班级数据
    php artisan tinker --execute="echo '班级总数: ' . App\Models\ClassModel::count() . PHP_EOL;"

    # 验证机构信息
    php artisan tinker --execute="echo '机构名称: ' . App\Models\Institution::first()->name . PHP_EOL;"
    ```

### 🔄 日常开发流程示例

#### 场景 1：添加新学生数据

```bash
# 1. 在界面中添加学生数据
# 2. 备份数据库
php artisan db:backup

# 3. 提交到Git
git add database/backups/latest.sql
git commit -m "添加新学生：张三、李四"
git push
```

#### 场景 2：修改班级排课

```bash
# 1. 在界面中修改排课
# 2. 备份数据库
php artisan db:backup

# 3. 提交到Git
git add database/backups/latest.sql
git commit -m "更新A1班级排课安排"
git push
```

#### 场景 3：同步到其他电脑

```bash
# 1. 拉取最新代码
git pull

# 2. 还原数据库
php artisan db:backup --restore

# 3. 开始开发
```

### 📁 备份文件结构

```
english-education-api/
├── database/
│   └── backups/
│       ├── README.md           # 说明文档
│       ├── latest.sql          # 最新备份（Git跟踪）
│       ├── backup_*.sql        # 历史备份（Git忽略）
│       └── .gitignore          # Git配置
├── scripts/
│   ├── backup-database.sh      # 备份脚本
│   └── restore-database.sh     # 还原脚本
└── app/Console/Commands/
    └── DatabaseBackup.php      # Artisan命令
```

### ⚠️ 重要注意事项

#### 数据安全

-   备份文件包含真实数据，请注意保护隐私
-   不要将备份文件上传到公开仓库
-   定期清理本地历史备份文件

#### 操作安全

-   还原数据库前会有确认提示
-   还原操作会**完全覆盖**当前所有数据
-   建议在还原前先备份当前数据（如有重要修改）

#### 团队协作

-   约定由一台主电脑负责数据维护
-   其他电脑主要用于代码开发
-   数据修改后及时备份和提交

### 🚀 最佳实践

1. **每次添加重要数据后立即备份**
2. **使用有意义的提交信息**
3. **定期同步数据到其他电脑**
4. **保持备份文件的最新状态**

### 🔧 故障排除

#### 备份失败

-   检查数据库连接配置（`.env`文件）
-   确认 `mysqldump` 命令可用
-   检查磁盘空间是否充足

#### 还原失败

-   检查备份文件是否存在：`ls -la database/backups/latest.sql`
-   确认 `mysql` 命令可用
-   检查数据库权限

#### Git 冲突

-   如果 `latest.sql` 有冲突，选择最新的版本
-   必要时手动合并数据

### 📖 详细文档

-   **完整工作流程**：[数据库同步工作流程文档](docs/database-sync-workflow.md)
-   **AI 助手指令**：[AI 数据库还原指令指南](AI_DATABASE_RESTORE_GUIDE.md)

### 🤖 AI 助手使用说明

如果您使用 AI 助手来帮助还原数据库，请让 AI 先阅读 [AI_DATABASE_RESTORE_GUIDE.md](AI_DATABASE_RESTORE_GUIDE.md) 文档，该文档包含了准确的操作指令和验证步骤。

### 前端项目创建

详见：[前端项目配置指南](docs/frontend-setup.md)

## API 文档

### 基础信息

-   **API 基础地址**: `http://localhost:8000/api`
-   **认证方式**: Bearer Token (Laravel Sanctum)
-   **响应格式**: JSON

### 认证接口

```
POST /api/auth/login      # 用户登录
POST /api/auth/register   # 用户注册
POST /api/auth/logout     # 用户登出
GET  /api/user           # 获取当前用户信息
```

### 原典法系统接口

```
GET    /api/offline/students           # 获取学生列表
POST   /api/offline/students           # 创建学生
GET    /api/offline/students/{id}      # 获取学生详情
PUT    /api/offline/students/{id}      # 更新学生信息
DELETE /api/offline/students/{id}      # 删除学生

GET    /api/offline/courses            # 获取课程列表
POST   /api/offline/courses            # 创建课程
GET    /api/offline/lessons            # 获取课时记录
POST   /api/offline/lessons            # 创建课时记录
```

### 线上课程系统接口

```
GET    /api/online/courses             # 获取线上课程
POST   /api/online/orders              # 创建订单
GET    /api/online/distributors        # 获取分销商列表
POST   /api/online/distributors        # 创建分销商
```

## 📖 开发规范

### 🔌 API 响应格式规范

项目中存在两种 API 响应格式，需要注意区分：

#### 1. 标准响应格式（推荐）

```json
// 成功响应
{
  "code": 200,
  "message": "操作成功",
  "data": {
    // 具体数据
  }
}

// 错误响应
{
  "code": 400,
  "message": "错误信息",
  "errors": {
    "field": ["具体错误信息"]
  }
}
```

#### 2. 兼容格式（部分旧接口）

```json
// 成功响应
{
  "success": true,
  "message": "操作成功",
  "data": {
    // 具体数据
  }
}

// 错误响应
{
  "success": false,
  "message": "错误信息",
  "errors": {
    "field": ["具体错误信息"]
  }
}
```

**⚠️ 注意**：新开发的 API 应统一使用标准响应格式（code 字段）

### 📄 分页响应格式

```json
{
  "code": 200,
  "message": "获取成功",
  "data": {
    "data": [...],           // 当前页数据
    "current_page": 1,       // 当前页码
    "last_page": 10,         // 最后一页
    "per_page": 15,          // 每页数量
    "total": 150,            // 总记录数
    "from": 1,               // 当前页起始记录号
    "to": 15                 // 当前页结束记录号
  }
}
```

### 🔐 认证规范

**Token 存储**：

-   前端使用 `localStorage.getItem("auth_token")` 存储 token
-   API 请求头：`Authorization: Bearer {token}`

**权限控制**：

-   所有 `/api/admin/*` 接口需要认证
-   基于机构的数据隔离（institution_id）
-   角色权限控制（admin, teacher, student）

### 🏗️ Controller 开发规范

#### 标准 CRUD 操作

```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ExampleController extends Controller
{
    /**
     * 获取列表（支持分页和筛选）
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Model::where('institution_id', $user->institution_id);

        // 筛选条件
        if ($request->filled('keyword')) {
            $query->where('name', 'like', "%{$request->keyword}%");
        }

        // 分页
        $data = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $data,
        ]);
    }

    /**
     * 创建资源
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            // 其他验证规则
        ]);

        $model = Model::create([
            ...$validated,
            'institution_id' => Auth::user()->institution_id,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $model,
        ]);
    }

    /**
     * 更新资源
     */
    public function update(Request $request, Model $model): JsonResponse
    {
        // 权限检查
        if ($model->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $model->update($validated);

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $model,
        ]);
    }

    /**
     * 删除资源
     */
    public function destroy(Model $model): JsonResponse
    {
        // 权限检查
        if ($model->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $model->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }
}
```

### 🗄️ Model 开发规范

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExampleModel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'institution_id',
        'created_by',
        // 其他可填充字段
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // 关联关系
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // 作用域
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // 访问器
    public function getStatusNameAttribute()
    {
        return match($this->status) {
            'active' => '启用',
            'inactive' => '禁用',
            default => '未知',
        };
    }
}
```

### ⚠️ 常见错误避免

1. **权限检查**：所有操作都要检查 `institution_id`
2. **数据验证**：使用 Laravel 的表单验证
3. **错误处理**：统一的错误响应格式
4. **软删除**：重要数据使用软删除
5. **关联加载**：避免 N+1 查询问题

## 开发计划

-   [开发计划详情](TODO.md)
-   [产品设计文档](docs/product-versions/README.md)

## 项目状态

-   ✅ 项目初始化
-   ✅ 产品架构设计
-   ✅ 前端技术栈确定
-   🔄 数据库设计中
-   ⏳ 核心功能开发待开始
-   ⏳ 前端项目创建待开始

## 📞 联系方式

如有问题，请查看 [产品设计文档](docs/product-versions/README.md) 或提交 Issue。

## 🤝 贡献指南

1. 遵循项目的 API 开发规范
2. 所有 API 都要进行权限检查
3. 使用统一的响应格式
4. 为新功能添加适当的数据验证
5. 重要数据使用软删除
6. 避免 N+1 查询问题
