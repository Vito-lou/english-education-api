<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'parent_id',
        'name',
        'code',
        'type',
        'description',
        'manager_name',
        'manager_phone',
        'address',
        'capacity',
        'facilities',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'facilities' => 'array',
        'capacity' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * 所属机构
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * 上级部门
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * 下级部门
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * 部门下的用户
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * 获取所有子部门（递归）
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * 获取部门类型标签
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'campus' => '校区',
            'department' => '部门',
            'classroom' => '教室',
            default => '未知'
        };
    }

    /**
     * 获取部门状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => '正常',
            'inactive' => '停用',
            default => '未知'
        };
    }

    /**
     * 获取部门层级路径
     */
    public function getPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * 检查是否为校区
     */
    public function isCampus(): bool
    {
        return $this->type === 'campus';
    }

    /**
     * 检查是否为教室
     */
    public function isClassroom(): bool
    {
        return $this->type === 'classroom';
    }
}
