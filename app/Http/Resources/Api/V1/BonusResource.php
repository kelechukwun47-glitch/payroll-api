<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BonusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'amount' => (float) $this->amount,
            'description' => $this->description,
            'distributions' => BonusDistributionResource::collection($this->whenLoaded('distributions')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}