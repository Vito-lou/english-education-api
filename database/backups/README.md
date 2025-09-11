# 数据库备份目录

这个目录用于存储数据库备份文件，确保多台电脑之间的数据同步。

## 使用方法

### 备份数据库
```bash
# 在项目根目录执行
./scripts/backup-database.sh
```

### 还原数据库
```bash
# 在项目根目录执行
./scripts/restore-database.sh
```

## 工作流程

### 主开发电脑（添加新数据后）
1. 备份数据库：`./scripts/backup-database.sh`
2. 提交到Git：`git add database/backups/latest.sql && git commit -m "更新数据库备份"`
3. 推送到远程：`git push`

### 其他电脑（同步数据）
1. 拉取最新代码：`git pull`
2. 还原数据库：`./scripts/restore-database.sh`

## 注意事项

- `latest.sql` 文件会被Git跟踪，包含最新的数据库备份
- 其他带时间戳的备份文件会被Git忽略
- 还原数据库前请确保已备份当前数据（如有需要）
- 备份文件包含真实数据，请注意数据安全
