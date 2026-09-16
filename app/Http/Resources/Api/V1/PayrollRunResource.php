<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
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
            'month' => (int) $this->month,
            'year' => (int) $this->year,
            'total_gross' => (float) $this->total_gross,
            'total_net' => (float) $this->total_net,
            'status' => $this->status,
            'payslips' => $this->whenLoaded('payslips'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}