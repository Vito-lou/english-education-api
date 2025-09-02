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
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->foreignId('lesson_id')->nullable()->after('course_id')->constrained('lessons')->onDelete('set null')->comment('关联课时');
            $table->text('teaching_focus')->nullable()->after('lesson_content')->comment('教学重点');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_schedules', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            $table->dropColumn(['lesson_id', 'teaching_focus']);
        });
    }
};
