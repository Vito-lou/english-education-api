<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStudent extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'relationship',
    ];

    protected $casts = [
        'relationship' => 'string',
    ];

    /**
     * 关联用户
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联学员
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 获取关系中文名
     */
    public function getRelationshipNameAttribute(): string
    {
        $relationships = [
            'parent' => '家长',
            'father' => '父亲',
            'mother' => '母亲',
            'guardian' => '监护人',
            'other' => '其他',
        ];

        return $relationships[$this->relationship] ?? $this->relationship;
    }
}
