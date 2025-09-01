<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LessonArrangement extends Model
{
    protected $fillable = [
        'schedule_id',
        'lesson_id',
        'teaching_focus',
        'created_by',
    ];

    protected $casts = [
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
     * 关联课时
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * 关联创建者
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联作业
     */
    public function homeworkAssignments(): HasMany
    {
        return $this->hasMany(HomeworkAssignment::class, 'arrangement_id');
    }
}
