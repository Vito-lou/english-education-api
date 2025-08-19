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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institution_id')->comment('机构ID');
            $table->string('name', 50)->comment('时间段名称');
            $table->time('start_time')->comment('开始时间');
            $table->time('end_time')->comment('结束时间');
            $table->integer('duration_minutes')->comment('时长(分钟)');
            $table->boolean('is_active')->default(true)->comment('是否启用');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->timestamps();

            // 外键约束
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');

            // 索引
            $table->index(['institution_id', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
