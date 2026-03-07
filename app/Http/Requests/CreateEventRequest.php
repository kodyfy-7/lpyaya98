<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:province,zone,area,parish',
            'parentId' => 'nullable|uuid',
            'startDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'startTime' => 'required|date_format:H:i:s',
            'registrationFee' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ];
    }
}
