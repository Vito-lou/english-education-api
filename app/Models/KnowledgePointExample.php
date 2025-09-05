<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgePointExample extends Model
{
    use HasFactory;

    protected $fillable = [
        'knowledge_point_id',
        'example_en',
        'example_cn',
        'sequence',
    ];

    protected $casts = [
        'sequence' => 'integer',
    ];

    /**
     * 关联知识点
     */
    public function knowledgePoint(): BelongsTo
    {
        return $this->belongsTo(KnowledgePoint::class);
    }

    /**
     * 作用域：按顺序排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence')->orderBy('id');
    }
}
