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
        // 机构表 - 对应原系统的school概念，但更加完善
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('机构名称');
            $table->string('code', 50)->unique()->comment('机构代码');
            $table->string('logo')->nullable()->comment('机构Logo');
            $table->text('description')->nullable()->comment('机构介绍');
            $table->string('contact_person', 50)->nullable()->comment('联系人');
            $table->string('contact_phone', 20)->nullable()->comment('联系电话');
            $table->string('contact_email', 100)->nullable()->comment('联系邮箱');
            $table->string('address')->nullable()->comment('机构地址');
            $table->string('business_license')->nullable()->comment('营业执照');
            $table->json('business_hours')->nullable()->comment('营业时间');
            $table->json('settings')->nullable()->comment('机构配置');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->comment('状态');
            $table->timestamp('established_at')->nullable()->comment('成立时间');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'deleted_at']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
