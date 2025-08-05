<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_id',
        'name',
        'code',
        'description',
        'is_system',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'is_system' => 'boolean',
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
            ->withPivot(['granted_by', 'granted_at'])
            ->withTimestamps();
    }

    /**
     * 角色的功能权限
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * 角色的数据权限
     */
    public function dataPermissions(): BelongsToMany
    {
        return $this->belongsToMany(DataPermission::class, 'role_data_permissions');
    }

    /**
     * 角色的菜单权限（新的简化模型）
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(SystemMenu::class, 'role_menus', 'role_id', 'menu_id');
    }

    /**
     * 获取角色类型标签
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->is_system ? '系统角色' : '机构角色';
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
     * 检查是否有指定功能权限
     */
    public function hasPermission(string $permissionCode): bool
    {
        return $this->permissions()->where('code', $permissionCode)->exists();
    }

    /**
     * 检查是否有指定数据权限
     */
    public function hasDataPermission(string $dataPermissionCode): bool
    {
        return $this->dataPermissions()->where('code', $dataPermissionCode)->exists();
    }

    /**
     * 检查是否为系统角色
     */
    public function isSystem(): bool
    {
        return $this->is_system;
    }

    /**
     * 检查是否激活
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
