<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgePoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KnowledgePointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = KnowledgePoint::with(['tags', 'stories', 'examples']);

        // 搜索
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('definition_en', 'like', "%{$search}%")
                  ->orWhere('definition_cn', 'like', "%{$search}%")
                  ->orWhereHas('examples', function ($eq) use ($search) {
                      $eq->where('example_en', 'like', "%{$search}%")
                         ->orWhere('example_cn', 'like', "%{$search}%");
                  });
            });
        }

        // 类型筛选
        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        // 标签筛选
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('knowledge_tags.id', $request->get('tag_id'));
            });
        }

        $knowledgePoints = $query->orderBy('created_at', 'desc')
                                ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $knowledgePoints->items(),
            'pagination' => [
                'current_page' => $knowledgePoints->currentPage(),
                'last_page' => $knowledgePoints->lastPage(),
                'per_page' => $knowledgePoints->perPage(),
                'total' => $knowledgePoints->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:vocabulary,grammar,phrase,sentence_pattern',
            'definition_en' => 'nullable|string',
            'definition_cn' => 'nullable|string',
            'explanation' => 'nullable|string',
            'examples' => 'nullable|array',
            'examples.*.example_en' => 'required_with:examples|string',
            'examples.*.example_cn' => 'nullable|string',
            'examples.*.sequence' => 'nullable|integer|min:0',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:knowledge_tags,id',
        ]);

        DB::beginTransaction();
        try {
            $knowledgePoint = KnowledgePoint::create([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'definition_en' => $validated['definition_en'] ?? null,
                'definition_cn' => $validated['definition_cn'] ?? null,
                'explanation' => $validated['explanation'] ?? null,
            ]);

            // 创建例句
            if (!empty($validated['examples'])) {
                foreach ($validated['examples'] as $index => $example) {
                    $knowledgePoint->examples()->create([
                        'example_en' => $example['example_en'],
                        'example_cn' => $example['example_cn'] ?? null,
                        'sequence' => $example['sequence'] ?? $index,
                    ]);
                }
            }

            // 关联标签
            if (!empty($validated['tag_ids'])) {
                $knowledgePoint->tags()->attach($validated['tag_ids']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '知识点创建成功',
                'data' => $knowledgePoint->load(['tags', 'examples']),
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
        $knowledgePoint = KnowledgePoint::with(['tags', 'stories', 'examples'])->find($id);

        if (!$knowledgePoint) {
            return response()->json([
                'success' => false,
                'message' => '知识点不存在',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $knowledgePoint,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $knowledgePoint = KnowledgePoint::find($id);

        if (!$knowledgePoint) {
            return response()->json([
                'success' => false,
                'message' => '知识点不存在',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:vocabulary,grammar,phrase,sentence_pattern',
            'definition_en' => 'nullable|string',
            'definition_cn' => 'nullable|string',
            'explanation' => 'nullable|string',
            'examples' => 'nullable|array',
            'examples.*.example_en' => 'required_with:examples|string',
            'examples.*.example_cn' => 'nullable|string',
            'examples.*.sequence' => 'nullable|integer|min:0',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:knowledge_tags,id',
        ]);

        DB::beginTransaction();
        try {
            $knowledgePoint->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'definition_en' => $validated['definition_en'] ?? null,
                'definition_cn' => $validated['definition_cn'] ?? null,
                'explanation' => $validated['explanation'] ?? null,
            ]);

            // 更新例句（先删除旧的，再创建新的）
            $knowledgePoint->examples()->delete();
            if (!empty($validated['examples'])) {
                foreach ($validated['examples'] as $index => $example) {
                    $knowledgePoint->examples()->create([
                        'example_en' => $example['example_en'],
                        'example_cn' => $example['example_cn'] ?? null,
                        'sequence' => $example['sequence'] ?? $index,
                    ]);
                }
            }

            // 更新标签关联
            if (isset($validated['tag_ids'])) {
                $knowledgePoint->tags()->sync($validated['tag_ids']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '知识点更新成功',
                'data' => $knowledgePoint->load(['tags', 'examples']),
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
        $knowledgePoint = KnowledgePoint::find($id);

        if (!$knowledgePoint) {
            return response()->json([
                'success' => false,
                'message' => '知识点不存在',
            ], 404);
        }

        try {
            $knowledgePoint->delete();

            return response()->json([
                'success' => true,
                'message' => '知识点删除成功',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取知识点类型列表
     */
    public function getTypes(): JsonResponse
    {
        $types = [
            ['value' => 'vocabulary', 'label' => '词汇'],
            ['value' => 'grammar', 'label' => '语法'],
            ['value' => 'phrase', 'label' => '短语'],
            ['value' => 'sentence_pattern', 'label' => '句型'],
        ];

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * 批量导入知识点
     */
    public function batchImport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'knowledge_points' => 'required|array|min:1',
            'knowledge_points.*.name' => 'required|string|max:255',
            'knowledge_points.*.type' => 'required|in:vocabulary,grammar,phrase,sentence_pattern',
            'knowledge_points.*.definition_en' => 'nullable|string',
            'knowledge_points.*.definition_cn' => 'nullable|string',
            'knowledge_points.*.explanation' => 'nullable|string',
            'knowledge_points.*.example_sentence' => 'nullable|string',
            'knowledge_points.*.audio_url' => 'nullable|url|max:255',
        ]);

        DB::beginTransaction();
        try {
            $created = [];
            $errors = [];

            foreach ($validated['knowledge_points'] as $index => $pointData) {
                try {
                    $knowledgePoint = KnowledgePoint::create($pointData);
                    $created[] = $knowledgePoint;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'name' => $pointData['name'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '批量导入完成',
                'data' => [
                    'created_count' => count($created),
                    'error_count' => count($errors),
                    'errors' => $errors,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '批量导入失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
