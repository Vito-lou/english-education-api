<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class KnowledgeTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag_name',
        'tag_system',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * 关联知识点（多对多）
     */
    public function knowledgePoints(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgePoint::class,
            'knowledge_point_tags',
            'knowledge_tag_id',
            'knowledge_point_id'
        )->withTimestamps();
    }

    /**
     * 作用域：按标签体系筛选
     */
    public function scopeBySystem($query, $system)
    {
        return $query->where('tag_system', $system);
    }

    /**
     * 作用域：K12体系
     */
    public function scopeK12($query)
    {
        return $query->where('tag_system', 'k12');
    }

    /**
     * 作用域：剑桥体系
     */
    public function scopeCambridge($query)
    {
        return $query->where('tag_system', 'cambridge');
    }

    /**
     * 作用域：雅思体系
     */
    public function scopeIelts($query)
    {
        return $query->where('tag_system', 'ielts');
    }

    /**
     * 作用域：托福体系
     */
    public function scopeToefl($query)
    {
        return $query->where('tag_system', 'toefl');
    }

    /**
     * 获取标签体系的中文名称
     */
    public function getSystemNameAttribute(): string
    {
        $systems = [
            'k12' => 'K12教育',
            'cambridge' => '剑桥英语',
            'ielts' => '雅思',
            'toefl' => '托福',
        ];

        return $systems[$this->tag_system] ?? $this->tag_system;
    }
}
