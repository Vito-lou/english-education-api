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
        Schema::create('role_data_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade')->comment('角色ID');
            $table->foreignId('data_permission_id')->constrained()->onDelete('cascade')->comment('数据权限ID');
            $table->timestamps();

            $table->unique(['role_id', 'data_permission_id'], 'uk_role_data_permission');
            $table->index('role_id');
            $table->index('data_permission_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_data_permissions');
    }
};
