<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BonusDistributionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bonus_id' => $this->bonus_id,
            'recipient_employee_id' => $this->recipient_employee_id,
            'level' => (int) $this->level,
            'percentage' => (float) $this->percentage,
            'amount' => (float) $this->amount,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}