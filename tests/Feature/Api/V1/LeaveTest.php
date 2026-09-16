<?php

namespace Tests\Feature\Api\V1;

use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_request_leave_exceeding_limit(): void
    {
        $user = User::factory()->create();
        $employee = Employee::factory()->create(['user_id' => $user->id]);
        $leaveType = LeaveType::factory()->create(['allowed_days' => 5]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(), // 11 days requested
            'reason' => 'Vacation',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days_requested']);
    }
}