<?php

namespace App\Http\Requests\Hr;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAccessRole('hr');
    }

    public function rules(): array
    {
        return [
            'confirm_delete_phrase' => [
                'required',
                Rule::in(['DELETE ACCOUNT FOREVER']),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_delete_phrase.required' => 'Please type DELETE ACCOUNT FOREVER to confirm.',
            'confirm_delete_phrase.in' => 'Please type DELETE ACCOUNT FOREVER exactly to confirm deletion.',
        ];
    }
}
