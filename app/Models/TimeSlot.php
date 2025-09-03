<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    protected $fillable = [
        'institution_id',
        'name',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_minutes' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * 所属机构
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * 课程安排
     */
    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    /**
     * 获取时间段显示名称
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name . ' (' . substr($this->start_time, 0, 5) . '-' . substr($this->end_time, 0, 5) . ')';
    }

    /**
     * 获取时间段范围
     */
    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5) . '-' . substr($this->end_time, 0, 5);
    }

    /**
     * 获取以小时为单位的时长
     */
    public function getDurationAttribute(): float
    {
        return $this->duration_minutes / 60;
    }


    /**
     * 作用域：启用的时间段
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 作用域：按机构筛选
     */
    public function scopeByInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    /**
     * 作用域：按排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('start_time');
    }
}
