<?php

namespace Database\Seeders;

use App\Enums\EmploymentStatus;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $adminUser = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@payroll.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $hrUser = User::create([
            'name' => 'Sarah HR Head',
            'email' => 'sarah@payroll.com',
            'password' => Hash::make('password123'),
            'role' => 'manager',
        ]);

        $devUser = User::create([
            'name' => 'John Developer',
            'email' => 'john@payroll.com',
            'password' => Hash::make('password123'),
            'role' => 'employee',
        ]);

        // 2. Create Departments
        $deptEng = Department::create([
            'name' => 'Software Engineering',
            'code' => 'ENG',
            'description' => 'Backend, API, and core system platform infrastructure',
        ]);

        $deptHR = Department::create([
            'name' => 'Human Resources',
            'code' => 'HR',
            'description' => 'Personnel management, onboarding, and payroll compliance',
        ]);

        // 3. Create Employees with Hierarchy (Manager -> Employee)
        $manager = Employee::create([
            'user_id' => $hrUser->id,
            'department_id' => $deptHR->id,
            'employee_code' => 'EMP-001',
            'job_title' => 'HR Director',
            'basic_salary' => 850000.00,
            'employment_status' => EmploymentStatus::ACTIVE,
            'joined_at' => '2023-01-15',
        ]);

        $deptHR->update(['department_head_id' => $manager->id]);

        $employee = Employee::create([
            'user_id' => $devUser->id,
            'department_id' => $deptEng->id,
            'manager_id' => $manager->id,
            'employee_code' => 'EMP-002',
            'job_title' => 'Backend Software Engineer',
            'basic_salary' => 500000.00,
            'employment_status' => EmploymentStatus::ACTIVE,
            'joined_at' => '2024-03-01',
        ]);

        // 4. Create Leave Types
        LeaveType::create([
            'name' => 'Annual Leave',
            'description' => 'Paid yearly leave allotment',
            'allowed_days' => 20,
            'requires_approval' => true,
        ]);

        LeaveType::create([
            'name' => 'Sick Leave',
            'description' => 'Medical leave allotment',
            'allowed_days' => 10,
            'requires_approval' => true,
        ]);
    }
}