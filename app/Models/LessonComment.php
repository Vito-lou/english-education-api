<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonComment extends Model
{
    protected $fillable = [
        'schedule_id',
        'student_id',
        'teacher_comment',
        'performance_rating',
        'homework_completion',
        'homework_quality_rating',
    ];

    protected $casts = [
        'performance_rating' => 'integer',
        'homework_quality_rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联排课
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ClassSchedule::class, 'schedule_id');
    }

    /**
     * 关联学员
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 获取作业完成情况中文名称
     */
    public function getHomeworkCompletionNameAttribute(): string
    {
        return match($this->homework_completion) {
            'completed' => '已完成',
            'partial' => '部分完成',
            'not_completed' => '未完成',
            default => '未评价',
        };
    }
}
