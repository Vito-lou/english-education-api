<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeworkAssignment;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            'due_date' => 'required|date|after:now',
            'requirements' => 'required|string',
            'status' => 'in:active,draft',
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

        $assignment = HomeworkAssignment::create([
            'title' => $validated['title'],
            'class_id' => $validated['class_id'],
            'due_date' => $validated['due_date'],
            'requirements' => $validated['requirements'],
            'attachments' => $attachmentData,
            'status' => $validated['status'] ?? 'active',
            'created_by' => Auth::id(),
            'institution_id' => Auth::user()->institution_id,
        ]);

        $assignment->load([
            'class.course',
            'class.level',
            'class.teacher',
            'creator'
        ]);

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
            'due_date' => 'required|date',
            'requirements' => 'required|string',
            'status' => 'in:active,draft,expired',
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

        $assignment->update([
            'title' => $validated['title'],
            'class_id' => $validated['class_id'],
            'due_date' => $validated['due_date'],
            'requirements' => $validated['requirements'],
            'attachments' => $currentAttachments,
            'status' => $validated['status'] ?? $assignment->status,
        ]);

        $assignment->load([
            'class.course',
            'class.level',
            'class.teacher',
            'creator'
        ]);

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
}
