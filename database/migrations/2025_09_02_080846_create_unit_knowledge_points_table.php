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
        Schema::create('unit_knowledge_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('course_units')->onDelete('cascade')->comment('关联单元');
            $table->enum('type', ['vocabulary', 'sentence_pattern', 'grammar'])->comment('知识点类型：词汇/句型/语法');
            $table->string('content')->comment('知识点内容');
            $table->string('pronunciation')->nullable()->comment('发音（音标或拼音）');
            $table->string('audio_url')->nullable()->comment('音频文件URL');
            $table->string('image_url')->nullable()->comment('配图URL');
            $table->text('explanation')->nullable()->comment('解释说明');
            $table->text('example_sentences')->nullable()->comment('例句（JSON格式）');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();

            // 索引
            $table->index(['unit_id', 'type']);
            $table->index(['unit_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_knowledge_points');
    }
};
