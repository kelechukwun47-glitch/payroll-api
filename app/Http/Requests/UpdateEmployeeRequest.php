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
        $employeeId = $this->route('employee')->id ?? $this->route('employee');

        return [
            'department_id' => 'nullable|exists:departments,id',
            'manager_id' => [
                'nullable',
                'exists:employees,id',
                function ($attribute, $value, $fail) use ($employeeId) {
                    if (!$value) {
                        return;
                    }

                    if ($value === $employeeId) {
                        $fail('An employee cannot be their own manager.');
                        return;
                    }

                    $currentManager = Employee::find($value);

                    while ($currentManager) {
                        if ($currentManager->id === $employeeId) {
                            $fail('Circular reporting detected: You cannot assign a manager who reports to this employee.');
                            return;
                        }

                        $currentManager = $currentManager->manager;
                    }
                },
            ],
            'job_title' => 'sometimes|string|max:100',
            'basic_salary' => 'sometimes|numeric|min:0',
            'joined_at' => 'sometimes|date',
        ];
    }
}