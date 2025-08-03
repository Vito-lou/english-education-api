<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    /**
     * 获取完整的组织架构树
     */
    public function tree(): JsonResponse
    {
        try {
            $nodes = collect();

            // 获取所有机构
            $institutions = Institution::where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->map(function ($institution) {
                    return [
                        'id' => $institution->id,
                        'parent_id' => null,
                        'name' => $institution->name,
                        'code' => $institution->code,
                        'type' => 'institution',
                        'description' => $institution->description,
                        'manager_name' => null,
                        'manager_phone' => null,
                        'address' => $institution->address,
                        'capacity' => null,
                        'facilities' => null,
                        'sort_order' => $institution->sort_order,
                        'status' => $institution->status,
                        'created_at' => $institution->created_at,
                        'updated_at' => $institution->updated_at,
                        // 机构特有字段
                        'contact_person' => $institution->contact_person,
                        'contact_phone' => $institution->contact_phone,
                        'contact_email' => $institution->contact_email,
                        'business_license' => $institution->business_license,
                        'business_hours' => $institution->business_hours,
                        'settings' => $institution->settings,
                        'established_at' => $institution->established_at,
                    ];
                });

            // 获取所有部门
            $departments = Department::with('institution')
                ->orderBy('institution_id')
                ->orderBy('sort_order')
                ->get()
                ->map(function ($department) {
                    return [
                        'id' => $department->id + 10000, // 避免ID冲突
                        'parent_id' => $department->parent_id ? $department->parent_id + 10000 : $department->institution_id,
                        'name' => $department->name,
                        'code' => $department->code,
                        'type' => $department->type,
                        'description' => $department->description,
                        'manager_name' => $department->manager_name,
                        'manager_phone' => $department->manager_phone,
                        'address' => $department->address,
                        'capacity' => $department->capacity,
                        'facilities' => $department->facilities,
                        'sort_order' => $department->sort_order,
                        'status' => $department->status,
                        'created_at' => $department->created_at,
                        'updated_at' => $department->updated_at,
                        // 机构特有字段（部门不需要）
                        'contact_person' => null,
                        'contact_phone' => null,
                        'contact_email' => null,
                        'business_license' => null,
                        'business_hours' => null,
                        'settings' => null,
                        'established_at' => null,
                        // 原始ID用于编辑
                        'original_id' => $department->id,
                        'original_type' => 'department',
                    ];
                });

            // 合并数据
            $allNodes = $institutions->concat($departments);

            return response()->json([
                'success' => true,
                'message' => '获取组织架构树成功',
                'data' => $allNodes->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '获取组织架构树失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 创建组织节点
     */
    public function createNode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['institution', 'campus', 'department', 'classroom'])],
            'parent_id' => 'nullable|integer',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:50',
            'manager_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'facilities' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],

            // 机构特有字段
            'contact_person' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email',
            'business_license' => 'nullable|string|max:100',
            'business_hours' => 'nullable|array',
            'settings' => 'nullable|array',
            'established_at' => 'nullable|date',
        ]);

        try {
            if ($validated['type'] === 'institution') {
                // 创建机构
                $institution = Institution::create([
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'description' => $validated['description'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'contact_person' => $validated['contact_person'] ?? null,
                    'contact_phone' => $validated['contact_phone'] ?? null,
                    'contact_email' => $validated['contact_email'] ?? null,
                    'business_license' => $validated['business_license'] ?? null,
                    'business_hours' => $validated['business_hours'] ?? null,
                    'settings' => $validated['settings'] ?? null,
                    'established_at' => $validated['established_at'] ?? null,
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'status' => $validated['status'] ?? 'active',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => '机构创建成功',
                    'data' => $institution
                ], 201);
            } else {
                // 创建部门
                $parentId = $validated['parent_id'];
                $institutionId = null;

                // 确定机构ID
                if ($parentId) {
                    if ($parentId > 10000) {
                        // 父节点是部门
                        $parentDepartment = Department::find($parentId - 10000);
                        $institutionId = $parentDepartment->institution_id;
                        $parentId = $parentDepartment->id;
                    } else {
                        // 父节点是机构
                        $institutionId = $parentId;
                        $parentId = null;
                    }
                }

                $department = Department::create([
                    'institution_id' => $institutionId,
                    'parent_id' => $parentId,
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'type' => $validated['type'],
                    'description' => $validated['description'] ?? null,
                    'manager_name' => $validated['manager_name'] ?? null,
                    'manager_phone' => $validated['manager_phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'capacity' => $validated['capacity'] ?? null,
                    'facilities' => $validated['facilities'] ?? null,
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'status' => $validated['status'] ?? 'active',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => '部门创建成功',
                    'data' => $department
                ], 201);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '创建失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 更新组织节点
     */
    public function updateNode(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'code' => 'sometimes|required|string|max:50',
            'description' => 'nullable|string',
            'manager_name' => 'nullable|string|max:50',
            'manager_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'capacity' => 'nullable|integer|min:1',
            'facilities' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],

            // 机构特有字段
            'contact_person' => 'nullable|string|max:50',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email',
            'business_license' => 'nullable|string|max:100',
            'business_hours' => 'nullable|array',
            'settings' => 'nullable|array',
            'established_at' => 'nullable|date',
        ]);

        try {
            if ($id > 10000) {
                // 更新部门
                $department = Department::findOrFail($id - 10000);
                $department->update($validated);

                return response()->json([
                    'success' => true,
                    'message' => '部门更新成功',
                    'data' => $department
                ]);
            } else {
                // 更新机构
                $institution = Institution::findOrFail($id);
                $institution->update($validated);

                return response()->json([
                    'success' => true,
                    'message' => '机构更新成功',
                    'data' => $institution
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '更新失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 删除组织节点
     */
    public function deleteNode($id): JsonResponse
    {
        try {
            if ($id > 10000) {
                // 删除部门
                $department = Department::findOrFail($id - 10000);

                // 检查是否有子部门
                if ($department->children()->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => '该部门下还有子部门，无法删除'
                    ], 422);
                }

                $department->delete();

                return response()->json([
                    'success' => true,
                    'message' => '部门删除成功'
                ]);
            } else {
                // 删除机构
                $institution = Institution::findOrFail($id);

                // 检查是否有部门
                if ($institution->departments()->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => '该机构下还有部门，无法删除'
                    ], 422);
                }

                $institution->delete();

                return response()->json([
                    'success' => true,
                    'message' => '机构删除成功'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '删除失败: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 移动组织节点
     */
    public function moveNode(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            if ($id > 10000) {
                // 移动部门
                $department = Department::findOrFail($id - 10000);

                if (isset($validated['parent_id'])) {
                    $newParentId = $validated['parent_id'];
                    if ($newParentId > 10000) {
                        // 新父节点是部门
                        $department->parent_id = $newParentId - 10000;
                    } else {
                        // 新父节点是机构
                        $department->parent_id = null;
                        $department->institution_id = $newParentId;
                    }
                }

                if (isset($validated['sort_order'])) {
                    $department->sort_order = $validated['sort_order'];
                }

                $department->save();

                return response()->json([
                    'success' => true,
                    'message' => '部门移动成功',
                    'data' => $department
                ]);
            } else {
                // 机构只能调整排序
                $institution = Institution::findOrFail($id);

                if (isset($validated['sort_order'])) {
                    $institution->sort_order = $validated['sort_order'];
                    $institution->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => '机构排序更新成功',
                    'data' => $institution
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '移动失败: ' . $e->getMessage()
            ], 500);
        }
    }
}
