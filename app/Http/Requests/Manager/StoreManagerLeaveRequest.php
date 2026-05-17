<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

class StoreManagerLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAccessRole('manager');
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:500'],
            'proof' => [
                'nullable',
                'file',
                'max:5120',
                'mimes:pdf,doc,docx,xls,xlsx,png,jpeg,jpg',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'proof.mimes' => 'Invalid file type. Only allowed files to upload are: pdf, doc, docx, xls, xlsx, png, jpeg, jpg.',
        ];
    }
}
