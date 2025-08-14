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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('班级名称');
            $table->unsignedBigInteger('campus_id')->comment('所属校区');
            $table->unsignedBigInteger('course_id')->comment('关联课程');
            $table->unsignedBigInteger('level_id')->nullable()->comment('课程级别');
            $table->integer('max_students')->default(20)->comment('班级容量');
            $table->unsignedBigInteger('teacher_id')->comment('授课老师');
            $table->integer('total_lessons')->default(0)->comment('授课课时');
            $table->enum('status', ['active', 'graduated'])->default('active')->comment('班级状态');
            $table->date('start_date')->comment('开班日期');
            $table->date('end_date')->nullable()->comment('结业日期');
            $table->text('remarks')->nullable()->comment('备注');
            $table->unsignedBigInteger('institution_id')->comment('所属机构');
            $table->timestamps();
            $table->softDeletes();

            // 外键约束
            $table->foreign('campus_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('level_id')->references('id')->on('course_levels')->onDelete('set null');
            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');

            // 索引
            $table->index(['institution_id', 'status']);
            $table->index(['campus_id', 'status']);
            $table->index(['course_id', 'level_id']);
            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
