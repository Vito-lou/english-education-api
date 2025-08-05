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
        Schema::create('system_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('菜单名称');
            $table->string('code', 100)->unique()->comment('菜单代码');
            $table->string('path', 200)->nullable()->comment('路由路径');
            $table->string('icon', 50)->nullable()->comment('图标');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('父菜单ID');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->text('description')->nullable()->comment('描述');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('system_menus')->onDelete('cascade');
            $table->index(['parent_id', 'sort_order']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_menus');
    }
};
