<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeworkAssignment extends Model
{
    protected $fillable = [
        'arrangement_id',
        'title',
        'content',
        'due_date',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联课程安排
     */
    public function arrangement(): BelongsTo
    {
        return $this->belongsTo(LessonArrangement::class, 'arrangement_id');
    }

    /**
     * 关联创建者
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 获取状态
     */
    public function getStatusAttribute(): string
    {
        return $this->due_date < now()->toDateString() ? 'expired' : 'active';
    }
}
