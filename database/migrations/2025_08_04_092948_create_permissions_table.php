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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('权限名称');
            $table->string('code', 100)->unique()->comment('权限代码');
            $table->enum('type', ['menu', 'button', 'api'])->comment('权限类型');
            $table->foreignId('parent_id')->nullable()->constrained('permissions')->comment('父权限ID');
            $table->string('resource', 100)->nullable()->comment('资源标识');
            $table->string('action', 50)->nullable()->comment('操作标识');
            $table->text('description')->nullable()->comment('权限描述');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();

            $table->index('parent_id');
            $table->index('type');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
