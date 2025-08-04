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
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('用户ID');
            $table->foreignId('role_id')->constrained()->onDelete('cascade')->comment('角色ID');
            $table->foreignId('granted_by')->nullable()->constrained('users')->comment('分配人ID');
            $table->timestamp('granted_at')->useCurrent()->comment('分配时间');
            $table->timestamps();

            $table->unique(['user_id', 'role_id'], 'uk_user_role');
            $table->index('user_id');
            $table->index('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
