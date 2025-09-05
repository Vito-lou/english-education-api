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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('故事标题');
            $table->text('description')->nullable()->comment('故事简介');
            $table->string('author', 100)->nullable()->comment('作者');
            $table->string('difficulty_level', 50)->nullable()->comment('难度等级');
            $table->string('cover_image_url')->nullable()->comment('封面图链接');
            $table->boolean('has_chapters')->default(false)->comment('是否分章节');
            $table->longText('content')->nullable()->comment('故事全文（仅当has_chapters=false时有效）');
            $table->timestamps();

            // 索引
            $table->index(['has_chapters']);
            $table->index(['difficulty_level']);
            $table->index(['title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
