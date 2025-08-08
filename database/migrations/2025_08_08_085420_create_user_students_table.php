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
        Schema::create('user_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('用户ID');
            $table->unsignedBigInteger('student_id')->comment('学员ID');
            $table->enum('relationship', ['parent', 'father', 'mother', 'guardian', 'other'])->default('parent')->comment('关系类型');
            $table->timestamps();

            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');

            // 唯一约束：同一用户和学员只能有一种关系
            $table->unique(['user_id', 'student_id']);

            // 索引
            $table->index(['user_id']);
            $table->index(['student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_students');
    }
};
