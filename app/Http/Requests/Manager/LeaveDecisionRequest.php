<?php

namespace App\Http\Requests\Manager;

use Illuminate\Foundation\Http\FormRequest;

class LeaveDecisionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->routeIs('manager.approvals.approve')) {
            $this->merge(['status' => 'approved']);
        }

        if ($this->routeIs('manager.approvals.reject')) {
            $this->merge(['status' => 'rejected']);
        }
    }

    public function authorize(): bool
    {
        return (bool) $this->user()?->hasAccessRole('manager');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'remarks' => ['required', 'string', 'max:500'],
        ];
    }
}
