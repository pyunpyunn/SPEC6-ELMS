<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAccessRole('hr');
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leaveType')?->id;

        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('leave_types', 'name')->ignore($leaveTypeId)],
            'annual_allocation' => ['required', 'integer', 'min:1', 'max:365'],
            'gender' => ['nullable', Rule::in('male', 'female', 'other')],
            'requires_approval' => ['boolean'],
            'is_compensable' => ['boolean'],
            'requires_proof' => ['boolean'],
            'proof_rules' => ['nullable', 'string', 'max:500'],
            'max_document_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requires_approval' => $this->boolean('requires_approval'),
            'is_compensable' => $this->boolean('is_compensable'),
            'requires_proof' => $this->boolean('requires_proof'),
            'max_document_days' => $this->input('max_document_days') !== null && $this->input('max_document_days') !== ''
                ? (int) $this->input('max_document_days')
                : null,
            'is_active' => $this->boolean('is_active', true),
            'gender' => $this->input('gender') ?: null,
        ]);
    }
}
