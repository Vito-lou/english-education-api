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
        // 角色表 - 原来的完整设计
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->onDelete('cascade')->comment('所属机构');
            $table->string('name', 50)->comment('角色名称');
            $table->string('code', 50)->comment('角色代码');
            $table->text('description')->nullable()->comment('角色描述');
            $table->enum('type', ['system', 'custom'])->default('custom')->comment('类型：系统预设/自定义');
            $table->json('permissions')->nullable()->comment('功能权限');
            $table->json('data_scope')->nullable()->comment('数据权限范围');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
