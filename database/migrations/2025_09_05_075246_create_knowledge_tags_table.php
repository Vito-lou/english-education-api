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
        Schema::create('knowledge_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag_name', 100)->comment('标签名称');
            $table->enum('tag_system', ['k12', 'cambridge', 'ielts', 'toefl'])->comment('标签体系');
            $table->text('description')->nullable()->comment('标签描述');
            $table->json('meta')->nullable()->comment('扩展元数据');
            $table->timestamps();

            // 索引
            $table->index(['tag_system']);
            $table->index(['tag_name']);
            $table->unique(['tag_name', 'tag_system'], 'unique_tag_system');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_tags');
    }
};
