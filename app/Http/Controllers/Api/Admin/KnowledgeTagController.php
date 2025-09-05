<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeTag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class KnowledgeTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = KnowledgeTag::with(['knowledgePoints']);

        // 搜索
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('tag_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 标签体系筛选
        if ($request->filled('tag_system')) {
            $query->where('tag_system', $request->get('tag_system'));
        }

        $tags = $query->orderBy('tag_system')
                     ->orderBy('tag_name')
                     ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tags->items(),
            'pagination' => [
                'current_page' => $tags->currentPage(),
                'last_page' => $tags->lastPage(),
                'per_page' => $tags->perPage(),
                'total' => $tags->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tag_name' => 'required|string|max:100',
            'tag_system' => 'required|in:k12,cambridge,ielts,toefl',
            'description' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        // 检查标签名称在同一体系下是否唯一
        $exists = KnowledgeTag::where('tag_name', $validated['tag_name'])
                             ->where('tag_system', $validated['tag_system'])
                             ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '该标签在当前体系下已存在',
            ], 422);
        }

        try {
            $tag = KnowledgeTag::create($validated);

            return response()->json([
                'success' => true,
                'message' => '标签创建成功',
                'data' => $tag,
            ], 201);

        } catch (\Exception $e) {
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
        $tag = KnowledgeTag::with(['knowledgePoints'])->find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => '标签不存在',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $tag,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tag = KnowledgeTag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => '标签不存在',
            ], 404);
        }

        $validated = $request->validate([
            'tag_name' => 'required|string|max:100',
            'tag_system' => 'required|in:k12,cambridge,ielts,toefl',
            'description' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        // 检查标签名称在同一体系下是否唯一（排除当前记录）
        $exists = KnowledgeTag::where('tag_name', $validated['tag_name'])
                             ->where('tag_system', $validated['tag_system'])
                             ->where('id', '!=', $id)
                             ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '该标签在当前体系下已存在',
            ], 422);
        }

        try {
            $tag->update($validated);

            return response()->json([
                'success' => true,
                'message' => '标签更新成功',
                'data' => $tag,
            ]);

        } catch (\Exception $e) {
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
        $tag = KnowledgeTag::find($id);

        if (!$tag) {
            return response()->json([
                'success' => false,
                'message' => '标签不存在',
            ], 404);
        }

        try {
            $tag->delete();

            return response()->json([
                'success' => true,
                'message' => '标签删除成功',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取标签体系列表
     */
    public function getSystems(): JsonResponse
    {
        $systems = [
            ['value' => 'k12', 'label' => 'K12教育'],
            ['value' => 'cambridge', 'label' => '剑桥英语'],
            ['value' => 'ielts', 'label' => '雅思'],
            ['value' => 'toefl', 'label' => '托福'],
        ];

        return response()->json([
            'success' => true,
            'data' => $systems,
        ]);
    }

    /**
     * 按体系获取标签列表（用于下拉选择）
     */
    public function getBySystem(Request $request): JsonResponse
    {
        $system = $request->get('system');

        $query = KnowledgeTag::select(['id', 'tag_name', 'tag_system', 'description']);

        if ($system) {
            $query->where('tag_system', $system);
        }

        $tags = $query->orderBy('tag_name')->get();

        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }

    /**
     * 批量创建标签
     */
    public function batchCreate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tag_system' => 'required|in:k12,cambridge,ielts,toefl',
            'tags' => 'required|array|min:1',
            'tags.*.tag_name' => 'required|string|max:100',
            'tags.*.description' => 'nullable|string',
            'tags.*.meta' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $created = [];
            $errors = [];

            foreach ($validated['tags'] as $index => $tagData) {
                // 检查是否已存在
                $exists = KnowledgeTag::where('tag_name', $tagData['tag_name'])
                                     ->where('tag_system', $validated['tag_system'])
                                     ->exists();

                if ($exists) {
                    $errors[] = [
                        'index' => $index,
                        'tag_name' => $tagData['tag_name'],
                        'error' => '标签已存在',
                    ];
                    continue;
                }

                try {
                    $tag = KnowledgeTag::create([
                        'tag_name' => $tagData['tag_name'],
                        'tag_system' => $validated['tag_system'],
                        'description' => $tagData['description'] ?? null,
                        'meta' => $tagData['meta'] ?? null,
                    ]);
                    $created[] = $tag;
                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'tag_name' => $tagData['tag_name'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '批量创建完成',
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
                'message' => '批量创建失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
