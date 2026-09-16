<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id|unique:employees,user_id',
            'department_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:employees,id',
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'job_title' => 'required|string|max:100',
            'basic_salary' => 'required|numeric|min:0',
            'joined_at' => 'required|date',
        ];
    }
}