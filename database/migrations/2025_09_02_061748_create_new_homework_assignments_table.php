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
        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('作业标题');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade')->comment('关联班级');
            $table->datetime('due_date')->comment('截止时间');
            $table->text('requirements')->comment('作业要求');
            $table->json('attachments')->nullable()->comment('附件信息(图片/视频)');
            $table->enum('status', ['active', 'expired', 'draft'])->default('active')->comment('作业状态');
            $table->foreignId('created_by')->constrained('users')->comment('布置教师');
            $table->foreignId('institution_id')->constrained('institutions')->comment('所属机构');
            $table->timestamps();

            // 索引
            $table->index(['class_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index(['institution_id']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_assignments');
    }
};
