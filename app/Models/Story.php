<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'author',
        'difficulty_level',
        'cover_image_url',
        'has_chapters',
        'content',
    ];

    protected $casts = [
        'has_chapters' => 'boolean',
    ];

    /**
     * 关联章节
     */
    public function chapters(): HasMany
    {
        return $this->hasMany(StoryChapter::class)->orderBy('chapter_number');
    }

    /**
     * 关联知识点（多对多）
     */
    public function knowledgePoints(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgePoint::class,
            'story_knowledge_relations',
            'story_id',
            'knowledge_point_id'
        )->withTimestamps();
    }

    /**
     * 获取故事内容（如果有章节则返回所有章节内容）
     */
    public function getFullContentAttribute(): string
    {
        if ($this->has_chapters) {
            return $this->chapters->pluck('content')->implode("\n\n");
        }

        return $this->content ?? '';
    }

    /**
     * 获取总字数
     */
    public function getTotalWordCountAttribute(): int
    {
        if ($this->has_chapters) {
            return $this->chapters->sum('word_count');
        }

        return str_word_count($this->content ?? '');
    }

    /**
     * 作用域：按难度筛选
     */
    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * 作用域：有章节的故事
     */
    public function scopeWithChapters($query)
    {
        return $query->where('has_chapters', true);
    }

    /**
     * 作用域：单篇故事
     */
    public function scopeSingleStory($query)
    {
        return $query->where('has_chapters', false);
    }
}
