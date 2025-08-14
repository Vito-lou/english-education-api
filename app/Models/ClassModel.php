<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'campus_id',
        'course_id',
        'level_id',
        'max_students',
        'teacher_id',
        'total_lessons',
        'status',
        'start_date',
        'end_date',
        'remarks',
        'institution_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_students' => 'integer',
        'total_lessons' => 'integer',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'deleted_at',
    ];

    /**
     * 关联所属校区
     */
    public function campus(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'campus_id');
    }

    /**
     * 关联课程
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * 关联课程级别
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(CourseLevel::class, 'level_id');
    }

    /**
     * 关联授课老师
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * 关联机构
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * 关联学员（通过中间表）
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_classes', 'class_id', 'student_id')
            ->withPivot(['enrollment_date', 'status'])
            ->withTimestamps();
    }

    /**
     * 关联活跃学员
     */
    public function activeStudents(): BelongsToMany
    {
        return $this->students()->wherePivot('status', 'active');
    }

    /**
     * 关联学员班级记录
     */
    public function studentClasses(): HasMany
    {
        return $this->hasMany(StudentClass::class, 'class_id');
    }

    /**
     * 获取当前学员数量
     */
    public function getCurrentStudentCountAttribute(): int
    {
        return $this->activeStudents()->count();
    }

    /**
     * 获取班级容量信息
     */
    public function getCapacityInfoAttribute(): string
    {
        return $this->current_student_count . '/' . $this->max_students;
    }

    /**
     * 获取状态中文名
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            'active' => '进行中',
            'graduated' => '已结业',
            default => '未知状态',
        };
    }

    /**
     * 检查是否可以添加学员
     */
    public function canAddStudent(): bool
    {
        return $this->status === 'active' && $this->current_student_count < $this->max_students;
    }

    /**
     * 结业班级
     */
    public function graduate(): bool
    {
        $this->status = 'graduated';
        $this->end_date = now()->toDateString();

        // 同时将所有活跃学员状态改为已结业
        $this->studentClasses()
            ->where('status', 'active')
            ->update(['status' => 'graduated']);

        return $this->save();
    }

    /**
     * 作用域：按机构筛选
     */
    public function scopeForInstitution($query, $institutionId)
    {
        return $query->where('institution_id', $institutionId);
    }

    /**
     * 作用域：按状态筛选
     */
    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'all') {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * 作用域：按校区筛选
     */
    public function scopeByCampus($query, $campusId)
    {
        if ($campusId) {
            return $query->where('campus_id', $campusId);
        }
        return $query;
    }

    /**
     * 作用域：按课程筛选
     */
    public function scopeByCourse($query, $courseId)
    {
        if ($courseId) {
            return $query->where('course_id', $courseId);
        }
        return $query;
    }

    /**
     * 作用域：搜索班级名称
     */
    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where('name', 'like', '%' . $search . '%');
        }
        return $query;
    }
}
