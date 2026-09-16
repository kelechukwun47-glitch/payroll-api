<?php

namespace Tests\Feature\Api\V1;

use App\Models\Bonus;
use App\Models\Employee;
use App\Services\BonusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusTest extends TestCase
{
    use RefreshDatabase;

    public function test_upline_bonus_distribution_stops_at_configured_levels(): void
    {
        $manager = Employee::factory()->create();
        $employee = Employee::factory()->create(['manager_id' => $manager->id]);

        $bonus = Bonus::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 100000,
        ]);

        $service = new BonusService();
        $service->distributeUplineBonus($bonus);

        $this->assertDatabaseHas('bonus_distributions', [
            'original_bonus_id' => $bonus->id,
            'beneficiary_employee_id' => $manager->id,
            'upline_level' => 1,
            'amount' => 10000.00, // 10% of 100k
        ]);
    }
}