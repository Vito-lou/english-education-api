<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. 创建知识点例句表
        Schema::create('knowledge_point_examples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_point_id')->constrained('knowledge_points')->onDelete('cascade')->comment('关联知识点ID');
            $table->text('example_en')->comment('英文例句');
            $table->text('example_cn')->nullable()->comment('中文翻译或解释');
            $table->integer('sequence')->default(0)->comment('显示顺序');
            $table->timestamps();

            // 索引
            $table->index(['knowledge_point_id', 'sequence']);
        });

        // 2. 迁移现有的例句数据到新表
        $knowledgePoints = DB::table('knowledge_points')
            ->whereNotNull('example_sentence')
            ->where('example_sentence', '!=', '')
            ->get();

        foreach ($knowledgePoints as $point) {
            DB::table('knowledge_point_examples')->insert([
                'knowledge_point_id' => $point->id,
                'example_en' => $point->example_sentence,
                'example_cn' => null, // 旧数据没有中文翻译
                'sequence' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. 删除knowledge_points表中的冗余字段
        Schema::table('knowledge_points', function (Blueprint $table) {
            $table->dropColumn(['audio_url', 'example_sentence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. 恢复knowledge_points表的字段
        Schema::table('knowledge_points', function (Blueprint $table) {
            $table->text('example_sentence')->nullable()->comment('示例句');
            $table->string('audio_url')->nullable()->comment('发音音频链接');
        });

        // 2. 将例句数据迁移回knowledge_points表
        $examples = DB::table('knowledge_point_examples')
            ->orderBy('knowledge_point_id')
            ->orderBy('sequence')
            ->get();

        foreach ($examples->groupBy('knowledge_point_id') as $pointId => $pointExamples) {
            $firstExample = $pointExamples->first();
            DB::table('knowledge_points')
                ->where('id', $pointId)
                ->update(['example_sentence' => $firstExample->example_en]);
        }

        // 3. 删除例句表
        Schema::dropIfExists('knowledge_point_examples');
    }
};
