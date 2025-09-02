<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeworkSubmission;
use App\Models\HomeworkAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeworkSubmissionController extends Controller
{
    /**
     * 获取作业提交列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = HomeworkSubmission::with([
            'homeworkAssignment.class',
            'student',
            'grader'
        ])->whereHas('homeworkAssignment', function ($q) use ($user) {
            $q->where('institution_id', $user->institution_id);
        });

        // 筛选条件
        if ($request->filled('homework_assignment_id')) {
            $query->byHomework($request->get('homework_assignment_id'));
        }

        if ($request->filled('student_id')) {
            $query->byStudent($request->get('student_id'));
        }

        if ($request->filled('status')) {
            $query->byStatus($request->get('status'));
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
     * 学生提交作业
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'homework_assignment_id' => 'required|exists:homework_assignments,id',
            'student_id' => 'required|exists:students,id',
            'content' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf,doc,docx|max:20480',
        ]);

        // 检查作业是否存在且属于当前机构
        $assignment = HomeworkAssignment::where('id', $validated['homework_assignment_id'])
            ->where('institution_id', Auth::user()->institution_id)
            ->first();

        if (!$assignment) {
            return response()->json([
                'code' => 404,
                'message' => '作业不存在',
            ], 404);
        }

        // 检查是否已经提交过
        $existingSubmission = HomeworkSubmission::where('homework_assignment_id', $validated['homework_assignment_id'])
            ->where('student_id', $validated['student_id'])
            ->first();

        if ($existingSubmission) {
            return response()->json([
                'code' => 400,
                'message' => '该学生已提交过作业',
            ], 400);
        }

        // 处理文件上传
        $attachmentData = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('homework-submissions', 'public');
                $attachmentData[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        // 判断是否迟交
        $isLate = now() > $assignment->due_date;
        $status = $isLate ? 'late' : 'submitted';

        $submission = HomeworkSubmission::create([
            'homework_assignment_id' => $validated['homework_assignment_id'],
            'student_id' => $validated['student_id'],
            'content' => $validated['content'],
            'attachments' => $attachmentData,
            'status' => $status,
            'submitted_at' => now(),
        ]);

        $submission->load(['homeworkAssignment', 'student']);

        return response()->json([
            'code' => 200,
            'message' => '提交成功',
            'data' => $submission,
        ]);
    }

    /**
     * 获取作业提交详情
     */
    public function show(string $id): JsonResponse
    {
        $submission = HomeworkSubmission::with([
            'homeworkAssignment.class',
            'student',
            'grader'
        ])->find($id);

        if (!$submission) {
            return response()->json([
                'code' => 404,
                'message' => '提交记录不存在',
            ], 404);
        }

        // 权限检查
        if ($submission->homeworkAssignment->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问',
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'message' => '获取成功',
            'data' => $submission,
        ]);
    }

    /**
     * 教师批改作业
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $submission = HomeworkSubmission::with('homeworkAssignment')->find($id);

        if (!$submission) {
            return response()->json([
                'code' => 404,
                'message' => '提交记录不存在',
            ], 404);
        }

        // 权限检查
        if ($submission->homeworkAssignment->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        $validated = $request->validate([
            'score' => 'nullable|numeric|min:0|max:' . $submission->max_score,
            'teacher_feedback' => 'nullable|string',
            'status' => 'in:submitted,late,graded',
        ]);

        $updateData = [];

        if (isset($validated['score'])) {
            $updateData['score'] = $validated['score'];
        }

        if (isset($validated['teacher_feedback'])) {
            $updateData['teacher_feedback'] = $validated['teacher_feedback'];
        }

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        // 如果设置了分数或反馈，标记为已批改
        if (isset($validated['score']) || isset($validated['teacher_feedback'])) {
            $updateData['status'] = 'graded';
            $updateData['graded_at'] = now();
            $updateData['graded_by'] = Auth::id();
        }

        $submission->update($updateData);
        $submission->load(['homeworkAssignment', 'student', 'grader']);

        return response()->json([
            'code' => 200,
            'message' => '批改成功',
            'data' => $submission,
        ]);
    }

    /**
     * 删除作业提交
     */
    public function destroy(string $id): JsonResponse
    {
        $submission = HomeworkSubmission::with('homeworkAssignment')->find($id);

        if (!$submission) {
            return response()->json([
                'code' => 404,
                'message' => '提交记录不存在',
            ], 404);
        }

        // 权限检查
        if ($submission->homeworkAssignment->institution_id !== Auth::user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作',
            ], 403);
        }

        // 删除相关附件文件
        if ($submission->attachments) {
            foreach ($submission->attachments as $attachment) {
                Storage::disk('public')->delete($attachment['path']);
            }
        }

        $submission->delete();

        return response()->json([
            'code' => 200,
            'message' => '删除成功',
        ]);
    }
}
