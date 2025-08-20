<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassSchedule extends Model
{
    protected $fillable = [
        'class_id',
        'course_id',
        'teacher_id',
        'time_slot_id',
        'schedule_date',
        'lesson_content',
        'classroom',
        'status',
        'created_by',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'class_id' => 'integer',
        'course_id' => 'integer',
        'teacher_id' => 'integer',
        'time_slot_id' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * 状态常量
     */
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RESCHEDULED = 'rescheduled';

    /**
     * 所属班级
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * 所属课程
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * 授课教师
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * 时间段
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * 创建人
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 考勤记录 - 暂未实现
     * TODO: 将来实现考勤功能时启用
     */
    // public function attendanceRecords(): HasMany
    // {
    //     return $this->hasMany(AttendanceRecord::class, 'schedule_id');
    // }

    /**
     * 实际上课记录 - 暂未实现
     * TODO: 将来实现上课记录功能时启用
     */
    // public function actualLessonRecord(): HasOne
    // {
    //     return $this->hasOne(ActualLessonRecord::class, 'schedule_id');
    // }

    /**
     * 获取状态中文名称
     */
    public function getStatusNameAttribute(): string
    {
        $statuses = [
            self::STATUS_SCHEDULED => '已排课',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_CANCELLED => '已取消',
            self::STATUS_RESCHEDULED => '已调课',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * 获取完整的时间信息
     */
    public function getFullTimeInfoAttribute(): string
    {
        return $this->schedule_date->format('Y-m-d') . ' ' . $this->timeSlot->time_range;
    }

    /**
     * 作用域：按班级筛选
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * 作用域：按教师筛选
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * 作用域：按日期范围筛选
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('schedule_date', [$startDate, $endDate]);
    }

    /**
     * 作用域：按状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * 作用域：今日课程
     */
    public function scopeToday($query)
    {
        return $query->whereDate('schedule_date', today());
    }

    /**
     * 作用域：未来课程
     */
    public function scopeFuture($query)
    {
        return $query->where('schedule_date', '>=', today());
    }

    /**
     * 作用域：按时间排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('schedule_date')->orderBy('time_slot_id');
    }
}
