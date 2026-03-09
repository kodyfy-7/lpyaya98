<?php

// ──────────────────────────────────────────
// app/Http/Requests/AddNewMembersRequest.php
// ──────────────────────────────────────────

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'dob' => 'nullable|string',
            'gender' => 'nullable|string',
            'phoneNumber' => 'nullable|string',
            'education' => 'nullable|string',
            'occupation' => 'nullable|string',
            'address' => 'nullable|string',
            'zoneId' => 'nullable|uuid',
            'areaId' => 'nullable|uuid',
            'parishId' => 'nullable|uuid',
            'departmentId' => 'nullable|uuid',
        ];
    }
}
