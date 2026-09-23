<?php

namespace App\Services;

use App\Models\Bonus;
use App\Models\BonusDistribution;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Exception;

class BonusService
{
    /**
     * Default upline distribution percentages by level index.
     */
    protected array $defaultPercentages = [
        1 => 10.0, // Level 1 (Direct Manager)
        2 => 5.0,  // Level 2 (Senior Manager)
        3 => 2.0,  // Level 3 (Director)
    ];

    /**
     * Process upline bonus distribution for an approved bonus.
     */
    public function distributeUplineBonus(Bonus $bonus): void
    {
        DB::transaction(function () use ($bonus) {
            $employee = $bonus->employee;
            $currentUpline = $employee->manager;
            $level = 1;
            $visitedEmployeeIds = [$employee->id];

            while ($currentUpline && isset($this->defaultPercentages[$level])) {
                // Anti-circularity check: stop if an employee appears twice in the chain
                if (in_array($currentUpline->id, $visitedEmployeeIds)) {
                    throw new Exception("Circular reporting structure detected at Employee ID: {$currentUpline->id}");
                }

                $visitedEmployeeIds[] = $currentUpline->id;
                $percentage = $this->defaultPercentages[$level];
                $amount = ($bonus->amount * $percentage) / 100;

                // Prevent duplicate distribution for the same bonus and level
                BonusDistribution::firstOrCreate(
                    [
                        'original_bonus_id' => $bonus->id,
                        'upline_level' => $level,
                    ],
                    [
                        'source_employee_id' => $employee->id,
                        'beneficiary_employee_id' => $currentUpline->id,
                        'percentage' => $percentage,
                        'amount' => $amount,
                        'status' => 'pending',
                        'distributed_at' => now(),
                    ]
                );

                $currentUpline = $currentUpline->manager;
                $level++;
            }
        });
    }

    /**
     * Validate reporting structure to prevent self-reporting and circular loops.
     */
    public function validateReportingStructure(int $employeeId, ?int $managerId): bool
    {
        if (!$managerId) {
            return true;
        }

        if ($employeeId === $managerId) {
            return false;
        }

        $visited = [$employeeId];
        $currentManager = Employee::find($managerId);

        while ($currentManager) {
            if (in_array($currentManager->id, $visited)) {
                return false; // Circular loop detected
            }
            $visited[] = $currentManager->id;
            $currentManager = $currentManager->manager;
        }

        return true;
    }
}