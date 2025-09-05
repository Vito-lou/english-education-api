<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoryChapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'story_id',
        'chapter_number',
        'chapter_title',
        'content',
        'word_count',
    ];

    protected $casts = [
        'chapter_number' => 'integer',
        'word_count' => 'integer',
    ];

    /**
     * 关联故事
     */
    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    /**
     * 自动计算字数
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($chapter) {
            if ($chapter->content && !$chapter->word_count) {
                $chapter->word_count = str_word_count($chapter->content);
            }
        });
    }

    /**
     * 作用域：按章节号排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('chapter_number');
    }
}
