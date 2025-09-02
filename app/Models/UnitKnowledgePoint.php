<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UnitKnowledgePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'type',
        'content',
        'image_url',
        'explanation',
        'example_sentences',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'example_sentences' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * 所属单元
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class, 'unit_id');
    }

    /**
     * 关联的作业
     */
    public function homeworkAssignments(): BelongsToMany
    {
        return $this->belongsToMany(
            HomeworkAssignment::class,
            'homework_knowledge_points',
            'knowledge_point_id',
            'homework_assignment_id'
        );
    }

    /**
     * 获取类型中文名
     */
    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'vocabulary' => '词汇',
            'sentence_pattern' => '句型',
            'grammar' => '语法',
            default => '未知',
        };
    }

    /**
     * 作用域：按类型筛选
     */
    public function scopeByType($query, $type)
    {
        if ($type && $type !== 'all') {
            return $query->where('type', $type);
        }
        return $query;
    }

    /**
     * 作用域：按单元筛选
     */
    public function scopeByUnit($query, $unitId)
    {
        if ($unitId) {
            return $query->where('unit_id', $unitId);
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
