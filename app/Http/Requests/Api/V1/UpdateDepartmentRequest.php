<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id ?? $this->route('department');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', 'unique:departments,name,' . $departmentId],
            'code' => ['sometimes', 'required', 'string', 'max:20', 'unique:departments,code,' . $departmentId],
            'description' => ['nullable', 'string'],
            'department_head_id' => ['nullable', 'string', 'exists:employees,id'],
        ];
    }
}