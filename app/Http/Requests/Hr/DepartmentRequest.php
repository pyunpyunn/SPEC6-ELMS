<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAccessRole('hr');
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($departmentId)],
            'name' => ['required', 'string', 'max:120', Rule::unique('departments', 'name')->ignore($departmentId)],
            'description' => ['nullable', 'string', 'max:500'],
            'manager_user_id' => ['nullable', 'exists:users,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
