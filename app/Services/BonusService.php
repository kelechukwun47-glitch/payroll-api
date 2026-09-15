<?php

namespace App\Services;

use App\Models\Bonus;
use App\Models\BonusDistribution;
use Illuminate\Support\Facades\DB;

class BonusService
{
    /**
     * Dynamically calculate and distribute upline bonuses up to 3 management levels.
     */
    public function distributeUpline(Bonus $bonus): void
    {
        DB::transaction(function () use ($bonus) {
            $currentManager = $bonus->employee->manager;
            $level = 1;

            // Tiered upline percentage rules: Level 1 = 10%, Level 2 = 5%, Level 3 = 2.5%
            $percentages = [
                1 => 10.00,
                2 => 5.00,
                3 => 2.50,
            ];

            while ($currentManager && $level <= 3) {
                $percentage = $percentages[$level];
                $amount = ($bonus->amount * $percentage) / 100;

                BonusDistribution::create([
                    'bonus_id' => $bonus->id,
                    'source_employee_id' => $bonus->employee_id,
                    'beneficiary_employee_id' => $currentManager->id,
                    'upline_level' => $level,
                    'percentage' => $percentage,
                    'amount' => $amount,
                    'status' => 'pending',
                ]);

                // Walk up to the next management level
                $currentManager = $currentManager->manager;
                $level++;
            }
        });
    }
}