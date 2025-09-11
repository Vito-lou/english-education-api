#!/bin/bash

# 数据库还原脚本
# 使用方法: ./scripts/restore-database.sh

# 配置
DB_NAME="english_education"
DB_USER="root"
BACKUP_DIR="database/backups"
BACKUP_FILE="$BACKUP_DIR/latest.sql"

echo "开始还原数据库..."
echo "数据库: $DB_NAME"
echo "备份文件: $BACKUP_FILE"

# 检查备份文件是否存在
if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ 备份文件不存在: $BACKUP_FILE"
    echo ""
    echo "💡 可用的备份文件："
    ls -la $BACKUP_DIR/*.sql 2>/dev/null || echo "   没有找到备份文件"
    exit 1
fi

# 显示备份文件信息
echo "📁 备份文件: $BACKUP_FILE"
SIZE=$(du -h $BACKUP_FILE | cut -f1)
echo "📊 文件大小: $SIZE"
MODIFIED=$(stat -c %y $BACKUP_FILE 2>/dev/null || stat -f %Sm $BACKUP_FILE)
echo "📅 修改时间: $MODIFIED"

echo ""
read -p "⚠️  警告：此操作将覆盖当前数据库数据，是否继续？(y/N): " -n 1 -r
echo

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "正在还原数据库..."
    
    # 执行还原
    mysql -u $DB_USER -p $DB_NAME < $BACKUP_FILE
    
    if [ $? -eq 0 ]; then
        echo "✅ 数据库还原成功！"
        echo ""
        echo "💡 提示："
        echo "1. 数据库已还原到备份时的状态"
        echo "2. 可以开始开发工作了"
    else
        echo "❌ 数据库还原失败！"
        exit 1
    fi
else
    echo "❌ 操作已取消"
    exit 0
fi
