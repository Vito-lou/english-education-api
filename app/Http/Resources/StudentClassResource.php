<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentClassResource extends JsonResource
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
            'student_id' => $this->student_id,
            'class_id' => $this->class_id,
            'enrollment_date' => $this->enrollment_date,
            'status' => $this->status,
            'status_name' => $this->getStatusName(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // 关联的学员信息（使用StudentResource）
            'student' => new StudentResource($this->whenLoaded('student')),

            // 关联的班级信息
            'class' => $this->whenLoaded('class'),
        ];
    }

    /**
     * 获取状态中文名称
     */
    private function getStatusName(): string
    {
        $statuses = [
            'active' => '在读',
            'graduated' => '已毕业',
            'dropped' => '退学',
            'transferred' => '转班',
        ];

        return $statuses[$this->status] ?? $this->status;
    }
}
