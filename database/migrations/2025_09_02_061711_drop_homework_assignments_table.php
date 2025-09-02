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
        Schema::dropIfExists('homework_assignments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 恢复原来的homework_assignments表结构
        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arrangement_id')->constrained('lesson_arrangements')->onDelete('cascade')->comment('关联课程安排');
            $table->string('title')->comment('作业标题');
            $table->text('content')->comment('作业内容');
            $table->date('due_date')->comment('截止日期');
            $table->foreignId('created_by')->constrained('users')->comment('布置教师');
            $table->timestamps();

            $table->index(['arrangement_id']);
            $table->index(['due_date']);
        });
    }
};
