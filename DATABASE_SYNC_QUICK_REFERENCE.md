# 数据库同步快速参考

## 🚀 常用命令

### 备份数据库
```bash
php artisan db:backup
```

### 还原数据库
```bash
php artisan db:backup --restore
```

### 验证数据
```bash
php artisan tinker --execute="
echo '学生总数: ' . App\Models\Student::count() . PHP_EOL;
echo '班级总数: ' . App\Models\ClassModel::count() . PHP_EOL;
echo '机构名称: ' . App\Models\Institution::first()->name . PHP_EOL;
"
```

## 📋 工作流程

### 主电脑（添加数据后）
```bash
# 1. 备份
php artisan db:backup

# 2. 提交
git add database/backups/latest.sql
git commit -m "更新数据库备份"
git push
```

### 其他电脑（同步数据）
```bash
# 1. 拉取
git pull

# 2. 还原
php artisan db:backup --restore
# 输入 yes 确认

# 3. 验证
php artisan tinker --execute="echo '机构: ' . App\Models\Institution::first()->name . PHP_EOL;"
```

## ✅ 预期结果

还原成功后应该看到：
- 学生总数：12
- 班级总数：2  
- 机构名称：星云英语
- A1班级排课：8个

## 🔧 故障排除

### 备份文件不存在
```bash
ls -la database/backups/latest.sql
# 如果不存在，先执行 git pull
```

### 数据库连接问题
```bash
php artisan migrate:status
# 检查 .env 数据库配置
```

## 📖 详细文档

- [完整README](README.md#🗄️-数据库同步方案)
- [AI指令指南](AI_DATABASE_RESTORE_GUIDE.md)
- [详细工作流程](docs/database-sync-workflow.md)
