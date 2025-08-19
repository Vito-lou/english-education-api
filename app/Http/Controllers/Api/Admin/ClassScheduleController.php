<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\ClassModel;
use App\Models\TimeSlot;
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

        // 检查是否已有考勤记录
        if ($classSchedule->attendanceRecords()->exists()) {
            return response()->json([
                'code' => 400,
                'message' => '该课程已有考勤记录，无法删除',
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
}
