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
        Schema::table('attendance_records', function (Blueprint $table) {
            // 添加统一的上课时间字段
            $table->datetime('lesson_time')
                  ->nullable()
                  ->after('lesson_content')
                  ->comment('上课时间（统一字段，无论排课还是手动点名都使用此字段）');
        });

        // 数据迁移：将现有数据的时间统一到lesson_time字段
        DB::statement("
            UPDATE attendance_records
            SET lesson_time = CASE
                WHEN record_type = 'manual' AND actual_lesson_time IS NOT NULL THEN actual_lesson_time
                WHEN record_type = 'scheduled' AND schedule_id IS NOT NULL THEN (
                    SELECT CONCAT(cs.schedule_date, ' ', ts.start_time)
                    FROM class_schedules cs
                    LEFT JOIN time_slots ts ON cs.time_slot_id = ts.id
                    WHERE cs.id = attendance_records.schedule_id
                )
                ELSE actual_lesson_time
            END
        ");

        // 设置lesson_time为非空（数据迁移完成后）
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->datetime('lesson_time')->nullable(false)->change();
        });

        // 添加索引
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->index(['student_id', 'lesson_time'], 'idx_student_lesson_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // 删除索引
            $table->dropIndex('idx_student_lesson_time');

            // 删除字段
            $table->dropColumn('lesson_time');
        });
    }
};
