<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'type' => 'sometimes|in:province,zone,area,parish',
            'parentId' => 'nullable|uuid',
            'startDate' => 'sometimes|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'startTime' => 'sometimes|date_format:H:i:s',
            'registrationFee' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
        ];
    }
}
