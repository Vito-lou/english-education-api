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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id')->comment('课程单元ID');
            $table->string('name')->comment('课时名称');
            $table->longText('content')->nullable()->comment('课时内容');
            $table->integer('duration')->nullable()->comment('课时时长(分钟)');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();

            $table->foreign('unit_id')->references('id')->on('course_units')->onDelete('cascade');
            $table->index(['unit_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
