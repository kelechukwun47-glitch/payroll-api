<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = is_object($employee) ? $employee->id : $employee;

        return [
            'department_id' => ['sometimes', 'nullable', 'string', 'exists:departments,id'],
            'manager_id' => [
                'sometimes',
                'nullable',
                'string',
                'exists:employees,id',
                function ($attribute, $value, $fail) use ($employeeId) {
                    if (!$value) {
                        return;
                    }

                    // 1. Direct Self-Assignment Check
                    if ($value === $employeeId) {
                        $fail('An employee cannot be assigned as their own manager.');
                        return;
                    }

                    // 2. Optimized Circular Reporting Graph Traversal
                    // We query only the manager_id column to prevent loading heavy model payloads into memory
                    $visitedManagerIds = [];
                    $currentManagerId = $value;

                    while ($currentManagerId) {
                        if ($currentManagerId === $employeeId) {
                            $fail('Circular reporting hierarchy detected: You cannot assign a manager who reports up to this employee.');
                            return;
                        }

                        // Prevent infinite loops in corrupt database states
                        if (in_array($currentManagerId, $visitedManagerIds, true)) {
                            break;
                        }

                        $visitedManagerIds[] = $currentManagerId;

                        // Light query: Select ONLY the next manager_id up the chain
                        $currentManagerId = Employee::where('id', $currentManagerId)->value('manager_id');
                    }
                },
            ],
            'job_title' => ['sometimes', 'required', 'string', 'max:100'],
            'basic_salary' => ['sometimes', 'required', 'numeric', 'min:0'],
            'joined_at' => ['sometimes', 'required', 'date'],
        ];
    }
}