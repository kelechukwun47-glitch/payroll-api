<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class BonusFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'amount' => 100000.00,
            'reason' => 'Performance Bonus',
            'status' => 'approved',
        ];
    }
}