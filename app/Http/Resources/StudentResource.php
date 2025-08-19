<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'age' => $this->age,
            'parent_name' => $this->parent_name,
            'parent_phone' => $this->parent_phone,
            'parent_relationship' => $this->parent_relationship,

            // 原始枚举值
            'student_type' => $this->student_type,
            'follow_up_status' => $this->follow_up_status,
            'intention_level' => $this->intention_level,
            'status' => $this->status,

            // 中文显示名称
            'student_type_name' => $this->student_type_name,
            'follow_up_status_name' => $this->follow_up_status_name,
            'intention_level_name' => $this->intention_level_name,

            'source' => $this->source,
            'remarks' => $this->remarks,
            'institution_id' => $this->institution_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // 关联数据
            'user' => $this->whenLoaded('user'),
            'institution' => $this->whenLoaded('institution'),
            'users' => $this->whenLoaded('users'),
            'classes' => $this->whenLoaded('classes'),
            'active_classes' => $this->whenLoaded('activeClasses'),
        ];
    }
}
