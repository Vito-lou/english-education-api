<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\ClassModel;
use App\Models\TimeSlot;
use App\Models\StudentEnrollment;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClassScheduleController extends Controller
{
    /**
     * 获取排课列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = ClassSchedule::with(['class', 'course', 'teacher', 'timeSlot'])
            ->whereHas('class', function ($q) use ($user) {
                $q->where('institution_id', $user->institution_id);
            });

        // 筛选条件
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->get('class_id'));
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->get('teacher_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('schedule_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('schedule_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // 排序
        $schedules = $query->ordered()->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'class_id' => $schedule->class_id,
                    'course_id' => $schedule->course_id,
                    'teacher_id' => $schedule->teacher_id,
                    'time_slot_id' => $schedule->time_slot_id,
                    'schedule_date' => $schedule->schedule_date->format('Y-m-d'),
                    'lesson_content' => $schedule->lesson_content,
                    'classroom' => $schedule->classroom,
                    'status' => $schedule->status,
                    'status_name' => $schedule->status_name,
                    'full_time_info' => $schedule->full_time_info,
                    'class' => $schedule->class ? [
                        'id' => $schedule->class->id,
                        'name' => $schedule->class->name,
                    ] : null,
                    'course' => $schedule->course ? [
                        'id' => $schedule->course->id,
                        'name' => $schedule->course->name,
                    ] : null,
                    'teacher' => $schedule->teacher ? [
                        'id' => $schedule->teacher->id,
                        'name' => $schedule->teacher->name,
                    ] : null,
                    'time_slot' => $schedule->timeSlot ? [
                        'id' => $schedule->timeSlot->id,
                        'name' => $schedule->timeSlot->name,
                        'time_range' => $schedule->timeSlot->time_range,
                    ] : null,
                    'created_at' => $schedule->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * 创建单个排课
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'schedule_date' => 'required|date|after_or_equal:today',
            'lesson_content' => 'nullable|string|max:100',
            'classroom' => 'nullable|string|max:50',
        ]);

        // 权限检查：班级是否属于当前机构
        $class = ClassModel::find($validated['class_id']);
        if ($class->institution_id !== $user->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权为该班级排课',
            ], 403);
        }

        // 冲突检查
        $conflicts = $this->checkScheduleConflicts(
            $validated['class_id'],
            $validated['teacher_id'],
            $validated['time_slot_id'],
            $validated['schedule_date']
        );

        if (!empty($conflicts)) {
            return response()->json([
                'code' => 400,
                'message' => '排课冲突',
                'data' => ['conflicts' => $conflicts],
            ], 400);
        }

        $schedule = ClassSchedule::create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        $schedule->load(['class', 'course', 'teacher', 'timeSlot']);

        return response()->json([
            'code' => 200,
            'message' => '排课创建成功',
            'data' => $schedule,
        ]);
    }

    /**
     * 批量创建排课
     */
    public function batchCreate(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:users,id',
            'time_slot_id' => 'required|exists:time_slots,id',
            'dates' => 'required|array|min:1',
            'dates.*' => 'date|after_or_equal:today',
            'lesson_content' => 'nullable|string|max:100',
            'classroom' => 'nullable|string|max:50',
        ]);

        // 权限检查
        $class = ClassModel::find($validated['class_id']);
        if ($class->institution_id !== $user->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权为该班级排课',
            ], 403);
        }

        $createdSchedules = [];
        $conflicts = [];

        DB::beginTransaction();
        try {
            foreach ($validated['dates'] as $date) {
                // 检查冲突
                $dateConflicts = $this->checkScheduleConflicts(
                    $validated['class_id'],
                    $validated['teacher_id'],
                    $validated['time_slot_id'],
                    $date
                );

                if (!empty($dateConflicts)) {
                    $conflicts[$date] = $dateConflicts;
                    continue;
                }

                // 创建排课
                $schedule = ClassSchedule::create([
                    'class_id' => $validated['class_id'],
                    'course_id' => $validated['course_id'],
                    'teacher_id' => $validated['teacher_id'],
                    'time_slot_id' => $validated['time_slot_id'],
                    'schedule_date' => $date,
                    'lesson_content' => $validated['lesson_content'],
                    'classroom' => $validated['classroom'],
                    'created_by' => $user->id,
                ]);

                $createdSchedules[] = $schedule;
            }

            DB::commit();

            $message = count($createdSchedules) > 0
                ? "成功创建 " . count($createdSchedules) . " 个排课"
                : "没有创建任何排课";

            if (!empty($conflicts)) {
                $message .= "，" . count($conflicts) . " 个日期存在冲突";
            }

            return response()->json([
                'code' => 200,
                'message' => $message,
                'data' => [
                    'created_count' => count($createdSchedules),
                    'conflict_count' => count($conflicts),
                    'conflicts' => $conflicts,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'code' => 500,
                'message' => '批量排课失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 删除排课
     */
    public function destroy(ClassSchedule $classSchedule): JsonResponse
    {
        $user = Auth::user();

        // 权限检查
        if ($classSchedule->class->institution_id !== $user->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权删除该排课',
            ], 403);
        }

        // 检查是否已点名（已完成状态不能删除）
        if ($classSchedule->status === ClassSchedule::STATUS_COMPLETED) {
            return response()->json([
                'code' => 400,
                'message' => '已点名的排课不能删除，如需修改请使用修改点名功能',
            ], 400);
        }

        $classSchedule->delete();

        return response()->json([
            'code' => 200,
            'message' => '排课删除成功',
        ]);
    }

    /**
     * 检查排课冲突
     */
    private function checkScheduleConflicts(int $classId, int $teacherId, int $timeSlotId, string $date): array
    {
        $conflicts = [];

        // 检查班级时间冲突
        $classConflict = ClassSchedule::where('class_id', $classId)
            ->where('schedule_date', $date)
            ->where('time_slot_id', $timeSlotId)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($classConflict) {
            $conflicts[] = '班级在该时间段已有课程安排';
        }

        // 检查教师时间冲突
        $teacherConflict = ClassSchedule::where('teacher_id', $teacherId)
            ->where('schedule_date', $date)
            ->where('time_slot_id', $timeSlotId)
            ->where('status', '!=', 'cancelled')
            ->exists();

        if ($teacherConflict) {
            $conflicts[] = '教师在该时间段已有课程安排';
        }

        return $conflicts;
    }

    /**
     * 获取指定课程的点名详情
     */
    public function getAttendance(Request $request, ClassSchedule $schedule): JsonResponse
    {
        // 权限检查
        if ($schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json(['code' => 403, 'message' => '无权访问'], 403);
        }

        // 加载关联数据
        $schedule->load(['class', 'course', 'teacher', 'timeSlot']);

        // 获取班级的所有在读学员
        $classStudents = $schedule->class->students()->where('student_classes.status', 'active')->get();

        // 获取已有的考勤记录
        $existingAttendance = $schedule->attendanceRecords()->get()->keyBy('student_id');

        $studentsData = $classStudents->map(function ($student) use ($schedule, $existingAttendance) {
            // 找到学员针对该课程的有效报名记录
            $enrollment = $student->enrollments()
                ->where('course_id', $schedule->course_id)
                ->whereIn('status', ['active', 'completed'])
                ->first();

            $attendance = $existingAttendance->get($student->id);

            return [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'enrollment_id' => $enrollment ? $enrollment->id : null,
                'course_name' => $schedule->course->name,
                'remaining_lessons' => $enrollment ? $enrollment->remaining_lessons : 0,
                'attendance_status' => $attendance ? $attendance->attendance_status : 'present', // 默认到课
                'deducted_lessons' => $attendance ? (float)$attendance->deducted_lessons : 1.0, // 默认扣1课时
                'remarks' => $attendance ? $attendance->teacher_notes : '',
            ];
        })->filter(function ($studentData) {
            // 只返回有有效报名记录的学员
            return $studentData['enrollment_id'] !== null;
        })->values(); // 重新索引数组

        $scheduleInfo = [
            'id' => $schedule->id,
            'class_id' => $schedule->class_id,
            'class_name' => $schedule->class->name,
            'lesson_date' => $schedule->schedule_date->format('Y-m-d'),
            'start_time' => $schedule->timeSlot->start_time->format('H:i'),
            'end_time' => $schedule->timeSlot->end_time->format('H:i'),
            'duration' => $schedule->timeSlot->duration,
            'teacher_name' => $schedule->teacher->name,
            'subject' => $schedule->course->name,
        ];

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'schedule_info' => $scheduleInfo,
                'lesson_content' => $schedule->lesson_content,
                'students' => $studentsData,
            ],
        ]);
    }

    /**
     * 批量提交点名记录
     */
    public function saveAttendance(Request $request, ClassSchedule $schedule): JsonResponse
    {
        // 权限检查
        if ($schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json(['code' => 403, 'message' => '无权操作'], 403);
        }

        // 加载时间段信息
        $schedule->load('timeSlot');

        $validated = $request->validate([
            'lesson_content' => 'nullable|string|max:1000',
            'students' => 'required|array',
            'students.*.student_id' => 'required|integer|exists:students,id',
            'students.*.enrollment_id' => 'nullable|integer|exists:student_enrollments,id',
            'students.*.attendance_status' => 'required|string|in:present,late,absent,leave,sick_leave,personal_leave',
            'students.*.deducted_lessons' => 'required|numeric|min:0|max:10',
            'students.*.remarks' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            // 1. 更新上课内容和状态
            $schedule->update([
                'lesson_content' => $validated['lesson_content'],
                'status' => ClassSchedule::STATUS_COMPLETED, // 点名后自动标记为已完成
            ]);

            foreach ($validated['students'] as $studentData) {
                // 跳过没有报名记录的学员
                if (!$studentData['enrollment_id']) {
                    continue;
                }

                $enrollment = StudentEnrollment::find($studentData['enrollment_id']);
                if (!$enrollment) {
                    continue;
                }

                $oldAttendance = AttendanceRecord::where('schedule_id', $schedule->id)
                    ->where('student_id', $studentData['student_id'])
                    ->first();

                $oldDeductedLessons = $oldAttendance ? (float)$oldAttendance->deducted_lessons : 0;

                // 2. 更新或创建考勤记录
                // 计算lesson_time：排课日期 + 时间段开始时间
                $timeSlot = $schedule->timeSlot;
                if (!$timeSlot) {
                    throw new \Exception("排课ID {$schedule->id} 没有关联的时间段");
                }
                $lessonTime = $schedule->schedule_date->format('Y-m-d') . ' ' . $timeSlot->start_time;

                AttendanceRecord::updateOrCreate(
                    [
                        'schedule_id' => $schedule->id,
                        'student_id' => $studentData['student_id'],
                    ],
                    [
                        'record_type' => 'scheduled', // 排课点名
                        'lesson_time' => $lessonTime, // 设置统一的上课时间
                        'attendance_status' => $studentData['attendance_status'],
                        'deducted_lessons' => $studentData['deducted_lessons'],
                        'teacher_notes' => $studentData['remarks'],
                        'recorded_by' => Auth::id(),
                        'recorded_at' => now(),
                    ]
                );

                // 3. 计算课时差异并更新报名记录
                $lessonDifference = (float)$studentData['deducted_lessons'] - $oldDeductedLessons;

                if ($lessonDifference != 0) {
                    $enrollment->remaining_lessons -= $lessonDifference;
                    $enrollment->used_lessons += $lessonDifference;
                    $enrollment->save();
                }
            }

            DB::commit();

            return response()->json(['code' => 200, 'message' => '点名成功']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'message' => '操作失败: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 获取班级的点名记录
     */
    public function getClassAttendanceRecords(Request $request, $classId): JsonResponse
    {
        $user = Auth::user();

        // 权限检查
        $class = ClassModel::find($classId);
        if (!$class || $class->institution_id !== $user->institution_id) {
            return response()->json(['code' => 403, 'message' => '无权访问'], 403);
        }

        $query = AttendanceRecord::with(['schedule.course', 'schedule.teacher', 'schedule.timeSlot', 'student', 'recorder'])
            ->where(function ($q) use ($classId) {
                // 包含有排课的记录
                $q->whereHas('schedule', function ($subQ) use ($classId) {
                    $subQ->where('class_id', $classId);
                })
                // 或者包含手动点名记录
                ->orWhere(function ($subQ) use ($classId) {
                    $subQ->where('record_type', 'manual')
                         ->where('class_id', $classId);
                });
            });

        // 筛选条件
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

        if ($request->filled('student_name')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->get('student_name') . '%');
            });
        }

        $records = $query->orderBy('recorded_at', 'desc')->get();

        $data = $records->map(function ($record) {
            if ($record->record_type === 'manual') {
                // 手动点名记录
                return [
                    'id' => $record->id,
                    'record_type' => 'manual',
                    'schedule_date' => $record->lesson_time ? date('Y-m-d', strtotime($record->lesson_time)) : '',
                    'time_range' => $record->lesson_time ? date('H:i', strtotime($record->lesson_time)) : '手动补录',
                    'course_name' => $record->lesson_content ?: '手动补录课程',
                    'teacher_name' => $record->recorder ? $record->recorder->name : '未知',
                    'student_name' => $record->student->name,
                    'attendance_status' => $record->attendance_status,
                    'status_name' => $record->status_name,
                    'deducted_lessons' => (float)$record->deducted_lessons,
                    'teacher_notes' => $record->teacher_notes,
                    'recorded_at' => $record->recorded_at->format('Y-m-d H:i:s'),
                ];
            } else {
                // 正常排课记录
                return [
                    'id' => $record->id,
                    'record_type' => 'scheduled',
                    'schedule_date' => $record->schedule->schedule_date->format('Y-m-d'),
                    'time_range' => $record->schedule->timeSlot->time_range,
                    'course_name' => $record->schedule->course->name,
                    'teacher_name' => $record->schedule->teacher->name,
                    'student_name' => $record->student->name,
                    'attendance_status' => $record->attendance_status,
                    'status_name' => $record->status_name,
                    'deducted_lessons' => (float)$record->deducted_lessons,
                    'teacher_notes' => $record->teacher_notes,
                    'recorded_at' => $record->recorded_at->format('Y-m-d H:i:s'),
                ];
            }
        });

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $data,
        ]);
    }

    /**
     * 获取指定排课可用的课时列表（按级别筛选）
     */
    public function getAvailableLessons(Request $request, string $id): JsonResponse
    {
        $schedule = ClassSchedule::with(['class.level'])->find($id);

        if (!$schedule) {
            return response()->json([
                'code' => 404,
                'message' => '排课不存在',
            ], 404);
        }

        // 权限检查
        if ($schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        // 获取该级别的课程单元和课时
        $units = \App\Models\CourseUnit::with(['lessons' => function ($q) {
            $q->where('status', 'active')->orderBy('sort_order');
        }])
        ->where('level_id', $schedule->class->level_id)
        ->where('status', 'active')
        ->orderBy('sort_order')
        ->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'schedule' => $schedule,
                'units' => $units,
            ],
        ]);
    }

    /**
     * 设置排课的课程内容
     */
    public function setLessonContent(Request $request, string $id): JsonResponse
    {
        $schedule = ClassSchedule::with('class')->find($id);

        if (!$schedule) {
            return response()->json([
                'code' => 404,
                'message' => '排课不存在',
            ], 404);
        }

        // 权限检查
        if ($schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $validated = $request->validate([
            'lesson_id' => 'nullable|exists:lessons,id',
            'teaching_focus' => 'nullable|string',
        ]);

        // 如果设置了课时，验证课时是否属于正确的级别
        if ($validated['lesson_id']) {
            $lesson = \App\Models\Lesson::with('unit')->find($validated['lesson_id']);
            if ($lesson && $lesson->unit->level_id !== $schedule->class->level_id) {
                return response()->json([
                    'code' => 400,
                    'message' => '所选课时不属于该班级的级别',
                ], 400);
            }
        }

        $schedule->update([
            'lesson_id' => $validated['lesson_id'] ?: null,
            'teaching_focus' => $validated['teaching_focus'] ?: null,
        ]);

        $schedule->load(['class', 'course', 'teacher', 'timeSlot', 'lesson.unit.course']);

        return response()->json([
            'code' => 200,
            'message' => '设置成功',
            'data' => $schedule,
        ]);
    }

    /**
     * 获取课程安排列表（用于家校互动）
     */
    public function getLessonArrangements(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = ClassSchedule::with([
            'class',
            'teacher',
            'timeSlot',
            'lesson.unit.course'
        ])->whereHas('class', function ($q) use ($user) {
            $q->where('institution_id', $user->institution_id);
        })->whereNotNull('lesson_id'); // 只显示已安排课程内容的排课

        // 筛选条件
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->get('class_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('schedule_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('schedule_date', '<=', $request->get('date_to'));
        }

        $schedules = $query->orderBy('schedule_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $schedules,
        ]);
    }

    /**
     * 获取未安排课程内容的排课列表
     */
    public function getUnassignedSchedules(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = ClassSchedule::with(['class', 'teacher', 'timeSlot'])
            ->whereHas('class', function ($q) use ($user) {
                $q->where('institution_id', $user->institution_id);
            })
            ->whereNull('lesson_id') // 只显示未安排课程内容的排课
            ->where('status', 'scheduled'); // 只显示已安排的排课

        $schedules = $query->orderBy('schedule_date', 'asc')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $schedules,
        ]);
    }
}


