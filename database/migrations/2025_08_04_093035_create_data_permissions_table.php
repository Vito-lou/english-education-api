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
        Schema::create('data_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('数据权限名称');
            $table->string('code', 100)->unique()->comment('数据权限代码');
            $table->string('resource_type', 50)->comment('资源类型（student, class, schedule等）');
            $table->enum('scope_type', ['all', 'partial'])->comment('权限范围：全部/部分');
            $table->text('description')->nullable()->comment('权限描述');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();

            $table->index('resource_type');
            $table->index('scope_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_permissions');
    }
};
