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
        // 扩展现有users表 - 原来的完整设计
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->constrained()->onDelete('set null')->comment('所属机构');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null')->comment('所属部门');
            $table->string('employee_id', 50)->nullable()->comment('员工编号');
            $table->string('phone', 20)->nullable()->comment('手机号');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->comment('性别');
            $table->date('birth_date')->nullable()->comment('出生日期');
            $table->string('id_card', 20)->nullable()->comment('身份证号');
            $table->string('avatar')->nullable()->comment('头像');
            $table->text('bio')->nullable()->comment('个人简介');
            $table->json('qualifications')->nullable()->comment('资质证书');
            $table->json('specialties')->nullable()->comment('擅长科目');
            $table->boolean('can_teach')->default(false)->comment('是否可授课');
            $table->decimal('hourly_rate', 8, 2)->nullable()->comment('课时费');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])->nullable()->comment('雇佣类型');
            $table->date('hire_date')->nullable()->comment('入职日期');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');
            $table->timestamp('last_login_at')->nullable()->comment('最后登录时间');
            $table->softDeletes();

            $table->index(['institution_id', 'status']);
            $table->index(['department_id', 'can_teach']);
            $table->unique(['institution_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'institution_id', 'department_id', 'employee_id', 'phone', 'gender',
                'birth_date', 'id_card', 'avatar', 'bio', 'qualifications', 'specialties',
                'can_teach', 'hourly_rate', 'employment_type', 'hire_date', 'status',
                'last_login_at', 'deleted_at'
            ]);
        });
    }
};
