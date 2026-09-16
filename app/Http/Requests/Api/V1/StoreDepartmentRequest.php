<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:departments,name'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
            'department_head_id' => ['nullable', 'string', 'exists:employees,id'],
        ];
    }
}