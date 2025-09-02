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
        Schema::create('homework_knowledge_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_assignment_id')->constrained('homework_assignments')->onDelete('cascade')->comment('关联作业');
            $table->foreignId('knowledge_point_id')->constrained('unit_knowledge_points')->onDelete('cascade')->comment('关联知识点');
            $table->timestamps();

            // 唯一约束：一个作业中同一个知识点只能出现一次
            $table->unique(['homework_assignment_id', 'knowledge_point_id'], 'homework_knowledge_unique');

            // 索引
            $table->index(['homework_assignment_id']);
            $table->index(['knowledge_point_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_knowledge_points');
    }
};
