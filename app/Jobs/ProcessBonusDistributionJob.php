<?php

namespace App\Jobs;

use App\Models\Bonus;
use App\Services\BonusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBonusDistributionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
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
        $bonusService->distributeUpline($this->bonus);
    }
}