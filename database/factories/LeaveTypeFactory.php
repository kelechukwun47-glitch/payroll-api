<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Annual Leave',
            'allowed_days' => 20,
            'requires_approval' => true,
            'is_active' => true,
        ];
    }
}