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
        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->comment('学员ID');
            $table->foreignId('institution_id')->constrained('institutions')->comment('机构ID');
            $table->foreignId('campus_id')->constrained('departments')->comment('校区ID');
            $table->foreignId('course_id')->constrained('courses')->comment('课程ID');
            $table->foreignId('level_id')->nullable()->constrained('course_levels')->comment('级别ID');
            $table->date('enrollment_date')->comment('报名日期');
            $table->date('start_date')->nullable()->comment('开课日期');
            $table->date('end_date')->nullable()->comment('结课日期');
            $table->integer('total_lessons')->comment('总课时数');
            $table->integer('used_lessons')->default(0)->comment('已用课时数');
            $table->enum('status', ['pending', 'active', 'suspended', 'completed', 'cancelled'])->default('pending')->comment('报名状态');
            $table->decimal('enrollment_fee', 10, 2)->comment('报名费用');
            $table->decimal('paid_amount', 10, 2)->default(0)->comment('已付金额');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])->default('unpaid')->comment('付款状态');
            $table->foreignId('sales_person_id')->nullable()->constrained('users')->comment('销售人员ID');
            $table->text('remarks')->nullable()->comment('备注信息');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['student_id', 'status'], 'idx_student_enrollment');
            $table->index(['institution_id', 'status'], 'idx_institution_enrollment');
            $table->index(['campus_id', 'enrollment_date'], 'idx_campus_enrollment');
            $table->index(['course_id', 'level_id'], 'idx_course_enrollment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_enrollments');
    }
};
