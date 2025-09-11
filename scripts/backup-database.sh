#!/bin/bash

# 数据库备份脚本
# 使用方法: ./scripts/backup-database.sh

# 配置
DB_NAME="english_education"
DB_USER="root"
BACKUP_DIR="database/backups"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="backup_${DATE}.sql"

# 创建备份目录
mkdir -p $BACKUP_DIR

echo "开始备份数据库..."
echo "数据库: $DB_NAME"
echo "备份文件: $BACKUP_DIR/$BACKUP_FILE"

# 执行备份
mysqldump -u $DB_USER -p $DB_NAME > $BACKUP_DIR/$BACKUP_FILE

if [ $? -eq 0 ]; then
    echo "✅ 数据库备份成功！"
    
    # 创建最新备份的软链接
    cd $BACKUP_DIR
    ln -sf $BACKUP_FILE latest.sql
    cd - > /dev/null
    
    echo "📁 备份文件: $BACKUP_DIR/$BACKUP_FILE"
    echo "🔗 最新备份: $BACKUP_DIR/latest.sql"
    
    # 显示备份文件大小
    SIZE=$(du -h $BACKUP_DIR/$BACKUP_FILE | cut -f1)
    echo "📊 文件大小: $SIZE"
    
    echo ""
    echo "💡 提示："
    echo "1. 请将备份文件提交到Git: git add $BACKUP_DIR/latest.sql"
    echo "2. 其他电脑可以使用: ./scripts/restore-database.sh 还原数据"
else
    echo "❌ 数据库备份失败！"
    exit 1
fi
