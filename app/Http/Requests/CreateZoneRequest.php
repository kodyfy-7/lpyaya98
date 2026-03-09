<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ];
    }
}
