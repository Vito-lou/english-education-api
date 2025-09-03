<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'name',
        'content',
        'duration',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'duration' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * 所属课程单元
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class, 'unit_id');
    }

    /**
     * 所属课程单元 (别名方法，用于兼容)
     */
    public function courseUnit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class, 'unit_id');
    }
}
