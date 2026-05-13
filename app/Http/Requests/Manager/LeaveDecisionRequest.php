<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

class LeaveDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'manager';
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'remarks' => ['required', 'string', 'max:500'],
        ];
    }
}
