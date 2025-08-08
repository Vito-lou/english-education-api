<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseLevel;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseLevelController extends Controller
{
    /**
     * 获取课程级别列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = CourseLevel::with(['course', 'units']);

        // 按课程筛选
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $levels = $query->orderBy('sort_order')->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $levels
        ]);
    }

    /**
     * 创建课程级别
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
        ]);

        // 检查同一课程下代码是否重复
        $exists = CourseLevel::where('course_id', $request->course_id)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return response()->json([
                'code' => 400,
                'message' => '该课程下已存在相同代码的级别'
            ], 400);
        }

        $level = CourseLevel::create([
            'course_id' => $request->course_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'status' => 'active',
        ]);

        return response()->json([
            'code' => 200,
            'message' => '级别创建成功',
            'data' => $level->load(['course', 'units'])
        ]);
    }

    /**
     * 获取级别详情
     */
    public function show(CourseLevel $courseLevel): JsonResponse
    {
        $courseLevel->load(['course', 'units.lessons']);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $courseLevel
        ]);
    }

    /**
     * 更新级别
     */
    public function update(Request $request, CourseLevel $courseLevel): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100',
            'description' => 'nullable|string',
            'sort_order' => 'integer|min:0',
        ]);

        // 检查同一课程下代码是否重复（排除当前级别）
        $exists = CourseLevel::where('course_id', $courseLevel->course_id)
            ->where('code', $request->code)
            ->where('id', '!=', $courseLevel->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'code' => 400,
                'message' => '该课程下已存在相同代码的级别'
            ], 400);
        }

        $courseLevel->update($request->only([
            'name', 'code', 'description', 'sort_order'
        ]));

        return response()->json([
            'code' => 200,
            'message' => '级别更新成功',
            'data' => $courseLevel->load(['course', 'units'])
        ]);
    }

    /**
     * 删除级别
     */
    public function destroy(CourseLevel $courseLevel): JsonResponse
    {
        // 检查是否有关联的单元
        if ($courseLevel->units()->count() > 0) {
            return response()->json([
                'code' => 400,
                'message' => '该级别下还有课程单元，无法删除'
            ], 400);
        }

        $courseLevel->delete();

        return response()->json([
            'code' => 200,
            'message' => '级别删除成功'
        ]);
    }
}
