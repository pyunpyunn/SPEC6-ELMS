<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'hr_admin';
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;
        $userId = $this->route('employee')?->user_id;
        $role = $this->input('role');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['required', Rule::in(['employee', 'manager', 'hr_admin'])],
            'employee_id' => [
                'required',
                'string',
                'max:30',
                Rule::unique('employees', 'employee_id')->ignore($employeeId),
                function ($attribute, $value, $fail) use ($role) {
                    $validPrefixes = [
                        'employee' => 'EMP-',
                        'manager' => 'MGR-',
                        'hr_admin' => 'HR-',
                    ];
                    
                    $expectedPrefix = $validPrefixes[$role] ?? '';
                    if ($expectedPrefix && !str_starts_with($value, $expectedPrefix)) {
                        $fail("Employee ID must start with '{$expectedPrefix}' for {$role} role.");
                    }
                },
            ],
            'department_id' => ['required', 'exists:departments,id'],
            'manager_id' => ['nullable', 'exists:employees,id'],
            'position' => ['required', 'string', 'max:120'],
            'date_hired' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'daily_rate' => ['required', 'numeric', 'min:0'],
            'employment_status' => ['required', Rule::in(['active', 'resigned', 'terminated'])],
        ];
    }
}
