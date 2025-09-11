# 数据库同步工作流程

本文档描述如何在多台电脑之间同步真实数据库数据。

## 🎯 解决方案概述

我们使用 **数据库备份 + Git** 的方案来确保多台电脑的数据库数据完全一致：

- 主开发电脑：添加真实数据后，备份数据库并提交到Git
- 其他电脑：拉取代码后，还原数据库到最新状态

## 🛠️ 可用工具

### 方法1：Artisan命令（推荐）
```bash
# 备份数据库
php artisan db:backup

# 还原数据库
php artisan db:backup --restore
```

### 方法2：Shell脚本
```bash
# 备份数据库
./scripts/backup-database.sh

# 还原数据库
./scripts/restore-database.sh
```

## 📋 完整工作流程

### 主开发电脑（添加新数据后）

1. **备份数据库**
   ```bash
   php artisan db:backup
   ```

2. **提交备份到Git**
   ```bash
   git add database/backups/latest.sql
   git commit -m "更新数据库备份 - 添加新的学生和排课数据"
   git push
   ```

### 其他电脑（同步数据）

1. **拉取最新代码**
   ```bash
   git pull
   ```

2. **还原数据库**
   ```bash
   php artisan db:backup --restore
   ```

3. **确认数据同步**
   ```bash
   # 验证学生数据
   php artisan tinker --execute="echo '学生总数: ' . App\Models\Student::count() . PHP_EOL;"
   
   # 验证班级数据
   php artisan tinker --execute="echo '班级总数: ' . App\Models\ClassModel::count() . PHP_EOL;"
   ```

## 🔄 日常开发流程

### 场景1：添加新学生数据
```bash
# 1. 在界面中添加学生数据
# 2. 备份数据库
php artisan db:backup

# 3. 提交到Git
git add database/backups/latest.sql
git commit -m "添加新学生：张三、李四"
git push
```

### 场景2：修改班级排课
```bash
# 1. 在界面中修改排课
# 2. 备份数据库
php artisan db:backup

# 3. 提交到Git
git add database/backups/latest.sql
git commit -m "更新A1班级排课安排"
git push
```

### 场景3：同步到其他电脑
```bash
# 1. 拉取最新代码
git pull

# 2. 还原数据库
php artisan db:backup --restore

# 3. 开始开发
```

## 📁 文件结构

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

## ⚠️ 注意事项

### 数据安全
- 备份文件包含真实数据，请注意保护隐私
- 不要将备份文件上传到公开仓库
- 定期清理本地历史备份文件

### 操作安全
- 还原数据库前会有确认提示
- 还原操作会覆盖当前所有数据
- 建议在还原前先备份当前数据（如有重要修改）

### 团队协作
- 约定由一台主电脑负责数据维护
- 其他电脑主要用于代码开发
- 数据修改后及时备份和提交

## 🚀 最佳实践

1. **每次添加重要数据后立即备份**
2. **使用有意义的提交信息**
3. **定期同步数据到其他电脑**
4. **保持备份文件的最新状态**

## 🔧 故障排除

### 备份失败
- 检查数据库连接配置
- 确认mysqldump命令可用
- 检查磁盘空间是否充足

### 还原失败
- 检查备份文件是否存在
- 确认mysql命令可用
- 检查数据库权限

### Git冲突
- 如果latest.sql有冲突，选择最新的版本
- 必要时手动合并数据
