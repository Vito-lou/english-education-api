<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'description',
        'contact_person',
        'contact_phone',
        'contact_email',
        'address',
        'business_license',
        'business_hours',
        'settings',
        'status',
        'established_at',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'settings' => 'array',
        'established_at' => 'datetime',
    ];

    /**
     * 机构下的部门
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * 机构下的用户
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * 机构下的角色
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * 获取机构的校区（顶级部门）
     */
    public function campuses(): HasMany
    {
        return $this->departments()->whereNull('parent_id')->where('type', 'campus');
    }

    /**
     * 获取机构状态标签
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => '正常',
            'inactive' => '停用',
            'suspended' => '暂停',
            default => '未知'
        };
    }

    /**
     * 检查机构是否激活
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
