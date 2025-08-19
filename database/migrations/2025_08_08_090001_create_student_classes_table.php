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
        Schema::create('student_classes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->comment('学员ID');
            $table->unsignedBigInteger('class_id')->comment('班级ID');
            $table->date('enrollment_date')->comment('入班日期');
            $table->enum('status', ['active', 'graduated', 'dropped', 'transferred'])->default('active')->comment('学员状态');
            $table->timestamps();

            // 外键约束
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');

            // 唯一约束：一个学员在同一个班级只能有一条有效记录
            $table->unique(['student_id', 'class_id']);

            // 索引
            $table->index(['class_id', 'status']);
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_classes');
    }
};
