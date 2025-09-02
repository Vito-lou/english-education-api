<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKnowledgePoint;
use App\Models\CourseUnit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UnitKnowledgePointController extends Controller
{
    /**
     * 获取单元知识点列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = UnitKnowledgePoint::with(['unit.course'])
            ->whereHas('unit.course', function ($q) use ($user) {
                $q->where('institution_id', $user->institution_id);
            });

        // 筛选条件
        if ($request->filled('unit_id')) {
            $query->byUnit($request->get('unit_id'));
        }

        if ($request->filled('type')) {
            $query->byType($request->get('type'));
        }

        if ($request->filled('status')) {
            $query->byStatus($request->get('status'));
        }

        $knowledgePoints = $query->orderBy('sort_order')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $knowledgePoints,
        ]);
    }

    /**
     * 创建知识点
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unit_id' => 'required|exists:course_units,id',
            'type' => 'required|in:vocabulary,sentence_pattern,grammar',
            'content' => 'required|string|max:255',
            'explanation' => 'nullable|string',
            'example_sentences' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'in:active,inactive',
        ]);

        // 验证单元是否属于当前机构
        $unit = CourseUnit::with('course')
            ->where('id', $validated['unit_id'])
            ->first();

        if (!$unit || $unit->course->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 404,
                'message' => '单元不存在或无权访问',
            ], 404);
        }

        // 处理图片上传
        $imageUrl = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('knowledge-points/images', 'public');
            $imageUrl = Storage::url($imagePath);
        }

        $knowledgePoint = UnitKnowledgePoint::create([
            ...$validated,
            'image_url' => $imageUrl,
            'status' => $validated['status'] ?? 'active',
        ]);

        $knowledgePoint->load(['unit.course']);

        return response()->json([
            'code' => 200,
            'message' => '创建成功',
            'data' => $knowledgePoint,
        ]);
    }

    /**
     * 获取知识点详情
     */
    public function show(string $id): JsonResponse
    {
        $knowledgePoint = UnitKnowledgePoint::with(['unit.course'])
            ->find($id);

        if (!$knowledgePoint) {
            return response()->json([
                'code' => 404,
                'message' => '知识点不存在',
            ], 404);
        }

        // 权限检查
        if ($knowledgePoint->unit->course->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $knowledgePoint,
        ]);
    }

    /**
     * 更新知识点
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $knowledgePoint = UnitKnowledgePoint::with(['unit.course'])
            ->find($id);

        if (!$knowledgePoint) {
            return response()->json([
                'code' => 404,
                'message' => '知识点不存在',
            ], 404);
        }

        // 权限检查
        if ($knowledgePoint->unit->course->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:vocabulary,sentence_pattern,grammar',
            'content' => 'required|string|max:255',
            'explanation' => 'nullable|string',
            'example_sentences' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'status' => 'in:active,inactive',
        ]);

        // 处理图片上传
        if ($request->hasFile('image')) {
            // 删除旧图片文件
            if ($knowledgePoint->image_url) {
                $oldPath = str_replace('/storage/', '', $knowledgePoint->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $imagePath = $request->file('image')->store('knowledge-points/images', 'public');
            $validated['image_url'] = Storage::url($imagePath);
        }

        $knowledgePoint->update($validated);
        $knowledgePoint->load(['unit.course']);

        return response()->json([
            'code' => 200,
            'message' => '更新成功',
            'data' => $knowledgePoint,
        ]);
    }

    /**
     * 删除知识点
     */
    public function destroy(string $id): JsonResponse
    {
        $knowledgePoint = UnitKnowledgePoint::with(['unit.course'])
            ->find($id);

        if (!$knowledgePoint) {
            return response()->json([
                'code' => 404,
                'message' => '知识点不存在',
            ], 404);
        }

        // 权限检查
        if ($knowledgePoint->unit->course->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        // 删除相关文件
        if ($knowledgePoint->image_url) {
            $imagePath = str_replace('/storage/', '', $knowledgePoint->image_url);
            Storage::disk('public')->delete($imagePath);
        }

        $knowledgePoint->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }

    /**
     * 批量排序
     */
    public function updateSort(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:unit_knowledge_points,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            UnitKnowledgePoint::where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json([
            'code' => 200,
            'message' => '排序更新成功',
        ]);
    }
}
