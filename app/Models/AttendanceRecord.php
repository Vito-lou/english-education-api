<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'schedule_id',
        'student_id',
        'attendance_status',
        'deducted_lessons',
        'check_in_time',
        'absence_reason',
        'makeup_required',
        'makeup_scheduled',
        'teacher_notes',
        'recorded_by',
        'recorded_at',
    ];

    protected $casts = [
        'schedule_id' => 'integer',
        'student_id' => 'integer',
        'deducted_lessons' => 'decimal:2',
        'check_in_time' => 'datetime',
        'makeup_required' => 'boolean',
        'makeup_scheduled' => 'boolean',
        'recorded_by' => 'integer',
        'recorded_at' => 'datetime',
    ];

    /**
     * 出勤状态常量
     */
    const STATUS_PRESENT = 'present';
    const STATUS_ABSENT = 'absent';
    const STATUS_LATE = 'late';
    const STATUS_LEAVE_EARLY = 'leave_early';
    const STATUS_SICK_LEAVE = 'sick_leave';
    const STATUS_PERSONAL_LEAVE = 'personal_leave';

    /**
     * 所属课程安排
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    /**
     * 所属学员
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 记录人
     */
    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * 获取出勤状态中文名称
     */
    public function getStatusNameAttribute(): string
    {
        $statuses = [
            self::STATUS_PRESENT => '到课',
            self::STATUS_ABSENT => '缺勤',
            self::STATUS_LATE => '迟到',
            self::STATUS_LEAVE_EARLY => '早退',
            self::STATUS_SICK_LEAVE => '病假',
            self::STATUS_PERSONAL_LEAVE => '事假',
        ];

        return $statuses[$this->attendance_status] ?? $this->attendance_status;
    }

    /**
     * 作用域：按课程安排筛选
     */
    public function scopeBySchedule($query, $scheduleId)
    {
        return $query->where('schedule_id', $scheduleId);
    }

    /**
     * 作用域：按学员筛选
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * 作用域：按出勤状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('attendance_status', $status);
    }

    /**
     * 作用域：需要补课的记录
     */
    public function scopeMakeupRequired($query)
    {
        return $query->where('makeup_required', true);
    }

    /**
     * 作用域：已安排补课的记录
     */
    public function scopeMakeupScheduled($query)
    {
        return $query->where('makeup_scheduled', true);
    }
}
