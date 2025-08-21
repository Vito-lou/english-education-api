<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEnrollment extends Model
{
    use SoftDeletes;

    protected $table = 'student_enrollments';


    protected $appends = [
        'status_name',
        'payment_status_name',
        'discount_type_name',
        'lesson_progress',
    ];

    protected $fillable = [
        'student_id',
        'institution_id',
        'campus_id',
        'course_id',
        'level_id',
        'enrollment_date',
        'start_date',
        'end_date',
        'total_lessons',
        'used_lessons',
        'remaining_lessons',
        'status',
        'enrollment_fee',
        'price_per_lesson',
        'discount_type',
        'discount_value',
        'actual_amount',
        'paid_amount',
        'payment_status',
        'sales_person_id',
        'remarks',
        'refund_amount',
        'refund_reason',
        'refunded_at',
        'refund_processed_by',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'enrollment_fee' => 'decimal:2',
        'price_per_lesson' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'total_lessons' => 'integer',
        'used_lessons' => 'integer',
        'remaining_lessons' => 'integer',
        'refund_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    /**
     * 学员关系
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 机构关系
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * 校区关系
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'campus_id');
    }

    /**
     * 课程关系
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * 级别关系
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'level_id');
    }

    /**
     * 销售人员关系
     */
    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }


    /**
     * 获取状态显示名称
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'pending' => '待确认',
            'active' => '进行中',
            'suspended' => '暂停',
            'completed' => '已完成',
            'cancelled' => '已取消',
            default => '未知',
        };
    }

    /**
     * 获取付款状态显示名称
     */
    public function getPaymentStatusNameAttribute(): string
    {
        return match($this->payment_status) {
            'unpaid' => '未付款',
            'partial' => '部分付款',
            'paid' => '已付款',
            'refunded' => '已退款',
            default => '未知',
        };
    }

    /**
     * 获取折扣类型显示名称
     */
    public function getDiscountTypeNameAttribute(): string
    {
        return match($this->discount_type) {
            'none' => '无折扣',
            'percentage' => '百分比折扣',
            'amount' => '金额优惠',
            default => '未知',
        };
    }

    /**
     * 计算课时进度百分比
     */
    public function getLessonProgressAttribute(): float
    {
        if ($this->total_lessons <= 0) {
            return 0;
        }
        return round(($this->used_lessons / $this->total_lessons) * 100, 2);
    }

    /**
     * 检查是否可以上课（有剩余课时）
     */
    public function canAttendLesson(): bool
    {
        return $this->remaining_lessons > 0 && $this->status === 'active';
    }

    /**
     * 扣减课时
     */
    public function deductLesson(): bool
    {
        if (!$this->canAttendLesson()) {
            return false;
        }

        $this->used_lessons += 1;
        $this->remaining_lessons -= 1;

        // 如果课时用完，更新状态为已完成
        if ($this->remaining_lessons <= 0) {
            $this->status = 'completed';
        }

        return $this->save();
    }
}
