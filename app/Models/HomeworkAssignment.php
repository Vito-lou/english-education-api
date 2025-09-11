<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeworkAssignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'class_id',
        'unit_id',
        'due_date',
        'requirements',
        'attachments',
        'status',
        'created_by',
        'institution_id',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'attachments' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联班级
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * 关联创建者
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 关联机构
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * 关联作业提交记录
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    /**
     * 关联单元
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class, 'unit_id');
    }

    /**
     * 关联知识点（单元知识点）
     */
    public function knowledgePoints(): BelongsToMany
    {
        return $this->belongsToMany(
            UnitKnowledgePoint::class,
            'homework_knowledge_points',
            'homework_assignment_id',
            'knowledge_point_id'
        );
    }

    /**
     * 关联故事知识点
     */
    public function storyKnowledgePoints(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgePoint::class,
            'homework_story_knowledge_points',
            'homework_assignment_id',
            'knowledge_point_id'
        );
    }

    /**
     * 获取所有关联的知识点（包括单元知识点和故事知识点）
     */
    public function getAllKnowledgePointsAttribute()
    {
        $unitKnowledgePoints = $this->knowledgePoints->map(function ($point) {
            return [
                'id' => $point->id,
                'type' => $point->type,
                'name' => $point->content,
                'content' => $point->content,
                'explanation' => $point->explanation,
                'source' => 'unit',
            ];
        });

        $storyKnowledgePoints = $this->storyKnowledgePoints->map(function ($point) {
            return [
                'id' => $point->id,
                'type' => $point->type,
                'name' => $point->name,
                'content' => $point->name,
                'explanation' => $point->explanation,
                'source' => 'story',
            ];
        });

        return $unitKnowledgePoints->concat($storyKnowledgePoints);
    }

    /**
     * 获取状态中文名
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'active' => '进行中',
            'expired' => '已过期',
            'draft' => '草稿',
            default => '未知状态',
        };
    }

    /**
     * 检查是否已过期
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->due_date < now();
    }

    /**
     * 获取提交统计
     */
    public function getSubmissionStatsAttribute(): array
    {
        $totalStudents = $this->class->activeStudents()->count();
        $submittedCount = $this->submissions()->count();

        return [
            'total_students' => $totalStudents,
            'submitted_count' => $submittedCount,
            'pending_count' => $totalStudents - $submittedCount,
            'submission_rate' => $totalStudents > 0 ? round(($submittedCount / $totalStudents) * 100, 2) : 0,
        ];
    }

    /**
     * 作用域：按机构筛选
     */
    public function scopeForInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
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

    /**
     * 作用域：按班级筛选
     */
    public function scopeByClass($query, $classId)
    {
        if ($classId) {
            return $query->where('class_id', $classId);
        }
        return $query;
    }

    /**
     * 作用域：搜索标题
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('title', 'like', '%' . $search . '%');
        }
        return $query;
    }
}
