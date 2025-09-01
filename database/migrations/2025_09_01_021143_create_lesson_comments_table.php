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
        Schema::create('lesson_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('class_schedules')->onDelete('cascade')->comment('关联排课');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('关联学员');
            $table->text('teacher_comment')->nullable()->comment('教师点评');
            $table->tinyInteger('performance_rating')->nullable()->comment('表现评分1-5');
            $table->enum('homework_completion', ['completed', 'partial', 'not_completed'])->nullable()->comment('作业完成情况');
            $table->tinyInteger('homework_quality_rating')->nullable()->comment('作业质量评分1-5');
            $table->timestamps();

            $table->index(['schedule_id']);
            $table->index(['student_id']);
            $table->unique(['schedule_id', 'student_id']); // 一个学员在一次课程中只能有一条点评
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_comments');
    }
};
