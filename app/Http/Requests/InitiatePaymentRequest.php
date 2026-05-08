<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only the authenticated user can initiate their own payment.
        // userId is no longer accepted from input — removed entirely.
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'due_id' => 'required|uuid|exists:monthly_dues,id',
            'amount' => 'required|numeric|min:100|max:10000000', // enforce sane bounds
            'currency' => 'nullable|string|size:3|in:NGN,USD,GHS,KES', // whitelist currencies
            // 'month' => 'required|integer|between:1,12',
            // 'year' => 'required|integer|min:2000|max:2100',
        ];
    }
}
