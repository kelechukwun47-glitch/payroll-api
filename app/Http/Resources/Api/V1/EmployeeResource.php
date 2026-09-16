<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'job_title' => $this->job_title,
            'basic_salary' => $this->basic_salary,
            'employment_status' => $this->employment_status?->value ?? $this->employment_status,
            'joined_at' => $this->joined_at?->format('Y-m-d'),
            'created_at' => $this->created_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'department' => $this->whenLoaded('department'),
            'manager' => new EmployeeResource($this->whenLoaded('manager')),
            'direct_reports_count' => $this->whenCounted('directReports'),
        ];
    }
}