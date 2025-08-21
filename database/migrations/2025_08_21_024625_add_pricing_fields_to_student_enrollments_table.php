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
        Schema::table('student_enrollments', function (Blueprint $table) {
            // 添加课时单价
            $table->decimal('price_per_lesson', 8, 2)->after('enrollment_fee')->comment('课时单价');

            // 添加折扣相关字段
            $table->enum('discount_type', ['none', 'percentage', 'amount'])->default('none')->after('price_per_lesson')->comment('折扣类型');
            $table->decimal('discount_value', 8, 2)->default(0)->after('discount_type')->comment('折扣值');

            // 添加实际收费金额（替代原来的enrollment_fee）
            $table->decimal('actual_amount', 10, 2)->after('discount_value')->comment('实际收费金额');

            // 添加剩余课时字段
            $table->integer('remaining_lessons')->after('used_lessons')->comment('剩余课时数');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'price_per_lesson',
                'discount_type',
                'discount_value',
                'actual_amount',
                'remaining_lessons'
            ]);
        });
    }
};
