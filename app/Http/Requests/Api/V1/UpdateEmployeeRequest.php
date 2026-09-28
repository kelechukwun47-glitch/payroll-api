<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('employees', 'email')->ignore($employeeId),
            ],
            'employee_number' => [
                'sometimes',
                'string',
                Rule::unique('employees', 'employee_number')->ignore($employeeId),
            ],
            'department_id' => ['sometimes', 'nullable', 'exists:departments,id'],
            'manager_id' => [
                'sometimes',
                'nullable',
                'exists:employees,id',
                function ($attribute, $value, $fail) use ($employeeId) {
                    if (!$value) {
                        return;
                    }

                    // Standardize string comparison for IDs
                    $targetManagerId = (string) $value;
                    $currentEmployeeId = (string) $employeeId;

                    // 1. Direct Self-Assignment Check
                    if ($targetManagerId === $currentEmployeeId) {
                        $fail('An employee cannot be assigned as their own manager.');
                        return;
                    }

                    // 2. Circular Reporting Graph Traversal
                    $visitedManagerIds = [];
                    $nextManagerId = $targetManagerId;

                    while ($nextManagerId) {
                        if ((string) $nextManagerId === $currentEmployeeId) {
                            $fail('Circular reporting hierarchy detected: You cannot assign a manager who reports up to this employee.');
                            return;
                        }

                        // Prevent infinite loops in corrupt database states
                        if (in_array($nextManagerId, $visitedManagerIds, true)) {
                            break;
                        }

                        $visitedManagerIds[] = $nextManagerId;

                        // Query only the next manager_id up the chain to minimize overhead
                        $nextManagerId = Employee::where('id', $nextManagerId)->value('manager_id');
                    }
                },
            ],
            'job_title' => ['sometimes', 'required', 'string', 'max:100'],
            'basic_salary' => ['sometimes', 'required', 'numeric', 'min:0'],
            'employment_status' => ['sometimes', Rule::in(['active', 'inactive', 'suspended', 'terminated'])],
            'joined_at' => ['sometimes', 'required', 'date'],
        ];
    }
}