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
        Schema::table('roles', function (Blueprint $table) {
            // 修改现有字段
            $table->string('name', 100)->change()->comment('角色名称');
            $table->text('description')->nullable()->change()->comment('角色描述');

            // 删除旧字段
            $table->dropColumn(['type', 'permissions', 'data_scope', 'deleted_at']);

            // 添加新字段
            $table->boolean('is_system')->default(false)->after('description')->comment('是否系统角色');

            // 修改外键约束，允许 null（系统角色）
            $table->dropForeign(['institution_id']);
            $table->foreignId('institution_id')->nullable()->change()->constrained()->onDelete('cascade')->comment('所属机构ID（系统角色为null）');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // 恢复旧字段
            $table->enum('type', ['system', 'custom'])->default('custom')->after('description')->comment('类型：系统预设/自定义');
            $table->json('permissions')->nullable()->after('type')->comment('功能权限');
            $table->json('data_scope')->nullable()->after('permissions')->comment('数据权限范围');
            $table->softDeletes();

            // 删除新字段
            $table->dropColumn('is_system');

            // 恢复字段长度
            $table->string('name', 50)->change();
        });
    }
};
