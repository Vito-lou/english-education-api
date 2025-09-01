<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonArrangement;
use App\Models\ClassSchedule;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LessonArrangementController extends Controller
{
    /**
     * 获取课程安排列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = LessonArrangement::with([
            'schedule.class',
            'schedule.teacher',
            'lesson.unit.course',
            'creator'
        ])->whereHas('schedule.class', function ($q) use ($user) {
            $q->where('institution_id', $user->institution_id);
        });

        // 筛选条件
        if ($request->filled('class_id')) {
            $query->whereHas('schedule', function ($q) use ($request) {
                $q->where('class_id', $request->get('class_id'));
            });
        }

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

        $arrangements = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $arrangements,
        ]);
    }

    /**
     * 创建课程安排
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:class_schedules,id',
            'lesson_id' => 'required|exists:lessons,id',
            'teaching_focus' => 'nullable|string',
        ]);

        // 检查排课是否属于当前机构
        $schedule = ClassSchedule::whereHas('class', function ($q) {
            $q->where('institution_id', Auth::user()->institution_id);
        })->where('id', $validated['schedule_id'])
          ->first();

        if (!$schedule) {
            return response()->json([
                'code' => 404,
                'message' => '排课不存在',
            ], 404);
        }

        // 检查是否已经安排过课程内容
        if (LessonArrangement::where('schedule_id', $validated['schedule_id'])->exists()) {
            return response()->json([
                'code' => 400,
                'message' => '该排课已安排课程内容',
            ], 400);
        }

        $arrangement = LessonArrangement::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        $arrangement->load(['schedule.class', 'lesson.unit.course', 'creator']);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $arrangement,
        ]);
    }

    /**
     * 获取课程安排详情
     */
    public function show(string $id): JsonResponse
    {
        $arrangement = LessonArrangement::with([
            'schedule.class',
            'schedule.teacher',
            'lesson.unit.course',
            'creator',
            'homeworkAssignments'
        ])->find($id);

        if (!$arrangement) {
            return response()->json([
                'code' => 404,
                'message' => '课程安排不存在',
            ], 404);
        }

        // 权限检查
        if ($arrangement->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $arrangement,
        ]);
    }

    /**
     * 更新课程安排
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $arrangement = LessonArrangement::find($id);

        if (!$arrangement) {
            return response()->json([
                'code' => 404,
                'message' => '课程安排不存在',
            ], 404);
        }

        // 权限检查
        if ($arrangement->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'teaching_focus' => 'nullable|string',
        ]);

        $arrangement->update($validated);
        $arrangement->load(['schedule.class', 'lesson.unit.course', 'creator']);

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $arrangement,
        ]);
    }

    /**
     * 删除课程安排
     */
    public function destroy(string $id): JsonResponse
    {
        $arrangement = LessonArrangement::find($id);

        if (!$arrangement) {
            return response()->json([
                'code' => 404,
                'message' => '课程安排不存在',
            ], 404);
        }

        // 权限检查
        if ($arrangement->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $arrangement->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }

    /**
     * 获取指定排课可用的课时列表（按级别筛选）
     */
    public function getAvailableLessons(Request $request, string $scheduleId): JsonResponse
    {
        $schedule = ClassSchedule::with(['class.level'])->find($scheduleId);

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
            $q->orderBy('sort_order');
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
}
