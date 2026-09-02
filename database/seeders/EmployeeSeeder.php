<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $ceoUser = User::create(['name' => 'Alice (CEO)', 'email' => 'alice@payroll.com', 'password' => Hash::make('password')]);
        $managerUser = User::create(['name' => 'Bob (Manager)', 'email' => 'bob@payroll.com', 'password' => Hash::make('password')]);
        $internUser = User::create(['name' => 'Charlie (Intern)', 'email' => 'charlie@payroll.com', 'password' => Hash::make('password')]);

        $ceo = Employee::create([
            'user_id' => $ceoUser->id,
            'employee_code' => 'EMP-001',
            'job_title' => 'Chief Executive Officer',
            'basic_salary' => 150000.00,
            'joined_at' => '2020-01-01',
            'manager_id' => null, // 
        ]);

        $manager = Employee::create([
            'user_id' => $managerUser->id,
            'employee_code' => 'EMP-002',
            'job_title' => 'Engineering Manager',
            'basic_salary' => 90000.00,
            'joined_at' => '2022-06-15',
            'manager_id' => $ceo->id,
        ]);

        $intern = Employee::create([
            'user_id' => $internUser->id,
            'employee_code' => 'EMP-003',
            'job_title' => 'Software Intern',
            'basic_salary' => 45000.00,
            'joined_at' => '2026-08-01',
            'manager_id' => $manager->id,
        ]);
    }
}