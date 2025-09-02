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
        Schema::table('unit_knowledge_points', function (Blueprint $table) {
            $table->dropColumn(['pronunciation', 'audio_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_knowledge_points', function (Blueprint $table) {
            $table->string('pronunciation')->nullable()->comment('发音（音标或拼音）');
            $table->string('audio_url')->nullable()->comment('音频文件URL');
        });
    }
};
