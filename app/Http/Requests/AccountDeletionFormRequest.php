<?php

namespace App\Http\Requests;

use App\Models\AccountDeletionRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountDeletionFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:100'],
            'mobile_number' => [
                'required',
                'string',
                'max:25',
                function ($attribute, $value, $fail) {
                    $digits = User::extractIndianDigits($value);
                    if (!$digits || !preg_match('/^[6-9]\d{9}$/', $digits)) {
                        $fail('Please enter a valid 10-digit Indian mobile number (e.g., 98300 12345 or +91 9830012345).');
                    }
                },
            ],
            'email' => ['nullable', 'string', 'email', 'max:150'],
            'reason' => ['required', 'string', Rule::in(array_keys(AccountDeletionRequest::REASONS))],
            'message' => ['nullable', 'string', 'max:1000'],
            'confirmation' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Please provide your full registered name.',
            'mobile_number.required' => 'Please enter your registered mobile number.',
            'reason.required' => 'Please select a reason for your account deletion request.',
            'reason.in' => 'Please select a valid reason from the dropdown options.',
            'confirmation.accepted' => 'You must check the confirmation box acknowledging your deletion request.',
        ];
    }
}
