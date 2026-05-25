<?php

namespace App\Http\Requests\Hr;

use App\Models\Position;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAccessRole('hr');
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'date_hired' => ['required', 'date'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->filled('department_id') || ! $this->filled('position_id')) {
                return;
            }

            $belongsToDepartment = Position::whereKey($this->integer('position_id'))
                ->where('department_id', $this->integer('department_id'))
                ->exists();

            if (! $belongsToDepartment) {
                $validator->errors()->add('position_id', 'The selected position does not belong to the selected department.');
            }
        });
    }
}
