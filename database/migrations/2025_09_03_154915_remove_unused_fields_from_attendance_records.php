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
            // 删除不再使用的字段
            $table->dropColumn([
                'actual_lesson_time',  // 已被lesson_time替代
                'check_in_time',       // 签到时间，暂时不需要
                'makeup_required',     // 补课相关字段，暂时不需要
                'makeup_scheduled',    // 补课相关字段，暂时不需要
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            // 恢复删除的字段
            $table->datetime('actual_lesson_time')->nullable()->comment('实际上课时间（手动点名时使用）');
            $table->datetime('check_in_time')->nullable()->comment('学生签到时间');
            $table->boolean('makeup_required')->default(false)->comment('是否需要补课');
            $table->boolean('makeup_scheduled')->default(false)->comment('是否已安排补课');
        });
    }
};
