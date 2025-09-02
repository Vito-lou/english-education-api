<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkSubmission extends Model
{
    protected $fillable = [
        'homework_assignment_id',
        'student_id',
        'content',
        'attachments',
        'status',
        'score',
        'max_score',
        'teacher_feedback',
        'submitted_at',
        'graded_at',
        'graded_by',
    ];

    protected $casts = [
        'attachments' => 'array',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联作业
     */
    public function homeworkAssignment(): BelongsTo
    {
        return $this->belongsTo(HomeworkAssignment::class);
    }

    /**
     * 关联学生
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 关联批改教师
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * 获取状态中文名
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'submitted' => '已提交',
            'late' => '迟交',
            'graded' => '已批改',
            default => '未知状态',
        };
    }

    /**
     * 获取得分百分比
     */
    public function getScorePercentageAttribute(): ?float
    {
        if ($this->score === null || $this->max_score <= 0) {
            return null;
        }

        return round(($this->score / $this->max_score) * 100, 2);
    }

    /**
     * 检查是否迟交
     */
    public function getIsLateAttribute(): bool
    {
        return $this->submitted_at > $this->homeworkAssignment->due_date;
    }

    /**
     * 检查是否已批改
     */
    public function getIsGradedAttribute(): bool
    {
        return $this->status === 'graded' && $this->graded_at !== null;
    }

    /**
     * 作用域：按作业筛选
     */
    public function scopeByHomework($query, $homeworkId)
    {
        if ($homeworkId) {
            return $query->where('homework_assignment_id', $homeworkId);
        }
        return $query;
    }

    /**
     * 作用域：按学生筛选
     */
    public function scopeByStudent($query, $studentId)
    {
        if ($studentId) {
            return $query->where('student_id', $studentId);
        }
        return $query;
    }

    /**
     * 作用域：按状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }
}
