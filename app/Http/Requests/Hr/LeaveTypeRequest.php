<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'hr_admin';
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leave_type')?->id ?? $this->route('leaveType')?->id;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('leave_types', 'name')->ignore($leaveTypeId)],
            'annual_allocation' => ['required', 'integer', 'min:0', 'max:365'],
            'requires_approval' => ['nullable', 'boolean'],
            'requires_proof' => ['nullable', 'boolean'],
            'proof_rules' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
