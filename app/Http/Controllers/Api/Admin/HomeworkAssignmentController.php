<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeworkAssignment;
use App\Models\LessonArrangement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HomeworkAssignmentController extends Controller
{
    /**
     * 获取作业列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = HomeworkAssignment::with([
            'arrangement.schedule.class',
            'arrangement.schedule.teacher',
            'arrangement.lesson.unit.course',
            'creator'
        ])->whereHas('arrangement.schedule.class', function ($q) use ($user) {
            $q->where('institution_id', $user->institution_id);
        });

        // 筛选条件
        if ($request->filled('class_id')) {
            $query->whereHas('arrangement.schedule', function ($q) use ($request) {
                $q->where('class_id', $request->get('class_id'));
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('due_date', '>=', now()->toDateString());
            } elseif ($status === 'expired') {
                $query->where('due_date', '<', now()->toDateString());
            }
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->get('date_to'));
        }

        $assignments = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $assignments,
        ]);
    }

    /**
     * 创建作业
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'arrangement_id' => 'required|exists:lesson_arrangements,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        // 检查课程安排是否属于当前机构
        $arrangement = LessonArrangement::with('schedule')
            ->where('id', $validated['arrangement_id'])
            ->first();

        if (!$arrangement || $arrangement->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 404,
                'message' => '课程安排不存在',
            ], 404);
        }

        $assignment = HomeworkAssignment::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        $assignment->load([
            'arrangement.schedule.class',
            'arrangement.lesson.unit.course',
            'creator'
        ]);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $assignment,
        ]);
    }

    /**
     * 获取作业详情
     */
    public function show(string $id): JsonResponse
    {
        $assignment = HomeworkAssignment::with([
            'arrangement.schedule.class',
            'arrangement.schedule.teacher',
            'arrangement.lesson.unit.course',
            'creator'
        ])->find($id);

        if (!$assignment) {
            return response()->json([
                'code' => 404,
                'message' => '作业不存在',
            ], 404);
        }

        // 权限检查
        if ($assignment->arrangement->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $assignment,
        ]);
    }

    /**
     * 更新作业
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $assignment = HomeworkAssignment::with('arrangement.schedule')->find($id);

        if (!$assignment) {
            return response()->json([
                'code' => 404,
                'message' => '作业不存在',
            ], 404);
        }

        // 权限检查
        if ($assignment->arrangement->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'due_date' => 'required|date',
        ]);

        $assignment->update($validated);
        $assignment->load([
            'arrangement.schedule.class',
            'arrangement.lesson.unit.course',
            'creator'
        ]);

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $assignment,
        ]);
    }

    /**
     * 删除作业
     */
    public function destroy(string $id): JsonResponse
    {
        $assignment = HomeworkAssignment::with('arrangement.schedule')->find($id);

        if (!$assignment) {
            return response()->json([
                'code' => 404,
                'message' => '作业不存在',
            ], 404);
        }

        // 权限检查
        if ($assignment->arrangement->schedule->class->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $assignment->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }
}
