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
            $table->decimal('refund_amount', 10, 2)->nullable()->after('paid_amount')->comment('退款金额');
            $table->string('refund_reason')->nullable()->after('refund_amount')->comment('退款原因');
            $table->timestamp('refunded_at')->nullable()->after('refund_reason')->comment('退款时间');
            $table->foreignId('refund_processed_by')->nullable()->constrained('users')->after('refunded_at')->comment('退款操作人ID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropForeign(['refund_processed_by']);
            $table->dropColumn(['refund_amount', 'refund_reason', 'refunded_at', 'refund_processed_by']);
        });
    }
};
