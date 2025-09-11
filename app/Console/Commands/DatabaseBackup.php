<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--restore : 还原数据库}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '备份或还原数据库';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('restore')) {
            return $this->restoreDatabase();
        }

        return $this->backupDatabase();
    }

    /**
     * 备份数据库
     */
    private function backupDatabase()
    {
        $this->info('开始备份数据库...');

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $backupDir = database_path('backups');
        $timestamp = now()->format('Ymd_His');
        $backupFile = "{$backupDir}/backup_{$timestamp}.sql";
        $latestFile = "{$backupDir}/latest.sql";

        // 创建备份目录
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // 构建mysqldump命令
        $command = sprintf(
            'mysqldump -h%s -u%s %s %s > %s',
            $dbHost,
            $dbUser,
            $dbPassword ? "-p{$dbPassword}" : '',
            $dbName,
            $backupFile
        );

        // 执行备份
        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($backupFile)) {
            // 创建最新备份的软链接
            if (file_exists($latestFile)) {
                unlink($latestFile);
            }
            symlink(basename($backupFile), $latestFile);

            $size = $this->formatBytes(filesize($backupFile));

            $this->info("✅ 数据库备份成功！");
            $this->line("📁 备份文件: {$backupFile}");
            $this->line("🔗 最新备份: {$latestFile}");
            $this->line("📊 文件大小: {$size}");
            $this->line("");
            $this->comment("💡 提示：");
            $this->comment("1. 请将备份文件提交到Git: git add database/backups/latest.sql");
            $this->comment("2. 其他电脑可以使用: php artisan db:backup --restore 还原数据");

            return 0;
        } else {
            $this->error("❌ 数据库备份失败！");
            return 1;
        }
    }

    /**
     * 还原数据库
     */
    private function restoreDatabase()
    {
        $backupFile = database_path('backups/latest.sql');

        if (!file_exists($backupFile)) {
            $this->error("❌ 备份文件不存在: {$backupFile}");
            return 1;
        }

        $size = $this->formatBytes(filesize($backupFile));
        $modified = date('Y-m-d H:i:s', filemtime($backupFile));

        $this->info('开始还原数据库...');
        $this->line("📁 备份文件: {$backupFile}");
        $this->line("📊 文件大小: {$size}");
        $this->line("📅 修改时间: {$modified}");
        $this->line("");

        if (!$this->confirm('⚠️  警告：此操作将覆盖当前数据库数据，是否继续？', false)) {
            $this->comment('❌ 操作已取消');
            return 0;
        }

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPassword = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        // 构建mysql命令
        $command = sprintf(
            'mysql -h%s -u%s %s %s < %s',
            $dbHost,
            $dbUser,
            $dbPassword ? "-p{$dbPassword}" : '',
            $dbName,
            $backupFile
        );

        // 执行还原
        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            $this->info("✅ 数据库还原成功！");
            $this->comment("💡 数据库已还原到备份时的状态，可以开始开发工作了");
            return 0;
        } else {
            $this->error("❌ 数据库还原失败！");
            return 1;
        }
    }

    /**
     * 格式化文件大小
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }
}
