<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\CourseUnit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LessonController extends Controller
{
    /**
     * 获取课时列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lesson::with(['unit']);

        // 按单元筛选
        if ($request->has('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $lessons = $query->orderBy('sort_order')->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $lessons
        ]);
    }

    /**
     * 创建课时
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'unit_id' => 'required|exists:course_units,id',
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'duration' => 'integer|min:0',
            'sort_order' => 'integer|min:0',
        ]);

        $lesson = Lesson::create([
            'unit_id' => $request->unit_id,
            'name' => $request->name,
            'content' => $request->content,
            'duration' => $request->duration ?? 0,
            'sort_order' => $request->sort_order ?? 0,
            'status' => 'active',
        ]);

        return response()->json([
            'code' => 200,
            'message' => '课时创建成功',
            'data' => $lesson->load(['unit'])
        ]);
    }

    /**
     * 获取课时详情
     */
    public function show(Lesson $lesson): JsonResponse
    {
        $lesson->load(['unit']);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $lesson
        ]);
    }

    /**
     * 更新课时
     */
    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'duration' => 'integer|min:0',
            'sort_order' => 'integer|min:0',
        ]);

        $lesson->update($request->only([
            'name', 'content', 'duration', 'sort_order'
        ]));

        return response()->json([
            'code' => 200,
            'message' => '课时更新成功',
            'data' => $lesson->load(['unit'])
        ]);
    }

    /**
     * 删除课时
     */
    public function destroy(Lesson $lesson): JsonResponse
    {
        $lesson->delete();

        return response()->json([
            'code' => 200,
            'message' => '课时删除成功'
        ]);
    }
}
