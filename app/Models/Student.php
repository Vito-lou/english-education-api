<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'gender',
        'birth_date',
        'parent_name',
        'parent_phone',
        'parent_relationship',
        'student_type',
        'follow_up_status',
        'intention_level',
        'user_id',
        'institution_id',
        'source',
        'remarks',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'gender' => 'string',
        'student_type' => 'string',
        'follow_up_status' => 'string',
        'intention_level' => 'string',
        'parent_relationship' => 'string',
        'status' => 'string',
    ];

    /**
     * 关联用户（主要家长账号）
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 关联机构
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * 关联的所有用户（支持多个家长）
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_students')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }

    /**
     * 获取年龄
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }

        return $this->birth_date->diffInYears(now());
    }

    /**
     * 获取学员类型中文名
     */
    public function getStudentTypeNameAttribute(): string
    {
        $types = [
            'potential' => '潜在学员',
            'trial' => '试听学员',
            'enrolled' => '正式学员',
            'graduated' => '已毕业',
            'suspended' => '暂停学习',
        ];

        return $types[$this->student_type] ?? $this->student_type;
    }

    /**
     * 获取跟进状态中文名
     */
    public function getFollowUpStatusNameAttribute(): string
    {
        $statuses = [
            'new' => '新学员',
            'contacted' => '已联系',
            'interested' => '有意向',
            'not_interested' => '无意向',
            'follow_up' => '跟进中',
        ];

        return $statuses[$this->follow_up_status] ?? $this->follow_up_status;
    }

    /**
     * 获取意向等级中文名
     */
    public function getIntentionLevelNameAttribute(): string
    {
        $levels = [
            'high' => '高意向',
            'medium' => '中意向',
            'low' => '低意向',
        ];

        return $levels[$this->intention_level] ?? $this->intention_level;
    }

    /**
     * 作用域：按机构筛选
     */
    public function scopeByInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    /**
     * 作用域：按学员类型筛选
     */
    public function scopeByType($query, $type)
    {
        return $query->where('student_type', $type);
    }

    /**
     * 作用域：按状态筛选
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
