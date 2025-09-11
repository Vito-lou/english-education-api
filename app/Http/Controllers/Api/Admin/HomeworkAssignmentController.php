<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeworkAssignment;
use App\Models\ClassModel;
use App\Models\CourseUnit;
use App\Models\UnitKnowledgePoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class HomeworkAssignmentController extends Controller
{
    /**
     * 获取作业列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = HomeworkAssignment::with([
            'class.course',
            'class.level',
            'class.teacher',
            'unit.course',
            'storyKnowledgePoints',
            'creator',
            'submissions'
        ])->forInstitution($user->institution_id);

        // 筛选条件
        if ($request->filled('class_id')) {
            $query->byClass($request->get('class_id'));
        }

        if ($request->filled('status')) {
            $query->byStatus($request->get('status'));
        }

        if ($request->filled('search')) {
            $query->search($request->get('search'));
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->get('date_to'));
        }

        $assignments = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        // 添加提交统计信息
        $assignments->getCollection()->transform(function ($assignment) {
            $assignment->submission_stats = $assignment->submission_stats;
            return $assignment;
        });

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
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'unit_id' => 'nullable|exists:course_units,id',
            'due_date' => 'required|date|after:now',
            'requirements' => 'required|string',
            'status' => 'in:active,draft',
            'knowledge_point_ids' => 'nullable|array',
            'knowledge_point_ids.*' => 'exists:unit_knowledge_points,id',
            'story_knowledge_point_ids' => 'nullable|array',
            'story_knowledge_point_ids.*' => 'exists:knowledge_points,id',
        ]);

        // 单独验证文件上传（如果有的话）
        if ($request->hasFile('attachments')) {
            $request->validate([
                'attachments' => 'array',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480', // 20MB
            ]);
        }

        // 检查班级是否属于当前机构
        $class = ClassModel::where('id', $validated['class_id'])
            ->where('institution_id', Auth::user()->institution_id)
            ->first();

        if (!$class) {
            return response()->json([
                'code' => 404,
                'message' => '班级不存在或无权访问',
            ], 404);
        }

        // 处理文件上传
        $attachmentData = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('homework-attachments', 'public');
                $attachmentData[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        DB::beginTransaction();
        try {
            $assignment = HomeworkAssignment::create([
                'title' => $validated['title'],
                'class_id' => $validated['class_id'],
                'unit_id' => $validated['unit_id'] ?? null,
                'due_date' => $validated['due_date'],
                'requirements' => $validated['requirements'],
                'attachments' => $attachmentData,
                'status' => $validated['status'] ?? 'active',
                'created_by' => Auth::id(),
                'institution_id' => Auth::user()->institution_id,
            ]);

            // 关联故事知识点
            if (!empty($validated['story_knowledge_point_ids'])) {
                $assignment->storyKnowledgePoints()->attach($validated['story_knowledge_point_ids']);
            }

            DB::commit();

            $assignment->load([
                'class.course',
                'class.level',
                'class.teacher',
                'unit.course',
                'storyKnowledgePoints',
                'creator'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '创建失败：' . $e->getMessage()
            ], 500);
        }

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
            'class.course',
            'class.level',
            'class.teacher',
            'creator',
            'submissions.student'
        ])->find($id);

        if (!$assignment) {
            return response()->json([
                'code' => 404,
                'message' => '作业不存在',
            ], 404);
        }

        // 权限检查
        if ($assignment->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        // 添加提交统计信息
        $assignment->submission_stats = $assignment->submission_stats;

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
        $assignment = HomeworkAssignment::find($id);

        if (!$assignment) {
            return response()->json([
                'code' => 404,
                'message' => '作业不存在',
            ], 404);
        }

        // 权限检查
        if ($assignment->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'unit_id' => 'nullable|exists:course_units,id',
            'due_date' => 'required|date',
            'requirements' => 'required|string',
            'status' => 'in:active,draft,expired',
            'knowledge_point_ids' => 'nullable|array',
            'knowledge_point_ids.*' => 'exists:unit_knowledge_points,id',
            'story_knowledge_point_ids' => 'nullable|array',
            'story_knowledge_point_ids.*' => 'exists:knowledge_points,id',
            'remove_attachments' => 'nullable|array', // 要删除的附件索引
        ]);

        // 单独验证文件上传（如果有的话）
        if ($request->hasFile('attachments')) {
            $request->validate([
                'attachments' => 'array',
                'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:20480',
            ]);
        }

        // 检查班级是否属于当前机构
        $class = ClassModel::where('id', $validated['class_id'])
            ->where('institution_id', Auth::user()->institution_id)
            ->first();

        if (!$class) {
            return response()->json([
                'code' => 404,
                'message' => '班级不存在或无权访问',
            ], 404);
        }

        // 处理附件更新
        $currentAttachments = $assignment->attachments ?? [];

        // 删除指定的附件
        if ($request->has('remove_attachments')) {
            foreach ($request->get('remove_attachments') as $index) {
                if (isset($currentAttachments[$index])) {
                    Storage::disk('public')->delete($currentAttachments[$index]['path']);
                    unset($currentAttachments[$index]);
                }
            }
            $currentAttachments = array_values($currentAttachments); // 重新索引
        }

        // 添加新附件
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('homework-attachments', 'public');
                $currentAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        DB::beginTransaction();
        try {
            $assignment->update([
                'title' => $validated['title'],
                'class_id' => $validated['class_id'],
                'unit_id' => $validated['unit_id'] ?? null,
                'due_date' => $validated['due_date'],
                'requirements' => $validated['requirements'],
                'attachments' => $currentAttachments,
                'status' => $validated['status'] ?? $assignment->status,
            ]);

            // 更新故事知识点关联
            if (isset($validated['story_knowledge_point_ids'])) {
                $assignment->storyKnowledgePoints()->sync($validated['story_knowledge_point_ids']);
            } else {
                $assignment->storyKnowledgePoints()->detach();
            }

            DB::commit();

            $assignment->load([
                'class.course',
                'class.level',
                'class.teacher',
                'unit.course',
                'storyKnowledgePoints',
                'creator'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '更新失败：' . $e->getMessage()
            ], 500);
        }

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
        $assignment = HomeworkAssignment::find($id);

        if (!$assignment) {
            return response()->json([
                'code' => 404,
                'message' => '作业不存在',
            ], 404);
        }

        // 权限检查
        if ($assignment->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        // 删除相关附件文件
        if ($assignment->attachments) {
            foreach ($assignment->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $assignment->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }

    /**
     * 获取班级列表（用于作业创建时选择）
     */
    public function getClasses(Request $request): JsonResponse
    {
        $user = Auth::user();

        $classes = ClassModel::with(['course', 'level', 'teacher'])
            ->forInstitution($user->institution_id)
            ->byStatus('active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $classes,
        ]);
    }

    /**
     * 获取作业提交列表
     */
    public function getSubmissions(Request $request, string $id): JsonResponse
    {
        $assignment = HomeworkAssignment::find($id);

        if (!$assignment) {
            return response()->json([
                'code' => 404,
                'message' => '作业不存在',
            ], 404);
        }

        // 权限检查
        if ($assignment->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        $query = $assignment->submissions()->with(['student']);

        // 筛选条件
        if ($request->filled('status')) {
            $query->byStatus($request->get('status'));
        }

        if ($request->filled('student_id')) {
            $query->byStudent($request->get('student_id'));
        }

        $submissions = $query->orderBy('submitted_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $submissions,
        ]);
    }

    /**
     * 根据班级获取可用的单元列表
     */
    public function getUnitsForClass(Request $request, string $classId): JsonResponse
    {
        $user = Auth::user();

        // 验证班级是否属于当前机构
        $class = ClassModel::with(['course', 'level'])
            ->where('id', $classId)
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$class) {
            return response()->json([
                'code' => 404,
                'message' => '班级不存在或无权访问',
            ], 404);
        }

        // 获取班级对应课程和级别的单元
        $query = CourseUnit::with(['story.knowledgePoints'])
            ->where('course_id', $class->course_id);

        if ($class->level_id) {
            $query->where('level_id', $class->level_id);
        }

        $units = $query->orderBy('sort_order')->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $units,
        ]);
    }

    /**
     * 获取单元的知识点列表，并显示历史布置情况
     */
    public function getKnowledgePointsForUnit(Request $request, string $unitId): JsonResponse
    {
        $user = Auth::user();
        $classId = $request->get('class_id');

        // 验证单元是否存在
        $unit = CourseUnit::with(['course', 'story.knowledgePoints'])
            ->where('id', $unitId)
            ->first();

        if (!$unit || $unit->course->institution_id !== $user->institution_id) {
            return response()->json([
                'code' => 404,
                'message' => '单元不存在或无权访问',
            ], 404);
        }

        // 获取知识点列表
        // 只使用关联故事的知识点
        $knowledgePoints = collect();
        $knowledgePointType = 'story'; // 标记知识点来源

        if ($unit->story && $unit->story->knowledgePoints->count() > 0) {
            $knowledgePoints = $unit->story->knowledgePoints;
        }

        // 如果提供了班级ID，查询该班级在此单元的历史作业中已布置的知识点
        $assignedKnowledgePointIds = [];
        if ($classId) {
            $assignedKnowledgePointIds = HomeworkAssignment::where('class_id', $classId)
                ->where('unit_id', $unitId)
                ->whereHas('storyKnowledgePoints')
                ->with('storyKnowledgePoints')
                ->get()
                ->pluck('storyKnowledgePoints')
                ->flatten()
                ->pluck('id')
                ->unique()
                ->toArray();
        }

        // 为每个知识点添加历史布置信息
        $knowledgePoints->transform(function ($point) use ($assignedKnowledgePointIds) {
            $point->previously_assigned = in_array($point->id, $assignedKnowledgePointIds);
            return $point;
        });

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => [
                'unit' => $unit,
                'knowledge_points' => $knowledgePoints,
                'knowledge_point_type' => $knowledgePointType, // 标记知识点来源
                'assigned_count' => count($assignedKnowledgePointIds),
                'total_count' => $knowledgePoints->count(),
            ],
        ]);
    }

    /**
     * 获取班级在指定单元的作业历史
     */
    public function getUnitHomeworkHistory(Request $request, string $classId, string $unitId): JsonResponse
    {
        $user = Auth::user();

        // 验证权限
        $class = ClassModel::where('id', $classId)
            ->where('institution_id', $user->institution_id)
            ->first();

        if (!$class) {
            return response()->json([
                'code' => 404,
                'message' => '班级不存在或无权访问',
            ], 404);
        }

        // 获取该班级在指定单元的作业历史
        $assignments = HomeworkAssignment::with(['storyKnowledgePoints', 'creator'])
            ->where('class_id', $classId)
            ->where('unit_id', $unitId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $assignments,
        ]);
    }
}
