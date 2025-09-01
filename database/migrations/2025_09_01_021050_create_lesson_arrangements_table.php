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
        Schema::create('lesson_arrangements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('class_schedules')->onDelete('cascade')->comment('关联排课');
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade')->comment('关联课时');
            $table->text('teaching_focus')->nullable()->comment('教学重点');
            $table->foreignId('created_by')->constrained('users')->comment('创建者');
            $table->timestamps();

            $table->index(['schedule_id']);
            $table->index(['lesson_id']);
            $table->unique(['schedule_id']); // 一个排课只能对应一个课程安排
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_arrangements');
    }
};
