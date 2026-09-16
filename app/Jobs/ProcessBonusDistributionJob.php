<?php

namespace App\Jobs;

use App\Models\Bonus;
use App\Services\BonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessBonusDistributionJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Bonus $bonus
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(BonusService $bonusService): void
    {
        // Executes the upline bonus calculation off the main thread
        $bonusService->distributeUplineBonus($this->bonus);
    }
}