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
        Schema::table('course_units', function (Blueprint $table) {
            $table->longText('story_content')->nullable()->after('learning_objectives')->comment('单元故事文本内容');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_units', function (Blueprint $table) {
            $table->dropColumn('story_content');
        });
    }
};
