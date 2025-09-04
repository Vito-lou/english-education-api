<?php

namespace App\Http\Controllers\Api\H5;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\StudentClass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HomeworkController extends Controller
{
    /**
     * 获取学生的课后作业列表
     * 根据学生ID查询其所在班级的所有作业
     */
    public function getStudentHomework(Request $request, $studentId): JsonResponse
    {
        $student = Student::find($studentId);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        // 获取学生所在的所有班级
        $classIds = StudentClass::where('student_id', $studentId)
            ->where('status', 'active')
            ->pluck('class_id')
            ->toArray();

        if (empty($classIds)) {
            return response()->json([
                'success' => true,
                'message' => '获取成功',
                'data' => [],
            ]);
        }

        // 查询这些班级的所有作业
        $query = HomeworkAssignment::whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->with([
                'class:id,name',
                'creator:id,name',
                'submissions' => function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                }
            ])
            ->orderBy('due_date', 'desc');

        // 状态筛选
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'pending') {
                // 待完成：没有提交记录的作业
                $query->whereDoesntHave('submissions', function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                });
            } elseif ($status === 'submitted') {
                // 已提交：有提交记录的作业
                $query->whereHas('submissions', function ($q) use ($studentId) {
                    $q->where('student_id', $studentId);
                });
            } elseif ($status === 'overdue') {
                // 已过期：截止时间已过且未提交的作业
                $query->where('due_date', '<', now())
                    ->whereDoesntHave('submissions', function ($q) use ($studentId) {
                        $q->where('student_id', $studentId);
                    });
            }
        }

        $homework = $query->paginate($request->get('per_page', 10));

        // 格式化数据
        $formattedHomework = $homework->getCollection()->map(function ($assignment) {
            $submission = $assignment->submissions->first();

            return [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'requirements' => $assignment->requirements,
                'due_date' => $assignment->due_date->format('Y-m-d H:i:s'),
                'due_date_formatted' => $assignment->due_date->format('m月d日 H:i'),
                'status' => $assignment->status,
                'is_expired' => $assignment->due_date < now(),
                'class' => [
                    'id' => $assignment->class->id,
                    'name' => $assignment->class->name,
                ],
                'creator' => [
                    'id' => $assignment->creator->id,
                    'name' => $assignment->creator->name,
                ],
                'submission' => $submission ? [
                    'id' => $submission->id,
                    'status' => $submission->status,
                    'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i:s'),
                    'score' => $submission->score,
                    'max_score' => $submission->max_score,
                    'teacher_feedback' => $submission->teacher_feedback,
                ] : null,
                'created_at' => $assignment->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $formattedHomework,
            'pagination' => [
                'current_page' => $homework->currentPage(),
                'last_page' => $homework->lastPage(),
                'per_page' => $homework->perPage(),
                'total' => $homework->total(),
            ],
        ]);
    }

    /**
     * 获取作业详情
     */
    public function getHomeworkDetail(Request $request, $homeworkId): JsonResponse
    {
        $homework = HomeworkAssignment::with([
            'class:id,name',
            'creator:id,name',
            'unit:id,name',
            'knowledgePoints:id,content,explanation'
        ])->find($homeworkId);

        if (!$homework) {
            return response()->json([
                'success' => false,
                'message' => '作业不存在',
            ], 404);
        }

        // 如果请求中包含学生ID，获取该学生的提交记录
        $submission = null;
        if ($request->filled('student_id')) {
            $submission = HomeworkSubmission::where('homework_assignment_id', $homeworkId)
                ->where('student_id', $request->get('student_id'))
                ->first();
        }

        // 处理附件URL
        $attachments = [];
        if ($homework->attachments) {
            foreach ($homework->attachments as $attachment) {
                $attachments[] = [
                    'name' => $attachment['name'],
                    'url' => Storage::url($attachment['path']),
                    'size' => $attachment['size'] ?? 0,
                    'type' => $attachment['type'] ?? '',
                ];
            }
        }

        $data = [
            'id' => $homework->id,
            'title' => $homework->title,
            'requirements' => $homework->requirements,
            'due_date' => $homework->due_date->format('Y-m-d H:i:s'),
            'due_date_formatted' => $homework->due_date->format('m月d日 H:i'),
            'status' => $homework->status,
            'is_expired' => $homework->due_date < now(),
            'attachments' => $attachments,
            'class' => [
                'id' => $homework->class->id,
                'name' => $homework->class->name,
            ],
            'creator' => [
                'id' => $homework->creator->id,
                'name' => $homework->creator->name,
            ],
            'unit' => $homework->unit ? [
                'id' => $homework->unit->id,
                'name' => $homework->unit->name,
            ] : null,
            'knowledge_points' => $homework->knowledgePoints->map(function ($point) {
                return [
                    'id' => $point->id,
                    'name' => $point->content,
                    'description' => $point->explanation,
                ];
            }),
            'submission' => $submission ? [
                'id' => $submission->id,
                'content' => $submission->content,
                'attachments' => $this->formatSubmissionAttachments($submission->attachments),
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->format('Y-m-d H:i:s'),
                'score' => $submission->score,
                'max_score' => $submission->max_score,
                'teacher_feedback' => $submission->teacher_feedback,
                'graded_at' => $submission->graded_at?->format('Y-m-d H:i:s'),
            ] : null,
            'created_at' => $homework->created_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $data,
        ]);
    }

    /**
     * 学生提交作业
     */
    public function submitHomework(Request $request, $homeworkId): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'content' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf,doc,docx|max:20480',
        ]);

        $homework = HomeworkAssignment::find($homeworkId);

        if (!$homework) {
            return response()->json([
                'success' => false,
                'message' => '作业不存在',
            ], 404);
        }

        // 检查作业是否已过期
        if ($homework->due_date < now()) {
            return response()->json([
                'success' => false,
                'message' => '作业已过期，无法提交',
            ], 400);
        }

        // 检查学生是否在该班级中
        $studentInClass = StudentClass::where('student_id', $validated['student_id'])
            ->where('class_id', $homework->class_id)
            ->where('status', 'active')
            ->exists();

        if (!$studentInClass) {
            return response()->json([
                'success' => false,
                'message' => '学生不在该班级中',
            ], 403);
        }

        // 处理文件上传
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('homework/submissions', 'public');
                $attachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                    'type' => $file->getMimeType(),
                ];
            }
        }

        // 检查是否已有提交记录
        $submission = HomeworkSubmission::where('homework_assignment_id', $homeworkId)
            ->where('student_id', $validated['student_id'])
            ->first();

        $isLate = $homework->due_date < now();

        if ($submission) {
            // 更新现有提交
            $submission->update([
                'content' => $validated['content'],
                'attachments' => $attachments,
                'status' => $isLate ? 'late' : 'submitted',
                'submitted_at' => now(),
            ]);
        } else {
            // 创建新提交
            $submission = HomeworkSubmission::create([
                'homework_assignment_id' => $homeworkId,
                'student_id' => $validated['student_id'],
                'content' => $validated['content'],
                'attachments' => $attachments,
                'status' => $isLate ? 'late' : 'submitted',
                'submitted_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => '提交成功',
            'data' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * 格式化提交附件
     */
    private function formatSubmissionAttachments($attachments): array
    {
        if (!$attachments) {
            return [];
        }

        return collect($attachments)->map(function ($attachment) {
            return [
                'name' => $attachment['name'],
                'url' => Storage::url($attachment['path']),
                'size' => $attachment['size'] ?? 0,
                'type' => $attachment['type'] ?? '',
            ];
        })->toArray();
    }
}
