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
        Schema::create('enrollment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 32)->unique()->comment('订单号');
            $table->foreignId('enrollment_id')->constrained('student_enrollments')->comment('报名ID');
            $table->foreignId('student_id')->constrained('students')->comment('学员ID');
            $table->foreignId('institution_id')->constrained('institutions')->comment('机构ID');
            $table->date('order_date')->comment('订单日期');
            $table->decimal('total_amount', 10, 2)->comment('订单总金额');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('优惠金额');
            $table->decimal('final_amount', 10, 2)->comment('最终金额');
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'alipay', 'wechat'])->nullable()->comment('支付方式');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'cancelled', 'refunded'])->default('pending')->comment('支付状态');
            $table->boolean('contract_signed')->default(false)->comment('合同是否签署');
            $table->boolean('invoice_issued')->default(false)->comment('发票是否开具');
            $table->foreignId('created_by')->constrained('users')->comment('创建人ID');
            $table->timestamps();

            $table->index('order_number', 'idx_order_number');
            $table->index(['student_id', 'order_date'], 'idx_student_order');
            $table->index(['institution_id', 'payment_status'], 'idx_institution_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_orders');
    }
};
