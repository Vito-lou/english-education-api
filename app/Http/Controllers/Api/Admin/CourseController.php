<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    /**
     * 获取科目列表
     */
    public function getSubjects(): JsonResponse
    {
        $subjects = Subject::with('courses')
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $subjects
        ]);
    }

    /**
     * 获取课程列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::with(['subject', 'levels', 'units']);

        // 按科目筛选
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        // 按状态筛选
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $courses = $query->orderBy('sort_order')->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $courses
        ]);
    }

    /**
     * 创建课程
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100',
            'description' => 'nullable|string',
            'has_levels' => 'boolean',
        ]);

        $course = Course::create([
            'subject_id' => $request->subject_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'has_levels' => $request->has_levels ?? true,
            'institution_id' => 1, // TODO: 从当前用户获取
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'code' => 200,
            'message' => '课程创建成功',
            'data' => $course->load(['subject', 'levels', 'units'])
        ]);
    }

    /**
     * 获取课程详情
     */
    public function show(Course $course): JsonResponse
    {
        $course->load([
            'subject',
            'levels' => function($query) {
                $query->orderBy('sort_order');
            },
            'levels.units' => function($query) {
                $query->orderBy('sort_order');
            },
            'levels.units.lessons' => function($query) {
                $query->orderBy('sort_order');
            },
            'units' => function($query) {
                $query->orderBy('sort_order');
            },
            'units.lessons' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $course
        ]);
    }

    /**
     * 更新课程
     */
    public function update(Request $request, Course $course): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100',
            'description' => 'nullable|string',
            'has_levels' => 'boolean',
        ]);

        $course->update($request->only([
            'name', 'code', 'description', 'has_levels', 'sort_order'
        ]));

        return response()->json([
            'code' => 200,
            'message' => '课程更新成功',
            'data' => $course->load(['subject', 'levels', 'units'])
        ]);
    }

    /**
     * 删除课程
     */
    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json([
            'code' => 200,
            'message' => '课程删除成功'
        ]);
    }
}
