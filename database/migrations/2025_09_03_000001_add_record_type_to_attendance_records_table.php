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
        Schema::table('attendance_records', function (Blueprint $table) {
            // 添加记录类型字段
            $table->enum('record_type', ['scheduled', 'manual'])
                  ->default('scheduled')
                  ->after('id')
                  ->comment('记录类型：scheduled=计划内课程，manual=手动补录课程');
            
            // 添加手动补录相关字段
            $table->unsignedBigInteger('class_id')
                  ->nullable()
                  ->after('schedule_id')
                  ->comment('班级ID（手动补录时使用）');
            
            $table->unsignedBigInteger('lesson_id')
                  ->nullable()
                  ->after('class_id')
                  ->comment('课时ID（手动补录时选择的课程内容）');
            
            $table->datetime('actual_lesson_time')
                  ->nullable()
                  ->after('lesson_id')
                  ->comment('实际上课时间（手动补录时填写）');
            
            $table->text('lesson_content')
                  ->nullable()
                  ->after('actual_lesson_time')
                  ->comment('上课内容描述（手动补录时填写）');
            
            // 修改schedule_id为可空，因为手动补录时不需要排课
            $table->unsignedBigInteger('schedule_id')->nullable()->change();
            
            // 添加外键约束
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('set null');
            
            // 添加索引
            $table->index('record_type');
            $table->index(['class_id', 'actual_lesson_time']);
        });
        
        // 删除原有的唯一约束，因为手动补录时schedule_id可能为null
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropUnique('uk_schedule_student');
        });
        
        // 添加新的复合唯一约束
        Schema::table('attendance_records', function (Blueprint $table) {
            // 对于scheduled类型，schedule_id + student_id 必须唯一
            // 对于manual类型，class_id + student_id + actual_lesson_time 必须唯一
            $table->index(['record_type', 'schedule_id', 'student_id'], 'idx_scheduled_attendance');
            $table->index(['record_type', 'class_id', 'student_id', 'actual_lesson_time'], 'idx_manual_attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // 删除新增的索引
            $table->dropIndex('idx_scheduled_attendance');
            $table->dropIndex('idx_manual_attendance');
            
            // 删除外键约束
            $table->dropForeign(['class_id']);
            $table->dropForeign(['lesson_id']);
            
            // 删除新增字段
            $table->dropColumn([
                'record_type',
                'class_id', 
                'lesson_id',
                'actual_lesson_time',
                'lesson_content'
            ]);
            
            // 恢复schedule_id为非空
            $table->unsignedBigInteger('schedule_id')->nullable(false)->change();
        });
        
        // 恢复原有的唯一约束
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->unique(['schedule_id', 'student_id'], 'uk_schedule_student');
        });
    }
};
