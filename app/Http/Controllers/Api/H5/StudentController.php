<?php

namespace App\Http\Controllers\Api\H5;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AttendanceRecord;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * 根据姓名搜索学生
     */
    public function searchByName(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:1|max:50',
        ]);

        $name = $request->get('name');

        $students = Student::with(['user:id,name'])
            ->where(function ($query) use ($name) {
                $query->whereHas('user', function ($subQuery) use ($name) {
                    $subQuery->where('name', 'like', "%{$name}%");
                })
                ->orWhere('name', 'like', "%{$name}%");
            })
            ->limit(10)
            ->get(['id', 'user_id', 'name', 'student_type']);

        return response()->json([
            'success' => true,
            'message' => '搜索成功',
            'data' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? $student->name,
                    'student_id' => $student->id,
                    'current_level' => $student->student_type,
                    'user_id' => $student->user_id, // 添加调试信息
                    'user_name' => $student->user->name ?? null, // 添加调试信息
                ];
            }),
        ]);
    }

    /**
     * 获取学生详情
     */
    public function getDetail(Request $request, $id): JsonResponse
    {
        $student = Student::with(['user:id,name,avatar'])
            ->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'id' => $student->id,
                'name' => $student->user->name ?? $student->name,
                'student_id' => $student->id,
                'current_level' => $student->student_type,
            ],
        ]);
    }

    /**
     * 获取学生课时信息
     */
    public function getClassHours(Request $request, $id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        // 计算课时统计
        $usedLessons = (float) AttendanceRecord::where('student_id', $id)->sum('deducted_lessons');

        // 这里应该从学生的课时包或者其他地方获取总课时，暂时使用固定值
        $totalHours = 48; // 临时固定值，实际应该从数据库获取
        $remainingHours = max(0, $totalHours - $usedLessons);

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'remaining_hours' => (float) $remainingHours,
                'total_hours' => (int) $totalHours,
                'used_hours' => (float) $usedLessons,
            ],
        ]);
    }

    /**
     * 获取学生学习进度
     */
    public function getProgress(Request $request, $id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        // 这里应该计算实际的学习进度，暂时返回模拟数据
        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'current_level' => $student->student_type,
                'completed_stories' => 5,
                'total_stories' => 20,
                'current_story' => '当前学习故事',
            ],
        ]);
    }

    /**
     * 获取学生考勤记录
     */
    public function getAttendanceRecords(Request $request, $id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        $query = AttendanceRecord::where('student_id', $id)
            ->with(['schedule.course', 'schedule.teacher', 'schedule.timeSlot', 'recorder']);

        // 日期筛选
        if ($request->filled('date_from')) {
            $query->where(function ($q) use ($request) {
                // 有排课的记录按排课日期筛选
                $q->whereHas('schedule', function ($subQ) use ($request) {
                    $subQ->where('schedule_date', '>=', $request->get('date_from'));
                })
                // 手动点名记录按上课时间筛选
                ->orWhere(function ($subQ) use ($request) {
                    $subQ->where('record_type', 'manual')
                         ->whereDate('lesson_time', '>=', $request->get('date_from'));
                });
            });
        }

        if ($request->filled('date_to')) {
            $query->where(function ($q) use ($request) {
                // 有排课的记录按排课日期筛选
                $q->whereHas('schedule', function ($subQ) use ($request) {
                    $subQ->where('schedule_date', '<=', $request->get('date_to'));
                })
                // 手动点名记录按上课时间筛选
                ->orWhere(function ($subQ) use ($request) {
                    $subQ->where('record_type', 'manual')
                         ->whereDate('lesson_time', '<=', $request->get('date_to'));
                });
            });
        }

        $records = $query
            ->orderBy('lesson_time', 'desc') // 使用统一的上课时间字段排序
            ->orderBy('id', 'desc') // ID作为第二排序字段，确保排序稳定
            ->paginate($request->get('per_page', 10));

        // 格式化数据 - 使用统一的lesson_time字段
        $formattedRecords = $records->getCollection()->map(function ($record) {
            return [
                'id' => $record->id,
                'record_type' => $record->record_type,
                'schedule_date' => $record->lesson_time ? date('Y-m-d', strtotime($record->lesson_time)) : '',
                'time_range' => $record->lesson_time ? date('H:i', strtotime($record->lesson_time)) : '',
                'course_name' => $record->record_type === 'manual'
                    ? ($record->lesson_content ?: '手动补录课程')
                    : ($record->schedule && $record->schedule->course ? $record->schedule->course->name : ''),
                'teacher_name' => $record->record_type === 'manual'
                    ? ($record->recorder ? $record->recorder->name : '未知')
                    : ($record->schedule && $record->schedule->teacher ? $record->schedule->teacher->name : ''),
                'student_name' => $record->student->name,
                'attendance_status' => $record->attendance_status,
                'status_name' => $record->status_name,
                'deducted_lessons' => (float)$record->deducted_lessons,
                'teacher_notes' => $record->teacher_notes,
                'recorded_at' => $record->recorded_at->format('Y-m-d H:i:s'),
            ];
        });

        $records->setCollection($formattedRecords);

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $records->items(),
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'has_more' => $records->hasMorePages(),
            ],
        ]);
    }

    /**
     * 获取学生课时统计汇总（只返回统计信息，不包含记录）
     */
    public function getClassHoursSummary(Request $request, $id): JsonResponse
    {
        $student = Student::with(['user:id,name'])->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        // 计算课时统计
        $usedLessons = (float) AttendanceRecord::where('student_id', $id)->sum('deducted_lessons');

        // 这里应该从学生的课时包获取总课时，暂时使用固定值
        $totalLessons = 48; // 临时固定值
        $remainingLessons = max(0, $totalLessons - $usedLessons);

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'id' => $student->id,
                'name' => $student->user->name ?? $student->name,
                'total_lessons' => (int) $totalLessons,
                'used_lessons' => (float) $usedLessons,
                'remaining_lessons' => (float) $remainingLessons,
            ],
        ]);
    }

    /**
     * 获取当前用户关联的学员信息
     */
    public function getMyStudents(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '用户未登录',
            ], 401);
        }

        // 查找用户关联的学员
        $students = Student::with(['user:id,name'])
            ->where('user_id', $user->id)
            ->orWhereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get(['id', 'user_id', 'name', 'student_type']);

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? $student->name,
                    'student_id' => $student->id,
                    'current_level' => $student->student_type,
                ];
            }),
        ]);
    }

    /**
     * 调试：获取所有学员信息
     */
    public function debug(): JsonResponse
    {
        $students = Student::with(['user:id,name,email'])
            ->get(['id', 'user_id', 'name', 'student_type', 'parent_name']);

        return response()->json([
            'success' => true,
            'message' => '调试信息',
            'data' => $students->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'parent_name' => $student->parent_name,
                    'student_type' => $student->student_type,
                    'user_id' => $student->user_id,
                    'user_name' => $student->user->name ?? null,
                    'user_email' => $student->user->email ?? null,
                ];
            }),
        ]);
    }

    /**
     * 获取学生课程表
     */
    public function getSchedule(Request $request, $id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        // 获取学生所在的班级
        $classIds = $student->classes()->pluck('classes.id');

        if ($classIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [
                    'student_name' => $student->user->name ?? $student->name,
                    'schedules' => [],
                    'upcoming_classes' => [],
                ],
            ]);
        }

        // 获取日期范围参数
        $dateFrom = $request->get('date_from', now()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->addDays(30)->format('Y-m-d'));

        // 查询课程安排
        $query = ClassSchedule::with([
            'class:id,name,level_id',
            'course:id,name',
            'teacher:id,name',
            'timeSlot:id,name,start_time,end_time',
            'lesson.unit:id,name'
        ])
        ->whereIn('class_id', $classIds)
        ->where('schedule_date', '>=', $dateFrom)
        ->where('schedule_date', '<=', $dateTo)
        ->where('status', '!=', 'cancelled');

        $schedules = $query->orderBy('schedule_date', 'asc')
            ->orderBy('time_slot_id', 'asc')
            ->get();

        // 格式化课程表数据
        $formattedSchedules = $schedules->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'date' => $schedule->schedule_date,
                'formatted_date' => date('m月d日', strtotime($schedule->schedule_date)),
                'weekday' => date('w', strtotime($schedule->schedule_date)),
                'weekday_name' => $this->getWeekdayName(date('w', strtotime($schedule->schedule_date))),
                'time_slot' => [
                    'id' => $schedule->timeSlot->id ?? null,
                    'name' => $schedule->timeSlot->name ?? '',
                    'start_time' => $schedule->timeSlot->start_time ?? '',
                    'end_time' => $schedule->timeSlot->end_time ?? '',
                    'time_range' => $schedule->timeSlot->time_range ?? '',
                ],
                'class' => [
                    'id' => $schedule->class->id ?? null,
                    'name' => $schedule->class->name ?? '',
                ],
                'course' => [
                    'id' => $schedule->course->id ?? null,
                    'name' => $schedule->course->name ?? '',
                ],
                'teacher' => [
                    'id' => $schedule->teacher->id ?? null,
                    'name' => $schedule->teacher->name ?? '',
                ],
                'lesson_content' => $schedule->lesson_content ?? '',
                'teaching_focus' => $schedule->teaching_focus ?? '',
                'classroom' => $schedule->classroom ?? '',
                'status' => $schedule->status,
                'status_name' => $this->getStatusName($schedule->status),
                'lesson_info' => $schedule->lesson ? [
                    'unit_name' => $schedule->lesson->unit->name ?? '',
                    'lesson_name' => $schedule->lesson->name ?? '',
                ] : null,
            ];
        });

        // 获取即将到来的课程（未来7天内）
        $upcomingClasses = $formattedSchedules->filter(function ($schedule) {
            $scheduleDate = strtotime($schedule['date']);
            $today = strtotime(date('Y-m-d'));
            $nextWeek = strtotime('+7 days', $today);

            return $scheduleDate >= $today && $scheduleDate <= $nextWeek;
        })->take(5)->values();

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'student_name' => $student->user->name ?? $student->name,
                'schedules' => $formattedSchedules->values(),
                'upcoming_classes' => $upcomingClasses,
                'date_range' => [
                    'from' => $dateFrom,
                    'to' => $dateTo,
                ],
            ],
        ]);
    }

    /**
     * 获取星期名称
     */
    private function getWeekdayName($weekday): string
    {
        $weekdays = [
            0 => '周日',
            1 => '周一',
            2 => '周二',
            3 => '周三',
            4 => '周四',
            5 => '周五',
            6 => '周六',
        ];

        return $weekdays[$weekday] ?? '';
    }

    /**
     * 获取状态名称
     */
    private function getStatusName($status): string
    {
        $statusNames = [
            'scheduled' => '已安排',
            'completed' => '已完成',
            'cancelled' => '已取消',
            'in_progress' => '进行中',
        ];

        return $statusNames[$status] ?? $status;
    }


}
