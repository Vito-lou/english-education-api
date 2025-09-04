<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TimeSlotController extends Controller
{
    /**
     * 获取时间段列表
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = TimeSlot::byInstitution($user->institution_id)
            ->with('institution');

        // 筛选条件
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // 排序
        $timeSlots = $query->ordered()->get();

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => $timeSlots->map(function ($timeSlot) {
                return [
                    'id' => $timeSlot->id,
                    'name' => $timeSlot->name,
                    'start_time' => substr($timeSlot->start_time, 0, 5),
                    'end_time' => substr($timeSlot->end_time, 0, 5),
                    'time_range' => $timeSlot->time_range,
                    'display_name' => $timeSlot->display_name,
                    'duration_minutes' => $timeSlot->duration_minutes,
                    'is_active' => $timeSlot->is_active,
                    'sort_order' => $timeSlot->sort_order,
                    'created_at' => $timeSlot->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * 创建时间段
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // 计算时长
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time']);
        $endTime = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time']);
        $durationMinutes = $endTime->diffInMinutes($startTime);

        $timeSlot = TimeSlot::create([
            'institution_id' => $user->institution_id,
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'duration_minutes' => $durationMinutes,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'code' => 200,
            'message' => '时间段创建成功',
            'data' => [
                'id' => $timeSlot->id,
                'name' => $timeSlot->name,
                'start_time' => substr($timeSlot->start_time, 0, 5),
                'end_time' => substr($timeSlot->end_time, 0, 5),
                'time_range' => $timeSlot->time_range,
                'display_name' => $timeSlot->display_name,
                'duration_minutes' => $timeSlot->duration_minutes,
                'is_active' => $timeSlot->is_active,
                'sort_order' => $timeSlot->sort_order,
            ],
        ]);
    }

    /**
     * 获取时间段详情
     */
    public function show(TimeSlot $timeSlot): JsonResponse
    {
        $user = Auth::user();

        // 权限检查
        if ($timeSlot->institution_id !== $user->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权访问该时间段',
            ], 403);
        }

        return response()->json([
            'code' => 200,
            'message' => 'success',
            'data' => [
                'id' => $timeSlot->id,
                'name' => $timeSlot->name,
                'start_time' => substr($timeSlot->start_time, 0, 5),
                'end_time' => substr($timeSlot->end_time, 0, 5),
                'time_range' => $timeSlot->time_range,
                'display_name' => $timeSlot->display_name,
                'duration_minutes' => $timeSlot->duration_minutes,
                'is_active' => $timeSlot->is_active,
                'sort_order' => $timeSlot->sort_order,
                'created_at' => $timeSlot->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $timeSlot->updated_at?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * 更新时间段
     */
    public function update(Request $request, TimeSlot $timeSlot): JsonResponse
    {
        $user = Auth::user();

        // 权限检查
        if ($timeSlot->institution_id !== $user->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权修改该时间段',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:50',
            'start_time' => 'date_format:H:i',
            'end_time' => 'date_format:H:i|after:start_time',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        // 如果更新了时间，重新计算时长
        if (isset($validated['start_time']) || isset($validated['end_time'])) {
            $startTime = \Carbon\Carbon::createFromFormat('H:i', $validated['start_time'] ?? substr($timeSlot->start_time, 0, 5));
            $endTime = \Carbon\Carbon::createFromFormat('H:i', $validated['end_time'] ?? substr($timeSlot->end_time, 0, 5));
            $validated['duration_minutes'] = $endTime->diffInMinutes($startTime);
        }

        $timeSlot->update($validated);

        return response()->json([
            'code' => 200,
            'message' => '时间段更新成功',
            'data' => [
                'id' => $timeSlot->id,
                'name' => $timeSlot->name,
                'start_time' => substr($timeSlot->start_time, 0, 5),
                'end_time' => substr($timeSlot->end_time, 0, 5),
                'time_range' => $timeSlot->time_range,
                'display_name' => $timeSlot->display_name,
                'duration_minutes' => $timeSlot->duration_minutes,
                'is_active' => $timeSlot->is_active,
                'sort_order' => $timeSlot->sort_order,
            ],
        ]);
    }

    /**
     * 删除时间段
     */
    public function destroy(TimeSlot $timeSlot): JsonResponse
    {
        $user = Auth::user();

        // 权限检查
        if ($timeSlot->institution_id !== $user->institution_id) {
            return response()->json([
                'code' => 403,
                'message' => '无权删除该时间段',
            ], 403);
        }

        // 检查是否有关联的排课记录
        if ($timeSlot->classSchedules()->exists()) {
            return response()->json([
                'code' => 400,
                'message' => '该时间段已有排课记录，无法删除',
            ], 400);
        }

        $timeSlot->delete();

        return response()->json([
            'code' => 200,
            'message' => '时间段删除成功',
        ]);
    }
}
