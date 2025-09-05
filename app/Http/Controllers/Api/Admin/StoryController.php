<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryChapter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Story::with(['chapters', 'knowledgePoints']);

        // 搜索
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 难度筛选
        if ($request->filled('difficulty_level')) {
            $query->where('difficulty_level', $request->get('difficulty_level'));
        }

        // 是否有章节筛选
        if ($request->filled('has_chapters')) {
            $query->where('has_chapters', $request->boolean('has_chapters'));
        }

        $stories = $query->orderBy('created_at', 'desc')
                        ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $stories->items(),
            'pagination' => [
                'current_page' => $stories->currentPage(),
                'last_page' => $stories->lastPage(),
                'per_page' => $stories->perPage(),
                'total' => $stories->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:100',
            'difficulty_level' => 'nullable|string|max:50',
            'cover_image_url' => 'nullable|url|max:255',
            'has_chapters' => 'boolean',
            'content' => 'nullable|string',
            'chapters' => 'nullable|array',
            'chapters.*.chapter_number' => 'required_with:chapters|integer|min:1',
            'chapters.*.chapter_title' => 'required_with:chapters|string|max:255',
            'chapters.*.content' => 'required_with:chapters|string',
            'knowledge_point_ids' => 'nullable|array',
            'knowledge_point_ids.*' => 'exists:knowledge_points,id',
        ]);

        DB::beginTransaction();
        try {
            // 创建故事
            $story = Story::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'author' => $validated['author'] ?? null,
                'difficulty_level' => $validated['difficulty_level'] ?? null,
                'cover_image_url' => $validated['cover_image_url'] ?? null,
                'has_chapters' => $validated['has_chapters'] ?? false,
                'content' => $validated['has_chapters'] ? null : ($validated['content'] ?? null),
            ]);

            // 如果有章节，创建章节
            if (!empty($validated['chapters']) && $validated['has_chapters']) {
                foreach ($validated['chapters'] as $chapterData) {
                    StoryChapter::create([
                        'story_id' => $story->id,
                        'chapter_number' => $chapterData['chapter_number'],
                        'chapter_title' => $chapterData['chapter_title'],
                        'content' => $chapterData['content'],
                    ]);
                }
            }

            // 关联知识点
            if (!empty($validated['knowledge_point_ids'])) {
                $story->knowledgePoints()->attach($validated['knowledge_point_ids']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '故事创建成功',
                'data' => $story->load(['chapters', 'knowledgePoints']),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '创建失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $story = Story::with(['chapters', 'knowledgePoints.tags'])->find($id);

        if (!$story) {
            return response()->json([
                'success' => false,
                'message' => '故事不存在',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $story,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $story = Story::find($id);

        if (!$story) {
            return response()->json([
                'success' => false,
                'message' => '故事不存在',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'author' => 'nullable|string|max:100',
            'difficulty_level' => 'nullable|string|max:50',
            'cover_image_url' => 'nullable|url|max:255',
            'has_chapters' => 'boolean',
            'content' => 'nullable|string',
            'chapters' => 'nullable|array',
            'chapters.*.id' => 'nullable|exists:story_chapters,id',
            'chapters.*.chapter_number' => 'required_with:chapters|integer|min:1',
            'chapters.*.chapter_title' => 'required_with:chapters|string|max:255',
            'chapters.*.content' => 'required_with:chapters|string',
            'knowledge_point_ids' => 'nullable|array',
            'knowledge_point_ids.*' => 'exists:knowledge_points,id',
        ]);

        DB::beginTransaction();
        try {
            // 更新故事基本信息
            $story->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'author' => $validated['author'] ?? null,
                'difficulty_level' => $validated['difficulty_level'] ?? null,
                'cover_image_url' => $validated['cover_image_url'] ?? null,
                'has_chapters' => $validated['has_chapters'] ?? false,
                'content' => $validated['has_chapters'] ? null : ($validated['content'] ?? null),
            ]);

            // 处理章节
            if ($validated['has_chapters']) {
                // 删除现有章节
                $story->chapters()->delete();

                // 创建新章节
                if (!empty($validated['chapters'])) {
                    foreach ($validated['chapters'] as $chapterData) {
                        StoryChapter::create([
                            'story_id' => $story->id,
                            'chapter_number' => $chapterData['chapter_number'],
                            'chapter_title' => $chapterData['chapter_title'],
                            'content' => $chapterData['content'],
                        ]);
                    }
                }
            } else {
                // 如果改为单篇故事，删除所有章节
                $story->chapters()->delete();
            }

            // 更新知识点关联
            if (isset($validated['knowledge_point_ids'])) {
                $story->knowledgePoints()->sync($validated['knowledge_point_ids']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '故事更新成功',
                'data' => $story->load(['chapters', 'knowledgePoints']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '更新失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $story = Story::find($id);

        if (!$story) {
            return response()->json([
                'success' => false,
                'message' => '故事不存在',
            ], 404);
        }

        try {
            $story->delete();

            return response()->json([
                'success' => true,
                'message' => '故事删除成功',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取难度等级列表
     */
    public function getDifficultyLevels(): JsonResponse
    {
        $levels = Story::select('difficulty_level')
                      ->whereNotNull('difficulty_level')
                      ->distinct()
                      ->pluck('difficulty_level')
                      ->filter()
                      ->values();

        return response()->json([
            'success' => true,
            'data' => $levels,
        ]);
    }
}
