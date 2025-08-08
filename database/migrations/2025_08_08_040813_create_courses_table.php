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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id')->comment('科目ID');
            $table->string('name')->comment('课程名称');
            $table->string('code')->comment('课程代码');
            $table->text('description')->nullable()->comment('课程描述');
            $table->enum('teaching_method', ['yuandian', 'standard', 'speed_memory'])->default('yuandian')->comment('教学方法');
            $table->boolean('has_levels')->default(true)->comment('是否有级别体系');
            $table->unsignedBigInteger('institution_id')->nullable()->comment('机构ID');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->index(['subject_id', 'status']);
            $table->index(['institution_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
