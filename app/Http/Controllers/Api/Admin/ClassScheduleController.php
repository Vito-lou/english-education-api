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
                AttendanceRecord::updateOrCreate(
                    [
                        'schedule_id' => $schedule->id,
                        'student_id' => $studentData['student_id'],
                    ],
                    [
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

        $query = AttendanceRecord::with(['schedule.course', 'schedule.teacher', 'schedule.timeSlot', 'student'])
            ->whereHas('schedule', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });

        // 筛选条件
        if ($request->filled('date_from')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('schedule_date', '>=', $request->get('date_from'));
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('schedule_date', '<=', $request->get('date_to'));
            });
        }

        if ($request->filled('student_name')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->get('student_name') . '%');
            });
        }

        $records = $query->orderBy('recorded_at', 'desc')->get();

        $data = $records->map(function ($record) {
            return [
                'id' => $record->id,
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
        });

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $data,
        ]);
    }
}


