<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'proof' => [
                'nullable',
                'file',
                'max:10240',
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
