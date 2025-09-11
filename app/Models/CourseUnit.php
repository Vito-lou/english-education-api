<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'level_id',
        'name',
        'description',
        'learning_objectives',
        'story_id',
        'story_chapter_id',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * 所属课程
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * 所属级别
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'level_id');
    }

    /**
     * 课时列表
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'unit_id')->orderBy('sort_order');
    }



    /**
     * 关联的故事
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class, 'story_id');
    }

    /**
     * 关联的故事章节
     */
    public function storyChapter(): BelongsTo
    {
        return $this->belongsTo(StoryChapter::class, 'story_chapter_id');
    }

    /**
     * 获取关联故事的知识点
     */
    public function getStoryKnowledgePointsAttribute()
    {
        if ($this->story) {
            return $this->story->knowledgePoints;
        }
        return collect();
    }


}
