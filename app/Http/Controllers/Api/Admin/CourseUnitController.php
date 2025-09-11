<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseUnit;
use App\Models\Course;
use App\Models\CourseLevel;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CourseUnitController extends Controller
{
    /**
     * 获取课程单元列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = CourseUnit::with(['course', 'level', 'lessons', 'story', 'storyChapter']);

        // 按课程筛选
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // 按级别筛选
        if ($request->has('level_id')) {
            if ($request->level_id === 'null' || $request->level_id === '') {
                $query->whereNull('level_id');
            } else {
                $query->where('level_id', $request->level_id);
            }
        }

        $units = $query->orderBy('sort_order')->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $units
        ]);
    }

    /**
     * 创建课程单元
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'level_id' => 'nullable|exists:course_levels,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'learning_objectives' => 'nullable|string',
            'story_id' => 'nullable|exists:stories,id',
            'story_chapter_id' => 'nullable|exists:story_chapters,id',
            'sort_order' => 'integer|min:0',
        ]);

        // 如果指定了级别，验证级别是否属于该课程
        if ($request->level_id) {
            $level = CourseLevel::where('id', $request->level_id)
                ->where('course_id', $request->course_id)
                ->first();

            if (!$level) {
                return response()->json([
                    'code' => 400,
                    'message' => '指定的级别不属于该课程'
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            $unit = CourseUnit::create([
                'course_id' => $request->course_id,
                'level_id' => $request->level_id,
                'name' => $request->name,
                'description' => $request->description,
                'learning_objectives' => $request->learning_objectives,
                'story_id' => $request->story_id,
                'story_chapter_id' => $request->story_chapter_id,
                'sort_order' => $request->sort_order ?? 0,
                'status' => 'active',
            ]);



            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '单元创建成功',
                'data' => $unit->load(['course', 'level', 'lessons', 'story', 'storyChapter'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 获取单元详情
     */
    public function show(CourseUnit $courseUnit): JsonResponse
    {
        $courseUnit->load(['course', 'level', 'lessons', 'story', 'storyChapter']);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $courseUnit
        ]);
    }

    /**
     * 更新单元
     */
    public function update(Request $request, CourseUnit $courseUnit): JsonResponse
    {
        $request->validate([
            'level_id' => 'nullable|exists:course_levels,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'learning_objectives' => 'nullable|string',
            'story_id' => 'nullable|exists:stories,id',
            'story_chapter_id' => 'nullable|exists:story_chapters,id',
            'sort_order' => 'integer|min:0',
        ]);

        // 如果指定了级别，验证级别是否属于该课程
        if ($request->level_id) {
            $level = CourseLevel::where('id', $request->level_id)
                ->where('course_id', $courseUnit->course_id)
                ->first();

            if (!$level) {
                return response()->json([
                    'code' => 400,
                    'message' => '指定的级别不属于该课程'
                ], 400);
            }
        }

        DB::beginTransaction();
        try {
            $courseUnit->update($request->only([
                'level_id', 'name', 'description', 'learning_objectives', 'story_id', 'story_chapter_id', 'sort_order'
            ]));



            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => '单元更新成功',
                'data' => $courseUnit->load(['course', 'level', 'lessons', 'story', 'storyChapter'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除单元
     */
    public function destroy(CourseUnit $courseUnit): JsonResponse
    {
        // 检查是否有关联的课时
        if ($courseUnit->lessons()->count() > 0) {
            return response()->json([
                'code' => 400,
                'message' => '该单元下还有课时，无法删除'
            ], 400);
        }

        $courseUnit->delete();

        return response()->json([
            'code' => 200,
            'message' => '单元删除成功'
        ]);
    }
}
