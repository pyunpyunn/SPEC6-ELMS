<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeactivateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAccessRole('hr');
    }

    public function rules(): array
    {
        return [
            'employment_status' => ['required', Rule::in(['resigned', 'terminated'])],
        ];
    }
}
