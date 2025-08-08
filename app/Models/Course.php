<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'name',
        'code',
        'description',
        'has_levels',
        'institution_id',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'has_levels' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * 所属科目
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * 所属机构
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * 课程级别
     */
    public function levels(): HasMany
    {
        return $this->hasMany(CourseLevel::class)->orderBy('sort_order');
    }

    /**
     * 课程单元
     */
    public function units(): HasMany
    {
        return $this->hasMany(CourseUnit::class)->orderBy('sort_order');
    }
}
