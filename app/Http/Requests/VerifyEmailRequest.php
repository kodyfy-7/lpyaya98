<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6|same:passwordConfirmation',
            'passwordConfirmation' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'password.same' => 'Please ensure confirmation of password',
        ];
    }
}
