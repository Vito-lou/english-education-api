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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('学员姓名');
            $table->string('phone', 20)->nullable()->comment('学员手机号');
            $table->enum('gender', ['male', 'female'])->nullable()->comment('性别');
            $table->date('birth_date')->nullable()->comment('出生日期');

            // 家长信息
            $table->string('parent_name')->comment('家长姓名');
            $table->string('parent_phone', 20)->comment('家长手机号');
            $table->enum('parent_relationship', ['father', 'mother', 'guardian', 'other'])->default('mother')->comment('家长关系');

            // 学员状态
            $table->enum('student_type', ['potential', 'trial', 'enrolled', 'graduated', 'suspended'])->default('potential')->comment('学员类型');
            $table->enum('follow_up_status', ['new', 'contacted', 'interested', 'not_interested', 'follow_up'])->default('new')->comment('跟进状态');
            $table->enum('intention_level', ['high', 'medium', 'low'])->default('medium')->comment('意向等级');

            // 关联信息
            $table->unsignedBigInteger('user_id')->nullable()->comment('关联用户账号ID');
            $table->unsignedBigInteger('institution_id')->comment('所属机构ID');

            // 其他信息
            $table->string('source')->nullable()->comment('来源渠道');
            $table->text('remarks')->nullable()->comment('备注信息');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('状态');

            $table->timestamps();
            $table->softDeletes();

            // 外键约束
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');

            // 索引
            $table->index(['institution_id', 'student_type', 'status']);
            $table->index(['parent_phone']);
            $table->index(['follow_up_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
