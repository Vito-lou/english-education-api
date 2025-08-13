#!/bin/bash

# 英语教育管理系统 - 家庭环境一键部署脚本
# 使用方法：chmod +x deploy_home.sh && ./deploy_home.sh

set -e  # 遇到错误立即退出

echo "🚀 开始部署英语教育管理系统..."
echo "=================================="

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 项目目录
PROJECT_DIR="$HOME/Herd"
BACKEND_DIR="$PROJECT_DIR/english-education-api"
FRONTEND_DIR="$PROJECT_DIR/english-education-frontend"

# 检查必要软件
echo -e "${BLUE}📋 检查环境依赖...${NC}"

command -v php >/dev/null 2>&1 || { echo -e "${RED}❌ PHP 未安装，请先安装 Herd${NC}"; exit 1; }
command -v mysql >/dev/null 2>&1 || { echo -e "${RED}❌ MySQL 未安装，请先安装 Herd${NC}"; exit 1; }
command -v node >/dev/null 2>&1 || { echo -e "${RED}❌ Node.js 未安装，请先安装 Herd${NC}"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo -e "${RED}❌ Composer 未安装${NC}"; exit 1; }
command -v pnpm >/dev/null 2>&1 || { echo -e "${YELLOW}⚠️  pnpm 未安装，正在安装...${NC}"; npm install -g pnpm; }

echo -e "${GREEN}✅ 环境检查完成${NC}"

# 创建项目目录
echo -e "${BLUE}📁 创建项目目录...${NC}"
mkdir -p "$PROJECT_DIR"
cd "$PROJECT_DIR"

# 后端部署
echo -e "${BLUE}🔧 部署后端项目...${NC}"

if [ ! -d "$BACKEND_DIR" ]; then
    echo -e "${YELLOW}⚠️  后端项目不存在，请先克隆代码${NC}"
    echo "请运行：git clone <仓库地址> $BACKEND_DIR"
    exit 1
fi

cd "$BACKEND_DIR"

# 安装后端依赖
echo -e "${BLUE}📦 安装后端依赖...${NC}"
composer install --no-dev --optimize-autoloader

# 配置环境文件
echo -e "${BLUE}⚙️  配置后端环境...${NC}"
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
    echo -e "${GREEN}✅ 环境文件已创建${NC}"
fi

# 创建数据库
echo -e "${BLUE}🗄️  创建数据库...${NC}"
mysql -u root -e "CREATE DATABASE IF NOT EXISTS english_education;" 2>/dev/null || {
    echo -e "${YELLOW}⚠️  数据库创建失败，可能已存在${NC}"
}

# 运行数据库迁移和种子数据
echo -e "${BLUE}🌱 运行数据库迁移和种子数据...${NC}"
php artisan migrate:fresh --seed --force

# 运行重置脚本确保数据一致性
echo -e "${BLUE}🔄 确保数据一致性...${NC}"
php reset_user_passwords.php

# 前端部署
echo -e "${BLUE}🎨 部署前端项目...${NC}"

if [ ! -d "$FRONTEND_DIR" ]; then
    echo -e "${YELLOW}⚠️  前端项目不存在，请先克隆代码${NC}"
    echo "请运行：git clone <前端仓库地址> $FRONTEND_DIR"
    exit 1
fi

cd "$FRONTEND_DIR"

# 安装前端依赖
echo -e "${BLUE}📦 安装前端依赖...${NC}"
pnpm install

# 配置前端环境
echo -e "${BLUE}⚙️  配置前端环境...${NC}"
cat > .env.local << EOF
VITE_API_BASE_URL=http://english-education-api.test/api
VITE_APP_NAME=英语教育管理系统
EOF

echo -e "${GREEN}✅ 前端环境配置完成${NC}"

# 验证部署
echo -e "${BLUE}🧪 验证部署...${NC}"

cd "$BACKEND_DIR"

# 检查数据库连接
php artisan tinker --execute="
try {
    \DB::connection()->getPdo();
    echo '✅ 数据库连接正常' . PHP_EOL;
} catch (Exception \$e) {
    echo '❌ 数据库连接失败: ' . \$e->getMessage() . PHP_EOL;
    exit(1);
}
"

# 检查用户数据
php artisan tinker --execute="
\$adminUser = \App\Models\User::where('email', 'admin@example.com')->first();
\$testUser = \App\Models\User::where('email', 'test@example.com')->first();
if (\$adminUser && \$testUser) {
    echo '✅ 测试用户创建成功' . PHP_EOL;
    echo 'Admin: ' . \$adminUser->name . ' (角色: ' . \$adminUser->roles->pluck('name')->join(', ') . ')' . PHP_EOL;
    echo 'Test: ' . \$testUser->name . ' (角色: ' . \$testUser->roles->pluck('name')->join(', ') . ')' . PHP_EOL;
} else {
    echo '❌ 用户创建失败' . PHP_EOL;
    exit(1);
}
"

# 检查菜单数据
php artisan tinker --execute="
\$menuCount = \App\Models\SystemMenu::count();
if (\$menuCount >= 10) {
    echo '✅ 菜单数据正常 (共 ' . \$menuCount . ' 个菜单)' . PHP_EOL;
} else {
    echo '❌ 菜单数据不完整' . PHP_EOL;
    exit(1);
}
"

echo ""
echo -e "${GREEN}🎉 部署完成！${NC}"
echo "=================================="
echo -e "${BLUE}📋 访问信息：${NC}"
echo -e "后端 API: ${YELLOW}http://english-education-api.test${NC}"
echo -e "前端界面: ${YELLOW}http://localhost:5173${NC} (需要运行 pnpm dev)"
echo ""
echo -e "${BLUE}🔑 测试账户：${NC}"
echo -e "管理员: ${YELLOW}admin@example.com / admin123${NC}"
echo -e "测试用户: ${YELLOW}test@example.com / password${NC}"
echo ""
echo -e "${BLUE}🚀 启动前端开发服务器：${NC}"
echo -e "cd $FRONTEND_DIR && pnpm dev"
echo ""
echo -e "${GREEN}✨ 享受开发吧！${NC}"
