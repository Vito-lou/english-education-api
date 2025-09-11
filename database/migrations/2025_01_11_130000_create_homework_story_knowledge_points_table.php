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
        Schema::create('homework_story_knowledge_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('homework_assignment_id')->comment('作业分配ID');
            $table->unsignedBigInteger('knowledge_point_id')->comment('故事知识点ID');
            $table->timestamps();

            // 外键约束
            $table->foreign('homework_assignment_id', 'hskp_homework_assignment_id_foreign')
                  ->references('id')->on('homework_assignments')->onDelete('cascade');
            $table->foreign('knowledge_point_id', 'hskp_knowledge_point_id_foreign')
                  ->references('id')->on('knowledge_points')->onDelete('cascade');

            // 唯一约束，防止重复关联
            $table->unique(['homework_assignment_id', 'knowledge_point_id'], 'hskp_unique');

            // 索引
            $table->index('homework_assignment_id');
            $table->index('knowledge_point_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_story_knowledge_points');
    }
};
