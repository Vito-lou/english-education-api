<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'path',
        'icon',
        'parent_id',
        'sort_order',
        'status',
        'description',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * 父菜单
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(SystemMenu::class, 'parent_id');
    }

    /**
     * 子菜单
     */
    public function children(): HasMany
    {
        return $this->hasMany(SystemMenu::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * 关联的权限（旧模型，保留兼容）
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'menu_id');
    }

    /**
     * 拥有此菜单权限的角色（新的简化模型）
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_menus', 'menu_id', 'role_id');
    }

    /**
     * 获取菜单树
     */
    public static function getMenuTree(): array
    {
        $menus = self::where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return self::buildTree($menus);
    }

    /**
     * 构建菜单树
     */
    private static function buildTree($menus, $parentId = null): array
    {
        $tree = [];

        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $menu->children_items = self::buildTree($menus, $menu->id);
                $tree[] = $menu;
            }
        }

        return $tree;
    }

    /**
     * 检查是否为叶子节点
     */
    public function isLeaf(): bool
    {
        return $this->children()->count() === 0;
    }
}
