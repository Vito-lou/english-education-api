<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryChapter;
use App\Models\KnowledgePoint;
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
        $query = Story::with(['chapters', 'knowledgePoints.examples']);

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
            'knowledge_points' => 'nullable|array',
            'knowledge_points.*.id' => 'nullable|integer',
            'knowledge_points.*.name' => 'required_with:knowledge_points|string|max:255',
            'knowledge_points.*.type' => 'required_with:knowledge_points|in:vocabulary,grammar,phrase,sentence_pattern',
            'knowledge_points.*.definition_en' => 'nullable|string',
            'knowledge_points.*.definition_cn' => 'nullable|string',
            'knowledge_points.*.explanation' => 'nullable|string',
            'knowledge_points.*.examples' => 'nullable|array',
            'knowledge_points.*.examples.*.example_en' => 'required_with:knowledge_points.*.examples|string',
            'knowledge_points.*.examples.*.example_cn' => 'nullable|string',
            'knowledge_points.*.examples.*.sequence' => 'required_with:knowledge_points.*.examples|integer|min:0',
            'knowledge_points.*.audio_url' => 'nullable|url',
            'knowledge_points.*.isNew' => 'nullable|boolean',
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

            // 处理知识点（新建或关联现有）
            $knowledgePointIds = [];
            if (!empty($validated['knowledge_points'])) {
                foreach ($validated['knowledge_points'] as $pointData) {
                    // 使用 isNew 标识判断是新建知识点还是关联现有知识点
                    $isNewPoint = isset($pointData['isNew']) && $pointData['isNew'] === true;

                    if (!$isNewPoint && isset($pointData['id'])) {
                        // 关联现有知识点，不做任何修改
                        $existingPoint = KnowledgePoint::find($pointData['id']);
                        if ($existingPoint) {
                            $knowledgePointIds[] = $existingPoint->id;
                        }
                    } elseif ($isNewPoint) {
                        // 创建新知识点
                        // 检查知识点名称是否已存在
                        $existingPoint = KnowledgePoint::where('name', $pointData['name'])->first();
                        if ($existingPoint) {
                            throw new \Exception("知识点 '{$pointData['name']}' 已存在，请使用其他名称");
                        }

                        $knowledgePoint = KnowledgePoint::create([
                            'name' => $pointData['name'],
                            'type' => $pointData['type'],
                            'definition_en' => $pointData['definition_en'] ?? null,
                            'definition_cn' => $pointData['definition_cn'] ?? null,
                            'explanation' => $pointData['explanation'] ?? null,
                        ]);

                        // 创建例句
                        if (!empty($pointData['examples'])) {
                            foreach ($pointData['examples'] as $example) {
                                $knowledgePoint->examples()->create([
                                    'example_en' => $example['example_en'],
                                    'example_cn' => $example['example_cn'] ?? null,
                                    'sequence' => $example['sequence'],
                                ]);
                            }
                        }

                        $knowledgePointIds[] = $knowledgePoint->id;
                    }
                }
            }

            // 关联现有知识点
            if (!empty($validated['knowledge_point_ids'])) {
                $knowledgePointIds = array_merge($knowledgePointIds, $validated['knowledge_point_ids']);
            }

            // 关联所有知识点
            if (!empty($knowledgePointIds)) {
                $story->knowledgePoints()->attach($knowledgePointIds);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '故事创建成功',
                'data' => $story->load(['chapters', 'knowledgePoints.examples']),
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
        $story = Story::with(['chapters', 'knowledgePoints.tags', 'knowledgePoints.examples'])->find($id);

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
            'knowledge_points' => 'nullable|array',
            'knowledge_points.*.id' => 'nullable|integer',
            'knowledge_points.*.name' => 'required_with:knowledge_points|string|max:255',
            'knowledge_points.*.type' => 'required_with:knowledge_points|in:vocabulary,grammar,phrase,sentence_pattern',
            'knowledge_points.*.definition_en' => 'nullable|string',
            'knowledge_points.*.definition_cn' => 'nullable|string',
            'knowledge_points.*.explanation' => 'nullable|string',
            'knowledge_points.*.examples' => 'nullable|array',
            'knowledge_points.*.examples.*.example_en' => 'required_with:knowledge_points.*.examples|string',
            'knowledge_points.*.examples.*.example_cn' => 'nullable|string',
            'knowledge_points.*.examples.*.sequence' => 'required_with:knowledge_points.*.examples|integer|min:0',
            'knowledge_points.*.audio_url' => 'nullable|url',
            'knowledge_points.*.isNew' => 'nullable|boolean',
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

            // 处理知识点（新建或关联现有）
            $knowledgePointIds = [];
            if (!empty($validated['knowledge_points'])) {
                foreach ($validated['knowledge_points'] as $pointData) {
                    // 使用 isNew 标识判断是新建知识点还是关联现有知识点
                    $isNewPoint = isset($pointData['isNew']) && $pointData['isNew'] === true;

                    if (!$isNewPoint && isset($pointData['id'])) {
                        // 关联现有知识点，不做任何修改
                        $existingPoint = KnowledgePoint::find($pointData['id']);
                        if ($existingPoint) {
                            $knowledgePointIds[] = $existingPoint->id;
                        }
                    } elseif ($isNewPoint) {
                        // 创建新知识点
                        // 检查知识点名称是否已存在
                        $existingPoint = KnowledgePoint::where('name', $pointData['name'])->first();
                        if ($existingPoint) {
                            throw new \Exception("知识点 '{$pointData['name']}' 已存在，请使用其他名称");
                        }

                        $knowledgePoint = KnowledgePoint::create([
                            'name' => $pointData['name'],
                            'type' => $pointData['type'],
                            'definition_en' => $pointData['definition_en'] ?? null,
                            'definition_cn' => $pointData['definition_cn'] ?? null,
                            'explanation' => $pointData['explanation'] ?? null,
                        ]);

                        // 创建例句
                        if (!empty($pointData['examples'])) {
                            foreach ($pointData['examples'] as $example) {
                                $knowledgePoint->examples()->create([
                                    'example_en' => $example['example_en'],
                                    'example_cn' => $example['example_cn'] ?? null,
                                    'sequence' => $example['sequence'],
                                ]);
                            }
                        }

                        $knowledgePointIds[] = $knowledgePoint->id;
                    }
                }
            }

            // 关联现有知识点
            if (!empty($validated['knowledge_point_ids'])) {
                $knowledgePointIds = array_merge($knowledgePointIds, $validated['knowledge_point_ids']);
            }

            // 更新知识点关联
            $story->knowledgePoints()->sync($knowledgePointIds);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '故事更新成功',
                'data' => $story->load(['chapters', 'knowledgePoints.examples']),
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

    /**
     * Get stories tree for selection (used in unit editor)
     */
    public function getStoriesTree(): JsonResponse
    {
        $stories = Story::with(['chapters' => function ($query) {
            $query->orderBy('chapter_number');
        }])
        ->orderBy('title')
        ->get();

        $tree = $stories->map(function ($story) {
            $storyNode = [
                'id' => $story->id,
                'title' => $story->title,
                'description' => $story->description,
                'author' => $story->author,
                'has_chapters' => $story->has_chapters,
                'type' => 'story',
                'children' => [],
            ];

            if ($story->has_chapters && $story->chapters->count() > 0) {
                $storyNode['children'] = $story->chapters->map(function ($chapter) use ($story) {
                    return [
                        'id' => $chapter->id,
                        'story_id' => $story->id,
                        'title' => "第{$chapter->chapter_number}章: {$chapter->chapter_title}",
                        'chapter_number' => $chapter->chapter_number,
                        'chapter_title' => $chapter->chapter_title,
                        'type' => 'chapter',
                    ];
                })->toArray();
            }

            return $storyNode;
        });

        return response()->json([
            'success' => true,
            'data' => $tree,
        ]);
    }

    /**
     * Get chapters for a specific story
     */
    public function getChapters(string $id): JsonResponse
    {
        $story = Story::with('chapters')->find($id);

        if (!$story) {
            return response()->json([
                'success' => false,
                'message' => '故事不存在',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $story->chapters->sortBy('chapter_number')->values(),
        ]);
    }
}
