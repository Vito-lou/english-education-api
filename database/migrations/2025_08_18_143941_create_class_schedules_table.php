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
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id')->comment('班级ID');
            $table->unsignedBigInteger('course_id')->comment('课程ID');
            $table->unsignedBigInteger('teacher_id')->comment('授课教师ID');
            $table->unsignedBigInteger('time_slot_id')->comment('时间段ID');
            $table->date('schedule_date')->comment('上课日期');
            $table->string('lesson_content', 100)->nullable()->comment('上课内容说明(最多20字)');
            $table->string('classroom', 50)->nullable()->comment('教室');
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'rescheduled'])->default('scheduled')->comment('状态');
            $table->unsignedBigInteger('created_by')->comment('创建人ID');
            $table->timestamps();

            // 外键约束
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('time_slot_id')->references('id')->on('time_slots')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // 索引
            $table->index(['class_id', 'schedule_date']);
            $table->index(['teacher_id', 'schedule_date']);
            $table->index(['schedule_date', 'status']);

            // 唯一约束：同一班级在同一日期同一时间段只能有一个排课
            $table->unique(['class_id', 'schedule_date', 'time_slot_id'], 'uk_class_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_schedules');
    }
};
