<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'definition_en',
        'definition_cn',
        'explanation',
    ];

    /**
     * 关联故事（多对多）
     */
    public function stories(): BelongsToMany
    {
        return $this->belongsToMany(
            Story::class,
            'story_knowledge_relations',
            'knowledge_point_id',
            'story_id'
        )->withTimestamps();
    }

    /**
     * 关联标签（多对多）
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgeTag::class,
            'knowledge_point_tags',
            'knowledge_point_id',
            'knowledge_tag_id'
        )->withTimestamps();
    }

    /**
     * 关联例句（一对多）
     */
    public function examples(): HasMany
    {
        return $this->hasMany(KnowledgePointExample::class)->ordered();
    }

    /**
     * 作用域：按类型筛选
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * 作用域：词汇类型
     */
    public function scopeVocabulary($query)
    {
        return $query->where('type', 'vocabulary');
    }

    /**
     * 作用域：语法类型
     */
    public function scopeGrammar($query)
    {
        return $query->where('type', 'grammar');
    }

    /**
     * 作用域：短语类型
     */
    public function scopePhrase($query)
    {
        return $query->where('type', 'phrase');
    }

    /**
     * 作用域：句型类型
     */
    public function scopeSentencePattern($query)
    {
        return $query->where('type', 'sentence_pattern');
    }

    /**
     * 获取类型的中文名称
     */
    public function getTypeNameAttribute(): string
    {
        $types = [
            'vocabulary' => '词汇',
            'grammar' => '语法',
            'phrase' => '短语',
            'sentence_pattern' => '句型',
        ];

        return $types[$this->type] ?? $this->type;
    }
}
