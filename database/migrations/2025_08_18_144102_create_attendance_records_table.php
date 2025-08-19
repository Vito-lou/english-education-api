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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id')->comment('课程安排ID');
            $table->unsignedBigInteger('student_id')->comment('学员ID');
            $table->enum('attendance_status', ['present', 'absent', 'late', 'leave_early', 'sick_leave', 'personal_leave'])->default('present')->comment('出勤状态');
            $table->timestamp('check_in_time')->nullable()->comment('签到时间');
            $table->string('absence_reason', 200)->nullable()->comment('缺席原因');
            $table->boolean('makeup_required')->default(false)->comment('是否需要补课');
            $table->boolean('makeup_scheduled')->default(false)->comment('是否已安排补课');
            $table->text('teacher_notes')->nullable()->comment('教师备注');
            $table->unsignedBigInteger('recorded_by')->comment('记录人ID');
            $table->timestamp('recorded_at')->useCurrent()->comment('记录时间');
            $table->timestamps();

            // 外键约束
            $table->foreign('schedule_id')->references('id')->on('class_schedules')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');

            // 索引
            $table->index('schedule_id');
            $table->index(['student_id', 'recorded_at']);

            // 唯一约束：同一课程安排中每个学员只能有一条考勤记录
            $table->unique(['schedule_id', 'student_id'], 'uk_schedule_student');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
