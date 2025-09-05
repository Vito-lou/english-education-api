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
        Schema::create('knowledge_point_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_point_id')->constrained('knowledge_points')->onDelete('cascade')->comment('关联知识点ID');
            $table->foreignId('knowledge_tag_id')->constrained('knowledge_tags')->onDelete('cascade')->comment('关联标签ID');
            $table->timestamps();

            // 唯一约束和索引
            $table->unique(['knowledge_point_id', 'knowledge_tag_id'], 'unique_point_tag');
            $table->index(['knowledge_point_id']);
            $table->index(['knowledge_tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_point_tags');
    }
};
