<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 学员类型常量
     */
    const TYPE_POTENTIAL = 'potential';    // 潜在学员
    const TYPE_TRIAL = 'trial';           // 试听学员
    const TYPE_ENROLLED = 'enrolled';     // 正式学员（只能通过报名获得）
    const TYPE_REFUNDED = 'refunded';     // 已退费学员
    const TYPE_GRADUATED = 'graduated';   // 已毕业
    const TYPE_SUSPENDED = 'suspended';   // 暂停学习

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
            self::TYPE_POTENTIAL => '潜在学员',
            self::TYPE_TRIAL => '试听学员',
            self::TYPE_ENROLLED => '正式学员',
            self::TYPE_REFUNDED => '已退费学员',
            self::TYPE_GRADUATED => '已毕业',
            self::TYPE_SUSPENDED => '暂停学习',
        ];

        return $types[$this->student_type] ?? $this->student_type;
    }

    /**
     * 获取可创建的学员类型（不包括正式学员）
     */
    public static function getCreatableTypes(): array
    {
        return [
            self::TYPE_POTENTIAL => '潜在学员',
            self::TYPE_TRIAL => '试听学员',
        ];
    }

    /**
     * 检查是否为正式学员
     */
    public function isEnrolled(): bool
    {
        return $this->student_type === self::TYPE_ENROLLED;
    }

    /**
     * 将学员状态改为正式学员（只能通过报名流程调用）
     */
    public function markAsEnrolled(): void
    {
        $this->student_type = self::TYPE_ENROLLED;
        $this->save();
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

    /**
     * 关联班级（通过中间表）
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassModel::class, 'student_classes', 'student_id', 'class_id')
            ->withPivot(['enrollment_date', 'status'])
            ->withTimestamps();
    }

    /**
     * 关联活跃班级
     */
    public function activeClasses(): BelongsToMany
    {
        return $this->classes()->wherePivot('status', 'active');
    }

    /**
     * 关联学员班级记录
     */
    public function studentClasses(): HasMany
    {
        return $this->hasMany(StudentClass::class);
    }

    /**
     * 关联学员报名记录
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }
}
