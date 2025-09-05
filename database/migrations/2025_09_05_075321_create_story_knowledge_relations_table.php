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
        Schema::create('story_knowledge_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained('stories')->onDelete('cascade')->comment('关联故事ID');
            $table->foreignId('knowledge_point_id')->constrained('knowledge_points')->onDelete('cascade')->comment('关联知识点ID');
            $table->timestamps();

            // 唯一约束和索引
            $table->unique(['story_id', 'knowledge_point_id'], 'unique_story_knowledge');
            $table->index(['story_id']);
            $table->index(['knowledge_point_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_knowledge_relations');
    }
};
