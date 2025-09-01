<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonComment;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonCommentController extends Controller
{
    /**
     * 获取课后点评列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // 获取排课列表，包含点评统计
        $query = ClassSchedule::with([
            'class',
            'teacher',
            'lessonArrangement.lesson.unit.course'
        ])->whereHas('class', function ($q) use ($user) {
            $q->where('institution_id', $user->institution_id);
        })->where('status', 'completed') // 只显示已完成的课程
          ->withCount([
              'students as total_students' => function ($q) {
                  $q->where('student_classes.status', 'active');
              },
              'lessonComments as commented_students'
          ]);

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

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'pending') {
                $query->havingRaw('commented_students < total_students OR commented_students IS NULL');
            } elseif ($status === 'completed') {
                $query->havingRaw('commented_students = total_students AND total_students > 0');
            }
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
     * 获取指定排课的学员点评列表
     */
    public function getScheduleComments(Request $request, string $scheduleId): JsonResponse
    {
        $schedule = ClassSchedule::with([
            'class',
            'students' => function ($q) {
                $q->where('student_classes.status', 'active');
            },
            'lessonComments.student'
        ])->whereHas('class', function ($q) {
            $q->where('institution_id', Auth::user()->institution_id);
        })->find($scheduleId);

        if (!$schedule) {
            return response()->json([
                'code' => 404,
                'message' => '排课不存在',
            ], 404);
        }

        // 构建学员点评数据
        $studentsWithComments = $schedule->students->map(function ($student) use ($schedule) {
            $comment = $schedule->lessonComments->firstWhere('student_id', $student->id);

            return [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'comment' => $comment ? [
                    'id' => $comment->id,
                    'teacher_comment' => $comment->teacher_comment,
                    'performance_rating' => $comment->performance_rating,
                    'homework_completion' => $comment->homework_completion,
                    'homework_completion_name' => $comment->homework_completion_name,
                    'homework_quality_rating' => $comment->homework_quality_rating,
                ] : null,
            ];
        });

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'schedule' => $schedule,
                'students' => $studentsWithComments,
            ],
        ]);
    }

    /**
     * 批量保存课后点评
     */
    public function batchStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:class_schedules,id',
            'comments' => 'required|array',
            'comments.*.student_id' => 'required|exists:students,id',
            'comments.*.teacher_comment' => 'nullable|string',
            'comments.*.performance_rating' => 'nullable|integer|min:1|max:5',
            'comments.*.homework_completion' => 'nullable|in:completed,partial,not_completed',
            'comments.*.homework_quality_rating' => 'nullable|integer|min:1|max:5',
        ]);

        // 检查排课权限
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

        DB::beginTransaction();
        try {
            foreach ($validated['comments'] as $commentData) {
                LessonComment::updateOrCreate(
                    [
                        'schedule_id' => $validated['schedule_id'],
                        'student_id' => $commentData['student_id'],
                    ],
                    $commentData
                );
            }

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '保存成功',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => '保存失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取单个点评详情
     */
    public function show(string $id): JsonResponse
    {
        $comment = LessonComment::with([
            'schedule.class',
            'student'
        ])->find($id);

        if (!$comment) {
            return response()->json([
                'code' => 404,
                'message' => '点评不存在',
            ], 404);
        }

        // 权限检查
        if ($comment->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $comment,
        ]);
    }

    /**
     * 更新点评
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $comment = LessonComment::with('schedule')->find($id);

        if (!$comment) {
            return response()->json([
                'code' => 404,
                'message' => '点评不存在',
            ], 404);
        }

        // 权限检查
        if ($comment->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $validated = $request->validate([
            'teacher_comment' => 'nullable|string',
            'performance_rating' => 'nullable|integer|min:1|max:5',
            'homework_completion' => 'nullable|in:completed,partial,not_completed',
            'homework_quality_rating' => 'nullable|integer|min:1|max:5',
        ]);

        $comment->update($validated);
        $comment->load(['schedule.class', 'student']);

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $comment,
        ]);
    }

    /**
     * 删除点评
     */
    public function destroy(string $id): JsonResponse
    {
        $comment = LessonComment::with('schedule')->find($id);

        if (!$comment) {
            return response()->json([
                'code' => 404,
                'message' => '点评不存在',
            ], 404);
        }

        // 权限检查
        if ($comment->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }
}
