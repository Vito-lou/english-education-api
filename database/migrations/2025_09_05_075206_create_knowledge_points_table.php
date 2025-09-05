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
        Schema::create('knowledge_points', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('知识点名称');
            $table->enum('type', ['vocabulary', 'grammar', 'phrase', 'sentence_pattern'])->comment('类型');
            $table->text('definition_en')->nullable()->comment('英文释义');
            $table->text('definition_cn')->nullable()->comment('中文释义');
            $table->text('explanation')->nullable()->comment('详细用法解释');
            $table->text('example_sentence')->nullable()->comment('示例句');
            $table->string('audio_url')->nullable()->comment('发音音频链接');
            $table->timestamps();

            // 索引
            $table->index(['type']);
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_points');
    }
};
