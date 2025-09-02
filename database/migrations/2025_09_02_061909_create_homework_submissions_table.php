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
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_assignment_id')->constrained('homework_assignments')->onDelete('cascade')->comment('关联作业');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('提交学生');
            $table->text('content')->nullable()->comment('提交内容');
            $table->json('attachments')->nullable()->comment('提交附件');
            $table->enum('status', ['submitted', 'late', 'graded'])->default('submitted')->comment('提交状态');
            $table->decimal('score', 5, 2)->nullable()->comment('得分');
            $table->decimal('max_score', 5, 2)->default(100)->comment('满分');
            $table->text('teacher_feedback')->nullable()->comment('教师反馈');
            $table->timestamp('submitted_at')->comment('提交时间');
            $table->timestamp('graded_at')->nullable()->comment('批改时间');
            $table->foreignId('graded_by')->nullable()->constrained('users')->comment('批改教师');
            $table->timestamps();

            // 索引
            $table->index(['homework_assignment_id', 'status']);
            $table->index(['student_id']);
            $table->index(['submitted_at']);
            $table->unique(['homework_assignment_id', 'student_id']); // 一个学生对一个作业只能提交一次
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
    }
};
