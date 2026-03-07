<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterForEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:20',
            'email' => 'required|email',
            'gender' => 'nullable|string',
            'zoneId' => 'required|uuid|exists:zones,id',
            'areaId' => 'required|uuid|exists:areas,id',
            'parishId' => 'required|uuid|exists:parishes,id',
            'location' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'zone_id' => $this->zoneId,
            'area_id' => $this->areaId,
            'parish_id' => $this->parishId,
            'phone_number' => $this->phoneNumber,
        ]);
    }
}
