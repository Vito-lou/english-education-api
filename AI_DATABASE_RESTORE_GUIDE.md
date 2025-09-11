# AI数据库还原指令指南

> 本文档专门为AI助手编写，提供准确的数据库还原操作指令

## 🎯 任务目标

当用户要求同步数据库或还原数据库时，AI需要执行以下标准流程来确保数据完全一致。

## 📋 标准操作流程

### 步骤1：验证环境
首先检查项目环境和备份文件：

```bash
# 检查当前目录
pwd

# 检查备份文件是否存在
ls -la database/backups/latest.sql

# 检查数据库连接
php artisan tinker --execute="echo 'Database connection: ' . config('database.default') . PHP_EOL;"
```

### 步骤2：查看当前数据状态（可选）
在还原前，可以查看当前数据库状态：

```bash
php artisan tinker --execute="
echo '=== 当前数据库状态 ===' . PHP_EOL;
echo '学生总数: ' . App\Models\Student::count() . PHP_EOL;
echo '班级总数: ' . App\Models\ClassModel::count() . PHP_EOL;
echo '用户总数: ' . App\Models\User::count() . PHP_EOL;
try {
    echo '机构名称: ' . App\Models\Institution::first()->name . PHP_EOL;
} catch (Exception \$e) {
    echo '机构信息: 无数据' . PHP_EOL;
}
"
```

### 步骤3：执行数据库还原
使用Artisan命令还原数据库：

```bash
php artisan db:backup --restore
```

**重要说明**：
- 系统会显示备份文件信息
- 系统会询问确认：`⚠️  警告：此操作将覆盖当前数据库数据，是否继续？ (yes/no) [no]:`
- **AI必须回答 `yes` 来确认还原操作**

### 步骤4：验证还原结果
还原完成后，验证数据是否正确：

```bash
php artisan tinker --execute="
echo '=== 还原后数据库状态 ===' . PHP_EOL;
echo '学生总数: ' . App\Models\Student::count() . PHP_EOL;
echo '班级总数: ' . App\Models\ClassModel::count() . PHP_EOL;
echo '用户总数: ' . App\Models\User::count() . PHP_EOL;
echo '机构名称: ' . App\Models\Institution::first()->name . PHP_EOL;
echo 'A1班级排课数: ' . App\Models\ClassSchedule::where('class_id', 2)->count() . PHP_EOL;
\$students = App\Models\Student::take(5)->get();
echo '前5个学生: ' . \$students->pluck('name')->join(', ') . PHP_EOL;
"
```

### 步骤5：确认成功
如果看到以下数据，说明还原成功：
- 学生总数：12
- 班级总数：2
- 机构名称：星云英语
- A1班级排课数：8
- 包含真实学生姓名（如：许芯睿、刘熙予、周瞳彤等）

## 🔧 故障处理

### 如果备份文件不存在
```bash
# 检查备份目录
ls -la database/backups/

# 如果没有latest.sql，提示用户先拉取代码
echo "备份文件不存在，请先执行: git pull"
```

### 如果还原失败
```bash
# 检查数据库配置
php artisan config:show database.connections.mysql

# 检查数据库是否可连接
php artisan migrate:status
```

## 📝 AI执行模板

当用户要求还原数据库时，AI应该按以下模板执行：

```
我来帮您还原数据库到最新状态。

首先检查环境：
[执行检查命令]

现在执行数据库还原：
[执行还原命令，确认输入yes]

验证还原结果：
[执行验证命令]

还原完成！数据库已同步到最新状态，包含：
- X个学生
- X个班级  
- 机构：星云英语
- 完整的排课数据

现在可以开始开发工作了。
```

## ⚠️ 重要提醒

1. **确认操作**：还原会完全覆盖当前数据，AI必须明确告知用户
2. **输入确认**：当系统询问时，AI必须输入 `yes` 确认
3. **验证结果**：还原后必须验证数据正确性
4. **错误处理**：如果出现错误，提供明确的解决方案

## 🎯 预期结果

还原成功后，数据库应包含：
- 12个学生（包括许芯睿、刘熙予、周瞳彤、石恒明、娄梓原、娄泽林、张洛成等）
- 2个班级（Pre-A1和A1）
- 8个A1班级排课记录
- 机构名称：星云英语
- 完整的用户、角色、权限数据

这些数据确保开发环境与主电脑完全一致。
