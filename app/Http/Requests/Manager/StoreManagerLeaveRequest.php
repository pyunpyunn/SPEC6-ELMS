<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

class StoreManagerLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'manager';
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:500'],
            'proof' => ['nullable', 'file', 'max:5120'],
        ];
    }
}
