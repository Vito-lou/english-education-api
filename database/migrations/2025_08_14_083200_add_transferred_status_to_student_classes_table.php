<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 修改student_classes表的status字段，添加transferred状态
        DB::statement("ALTER TABLE student_classes MODIFY COLUMN status ENUM('active', 'graduated', 'dropped', 'transferred') DEFAULT 'active' COMMENT '学员状态'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 回滚时移除transferred状态
        DB::statement("ALTER TABLE student_classes MODIFY COLUMN status ENUM('active', 'graduated', 'dropped') DEFAULT 'active' COMMENT '学员状态'");
    }
};
