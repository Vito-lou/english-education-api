<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'name',
        'code',
        'description',
        'type',
        'permissions',
        'data_scope',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'permissions' => 'array',
        'data_scope' => 'array',
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
     * 拥有此角色的用户
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['additional_permissions', 'data_restrictions', 'assigned_at', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * 获取角色类型标签
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'system' => '系统预设',
            'custom' => '自定义',
            default => '未知'
        };
    }

    /**
     * 获取角色状态标签
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
     * 检查是否有指定权限
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissions) {
            return false;
        }

        // 检查是否有超级权限
        if (in_array('*', $this->permissions)) {
            return true;
        }

        // 检查具体权限
        return in_array($permission, $this->permissions);
    }

    /**
     * 检查是否有权限组
     */
    public function hasPermissionGroup(string $group): bool
    {
        if (!$this->permissions) {
            return false;
        }

        // 检查是否有超级权限
        if (in_array('*', $this->permissions)) {
            return true;
        }

        // 检查权限组（如 users.*）
        $groupPattern = $group . '.*';
        return in_array($groupPattern, $this->permissions);
    }

    /**
     * 检查是否为系统角色
     */
    public function isSystem(): bool
    {
        return $this->type === 'system';
    }

    /**
     * 检查是否激活
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
