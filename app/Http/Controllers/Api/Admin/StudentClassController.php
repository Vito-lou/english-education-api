<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentClass;
use App\Models\ClassModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentClassController extends Controller
{
    /**
     * 获取班级学员列表
     */
    public function index(Request $request): JsonResponse
    {
        $query = StudentClass::with(['student', 'class'])
            ->whereHas('class', function ($q) {
                $q->where('institution_id', auth()->user()->institution_id);
            });

        // 按班级筛选
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // 按状态筛选
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 搜索学员
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('parent_name', 'like', "%{$search}%")
                  ->orWhere('parent_phone', 'like', "%{$search}%");
            });
        }

        // 排序
        $query->orderBy('enrollment_date', 'desc');

        $studentClasses = $query->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $studentClasses,
        ]);
    }

    /**
     * 创建学员班级记录（学员分班）
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
            'enrollment_date' => 'required|date',
        ]);

        try {
            // 检查班级是否属于同一机构
            $class = ClassModel::find($validated['class_id']);
            if ($class->institution_id !== auth()->user()->institution_id) {
                return response()->json([
                    'code' => 403,
                    'message' => '无权操作该班级',
                ], 403);
            }

            // 检查学员是否属于同一机构
            $student = \App\Models\Student::find($validated['student_id']);
            if ($student->institution_id !== auth()->user()->institution_id) {
                return response()->json([
                    'code' => 403,
                    'message' => '无权操作该学员',
                ], 403);
            }

            // 检查班级是否还有容量
            if (!$class->canAddStudent()) {
                return response()->json([
                    'code' => 400,
                    'message' => '班级已满或状态不允许添加学员',
                ], 400);
            }

            // 检查学员是否已在该班级
            $existingRecord = StudentClass::where('student_id', $validated['student_id'])
                ->where('class_id', $validated['class_id'])
                ->where('status', 'active')
                ->first();

            if ($existingRecord) {
                return response()->json([
                    'code' => 400,
                    'message' => '学员已在该班级中',
                ], 400);
            }

            // 创建学员班级记录
            $studentClass = StudentClass::create([
                'student_id' => $validated['student_id'],
                'class_id' => $validated['class_id'],
                'enrollment_date' => $validated['enrollment_date'],
                'status' => 'active',
            ]);

            $studentClass->load(['student', 'class']);

            return response()->json([
                'code' => 200,
                'message' => '学员分班成功',
                'data' => $studentClass,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '分班失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 获取学员班级记录详情
     */
    public function show(StudentClass $studentClass): JsonResponse
    {
        // 检查权限：只能查看同机构的记录
        if ($studentClass->class->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问该记录',
            ], 403);
        }

        $studentClass->load(['student', 'class']);

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $studentClass,
        ]);
    }

    /**
     * 更新学员班级记录
     */
    public function update(Request $request, StudentClass $studentClass): JsonResponse
    {
        // 检查权限：只能操作同机构的记录
        if ($studentClass->class->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作该记录',
            ], 403);
        }

        $validated = $request->validate([
            'enrollment_date' => 'sometimes|date',
            'status' => 'sometimes|in:active,graduated,dropped',
        ]);

        try {
            $studentClass->update($validated);
            $studentClass->load(['student', 'class']);

            return response()->json([
                'code' => 200,
                'message' => '更新成功',
                'data' => $studentClass,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '更新失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 删除学员班级记录（移出班级）
     */
    public function destroy(StudentClass $studentClass): JsonResponse
    {
        // 检查权限：只能操作同机构的记录
        if ($studentClass->class->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作该记录',
            ], 403);
        }

        try {
            // 更新状态为退班，而不是物理删除
            $studentClass->update(['status' => 'dropped']);

            return response()->json([
                'code' => 200,
                'message' => '学员已移出班级',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => '操作失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 学员转班
     */
    public function transfer(Request $request, StudentClass $studentClass): JsonResponse
    {
        // 检查权限：只能操作同机构的记录
        if ($studentClass->class->institution_id !== auth()->user()->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权操作该记录',
            ], 403);
        }

        $validated = $request->validate([
            'to_class_id' => 'required|exists:classes,id',
            'transfer_date' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            // 检查目标班级
            $toClass = ClassModel::find($validated['to_class_id']);
            if ($toClass->institution_id !== auth()->user()->institution_id) {
                return response()->json([
                    'code' => 403,
                    'message' => '无权操作目标班级',
                ], 403);
            }

            if (!$toClass->canAddStudent()) {
                return response()->json([
                    'code' => 400,
                    'message' => '目标班级已满或状态不允许添加学员',
                ], 400);
            }

            // 结束当前班级记录
            $studentClass->update(['status' => 'transferred']);

            // 创建新班级记录
            $newRecord = StudentClass::create([
                'student_id' => $studentClass->student_id,
                'class_id' => $validated['to_class_id'],
                'enrollment_date' => $validated['transfer_date'],
                'status' => 'active',
            ]);

            DB::commit();

            $newRecord->load(['student', 'class']);

            return response()->json([
                'code' => 200,
                'message' => '学员转班成功',
                'data' => $newRecord,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'code' => 500,
                'message' => '转班失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
