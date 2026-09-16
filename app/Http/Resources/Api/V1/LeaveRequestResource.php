<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'days_requested' => $this->days_requested,
            'reason' => $this->reason,
            'status' => $this->status?->value ?? $this->status,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'leave_type' => $this->whenLoaded('leaveType'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}