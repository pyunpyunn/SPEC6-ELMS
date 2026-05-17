<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;

class LeaveDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAccessRole('hr');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'remarks' => ['required', 'string', 'max:500'],
        ];
    }
}
