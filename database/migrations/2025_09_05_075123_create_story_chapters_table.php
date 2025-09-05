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
        Schema::create('story_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('stories')->onDelete('cascade')->comment('关联故事ID');
            $table->integer('chapter_number')->comment('章节序号');
            $table->string('chapter_title')->comment('章节标题');
            $table->longText('content')->comment('章节完整文本内容');
            $table->integer('word_count')->nullable()->comment('本章词数');
            $table->timestamps();

            // 外键约束和索引
            $table->unique(['story_id', 'chapter_number'], 'unique_story_chapter');
            $table->index(['story_id', 'chapter_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_chapters');
    }
};
