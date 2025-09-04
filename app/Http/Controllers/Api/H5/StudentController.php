<?php

namespace App\Http\Controllers\Api\H5;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * 根据姓名搜索学生
     */
    public function searchByName(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:1|max:50',
        ]);

        $name = $request->get('name');

        $students = Student::with(['user:id,name'])
            ->where(function ($query) use ($name) {
                $query->whereHas('user', function ($subQuery) use ($name) {
                    $subQuery->where('name', 'like', "%{$name}%");
                })
                ->orWhere('name', 'like', "%{$name}%");
            })
            ->limit(10)
            ->get(['id', 'user_id', 'name', 'student_type']);

        return response()->json([
            'success' => true,
            'message' => '搜索成功',
            'data' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? $student->name,
                    'student_id' => $student->id,
                    'current_level' => $student->student_type,
                    'user_id' => $student->user_id, // 添加调试信息
                    'user_name' => $student->user->name ?? null, // 添加调试信息
                ];
            }),
        ]);
    }

    /**
     * 获取学生详情
     */
    public function getDetail(Request $request, $id): JsonResponse
    {
        $student = Student::with(['user:id,name,avatar'])
            ->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'id' => $student->id,
                'name' => $student->user->name ?? $student->name,
                'student_id' => $student->id,
                'current_level' => $student->student_type,
            ],
        ]);
    }

    /**
     * 获取学生课时信息
     */
    public function getClassHours(Request $request, $id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        // 计算课时统计
        $usedLessons = (float) AttendanceRecord::where('student_id', $id)->sum('deducted_lessons');

        // 这里应该从学生的课时包或者其他地方获取总课时，暂时使用固定值
        $totalHours = 48; // 临时固定值，实际应该从数据库获取
        $remainingHours = max(0, $totalHours - $usedLessons);

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'remaining_hours' => (float) $remainingHours,
                'total_hours' => (int) $totalHours,
                'used_hours' => (float) $usedLessons,
            ],
        ]);
    }

    /**
     * 获取学生学习进度
     */
    public function getProgress(Request $request, $id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        // 这里应该计算实际的学习进度，暂时返回模拟数据
        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'current_level' => $student->student_type,
                'completed_stories' => 5,
                'total_stories' => 20,
                'current_story' => '当前学习故事',
            ],
        ]);
    }

    /**
     * 获取学生考勤记录
     */
    public function getAttendanceRecords(Request $request, $id): JsonResponse
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        $query = AttendanceRecord::where('student_id', $id)
            ->with(['schedule.course', 'schedule.teacher', 'schedule.timeSlot', 'recorder']);

        // 日期筛选
        if ($request->filled('date_from')) {
            $query->where(function ($q) use ($request) {
                // 有排课的记录按排课日期筛选
                $q->whereHas('schedule', function ($subQ) use ($request) {
                    $subQ->where('schedule_date', '>=', $request->get('date_from'));
                })
                // 手动点名记录按上课时间筛选
                ->orWhere(function ($subQ) use ($request) {
                    $subQ->where('record_type', 'manual')
                         ->whereDate('lesson_time', '>=', $request->get('date_from'));
                });
            });
        }

        if ($request->filled('date_to')) {
            $query->where(function ($q) use ($request) {
                // 有排课的记录按排课日期筛选
                $q->whereHas('schedule', function ($subQ) use ($request) {
                    $subQ->where('schedule_date', '<=', $request->get('date_to'));
                })
                // 手动点名记录按上课时间筛选
                ->orWhere(function ($subQ) use ($request) {
                    $subQ->where('record_type', 'manual')
                         ->whereDate('lesson_time', '<=', $request->get('date_to'));
                });
            });
        }

        $records = $query
            ->orderBy('lesson_time', 'desc') // 使用统一的上课时间字段排序
            ->orderBy('id', 'desc') // ID作为第二排序字段，确保排序稳定
            ->paginate($request->get('per_page', 10));

        // 格式化数据 - 使用统一的lesson_time字段
        $formattedRecords = $records->getCollection()->map(function ($record) {
            return [
                'id' => $record->id,
                'record_type' => $record->record_type,
                'schedule_date' => $record->lesson_time ? date('Y-m-d', strtotime($record->lesson_time)) : '',
                'time_range' => $record->lesson_time ? date('H:i', strtotime($record->lesson_time)) : '',
                'course_name' => $record->record_type === 'manual'
                    ? ($record->lesson_content ?: '手动补录课程')
                    : ($record->schedule && $record->schedule->course ? $record->schedule->course->name : ''),
                'teacher_name' => $record->record_type === 'manual'
                    ? ($record->recorder ? $record->recorder->name : '未知')
                    : ($record->schedule && $record->schedule->teacher ? $record->schedule->teacher->name : ''),
                'student_name' => $record->student->name,
                'attendance_status' => $record->attendance_status,
                'status_name' => $record->status_name,
                'deducted_lessons' => (float)$record->deducted_lessons,
                'teacher_notes' => $record->teacher_notes,
                'recorded_at' => $record->recorded_at->format('Y-m-d H:i:s'),
            ];
        });

        $records->setCollection($formattedRecords);

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $records->items(),
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'has_more' => $records->hasMorePages(),
            ],
        ]);
    }

    /**
     * 获取学生课时统计汇总（只返回统计信息，不包含记录）
     */
    public function getClassHoursSummary(Request $request, $id): JsonResponse
    {
        $student = Student::with(['user:id,name'])->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => '学生不存在',
            ], 404);
        }

        // 计算课时统计
        $usedLessons = (float) AttendanceRecord::where('student_id', $id)->sum('deducted_lessons');

        // 这里应该从学生的课时包获取总课时，暂时使用固定值
        $totalLessons = 48; // 临时固定值
        $remainingLessons = max(0, $totalLessons - $usedLessons);

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => [
                'id' => $student->id,
                'name' => $student->user->name ?? $student->name,
                'total_lessons' => (int) $totalLessons,
                'used_lessons' => (float) $usedLessons,
                'remaining_lessons' => (float) $remainingLessons,
            ],
        ]);
    }

    /**
     * 获取当前用户关联的学员信息
     */
    public function getMyStudents(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '用户未登录',
            ], 401);
        }

        // 查找用户关联的学员
        $students = Student::with(['user:id,name'])
            ->where('user_id', $user->id)
            ->orWhereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get(['id', 'user_id', 'name', 'student_type']);

        return response()->json([
            'success' => true,
            'message' => '获取成功',
            'data' => $students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name ?? $student->name,
                    'student_id' => $student->id,
                    'current_level' => $student->student_type,
                ];
            }),
        ]);
    }

    /**
     * 调试：获取所有学员信息
     */
    public function debug(): JsonResponse
    {
        $students = Student::with(['user:id,name,email'])
            ->get(['id', 'user_id', 'name', 'student_type', 'parent_name']);

        return response()->json([
            'success' => true,
            'message' => '调试信息',
            'data' => $students->map(function ($student) {
                return [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'parent_name' => $student->parent_name,
                    'student_type' => $student->student_type,
                    'user_id' => $student->user_id,
                    'user_name' => $student->user->name ?? null,
                    'user_email' => $student->user->email ?? null,
                ];
            }),
        ]);
    }
}
