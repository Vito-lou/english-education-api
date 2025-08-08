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
        Schema::create('course_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('course_id')->comment('课程ID');
            $table->unsignedBigInteger('level_id')->nullable()->comment('级别ID，可为null支持无级别课程');
            $table->string('name')->comment('单元名称');
            $table->text('description')->nullable()->comment('单元描述');
            $table->text('learning_objectives')->nullable()->comment('学习目标');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('level_id')->references('id')->on('course_levels')->onDelete('cascade');
            $table->index(['course_id', 'level_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_units');
    }
};
