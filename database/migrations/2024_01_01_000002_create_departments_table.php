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
        // 部门表 - 改进原系统的structures表
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->onDelete('cascade')->comment('所属机构');
            $table->foreignId('parent_id')->nullable()->constrained('departments')->onDelete('cascade')->comment('上级部门');
            $table->string('name', 100)->comment('部门名称');
            $table->string('code', 50)->comment('部门代码');
            $table->enum('type', ['campus', 'department', 'classroom'])->comment('类型：校区/部门/教室');
            $table->text('description')->nullable()->comment('部门描述');
            $table->string('manager_name', 50)->nullable()->comment('负责人姓名');
            $table->string('manager_phone', 20)->nullable()->comment('负责人电话');
            $table->string('address')->nullable()->comment('地址');
            $table->integer('capacity')->nullable()->comment('容量（教室用）');
            $table->json('facilities')->nullable()->comment('设施设备');
            $table->integer('sort_order')->default(0)->comment('排序');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['institution_id', 'code']);
            $table->index(['institution_id', 'type', 'status']);
            $table->index(['parent_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
