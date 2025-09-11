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
        Schema::table('course_units', function (Blueprint $table) {
            // 添加故事关联字段
            $table->unsignedBigInteger('story_id')->nullable()->after('learning_objectives')->comment('关联故事ID');
            $table->unsignedBigInteger('story_chapter_id')->nullable()->after('story_id')->comment('关联故事章节ID（如果故事有章节）');
            
            // 添加外键约束
            $table->foreign('story_id')->references('id')->on('stories')->onDelete('set null');
            $table->foreign('story_chapter_id')->references('id')->on('story_chapters')->onDelete('set null');
            
            // 添加索引
            $table->index(['story_id', 'story_chapter_id']);
            
            // 重命名story_content字段为story_content_deprecated，标记为废弃
            $table->renameColumn('story_content', 'story_content_deprecated');
        });
        
        // 更新字段注释
        Schema::table('course_units', function (Blueprint $table) {
            $table->text('story_content_deprecated')->nullable()->comment('已废弃：原故事内容字段，请使用story_id关联')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_units', function (Blueprint $table) {
            // 删除外键约束
            $table->dropForeign(['story_id']);
            $table->dropForeign(['story_chapter_id']);
            
            // 删除索引
            $table->dropIndex(['story_id', 'story_chapter_id']);
            
            // 删除新增字段
            $table->dropColumn(['story_id', 'story_chapter_id']);
            
            // 恢复原字段名
            $table->renameColumn('story_content_deprecated', 'story_content');
        });
        
        // 恢复原字段注释
        Schema::table('course_units', function (Blueprint $table) {
            $table->text('story_content')->nullable()->comment('故事内容')->change();
        });
    }
};
