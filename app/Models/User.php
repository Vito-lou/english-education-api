<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'institution_id',
        'department_id',
        'employee_id',
        'phone',
        'gender',
        'birth_date',
        'id_card',
        'avatar',
        'bio',
        'qualifications',
        'specialties',
        'can_teach',
        'hourly_rate',
        'employment_type',
        'hire_date',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'id_card',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'hire_date' => 'date',
            'last_login_at' => 'datetime',
            'qualifications' => 'array',
            'specialties' => 'array',
            'can_teach' => 'boolean',
            'hourly_rate' => 'decimal:2',
        ];
    }

    /**
     * 所属机构
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * 所属部门
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * 用户的角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['granted_by', 'granted_at'])
            ->withTimestamps();
    }

    /**
     * 获取用户状态标签
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
     * 获取性别标签
     */
    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'male' => '男',
            'female' => '女',
            'other' => '其他',
            default => '未知'
        };
    }

    /**
     * 检查用户是否有指定权限
     */
    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 检查用户是否激活
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * 检查用户是否可以授课
     */
    public function canTeach(): bool
    {
        return $this->can_teach && $this->isActive();
    }
}
