<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 先删除外键约束，然后删除表
        Schema::table('homework_assignments', function (Blueprint $table) {
            $table->dropForeign(['arrangement_id']);
        });

        // 删除 lesson_arrangements 表
        Schema::dropIfExists('lesson_arrangements');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 如果需要回滚，重新创建表（这里只是占位，实际不建议回滚）
        // 因为数据会丢失，建议通过备份恢复
    }
};
