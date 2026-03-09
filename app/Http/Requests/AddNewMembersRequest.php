<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddNewMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parishId' => 'required|uuid|exists:parishes,id',
            'members' => 'required|array|min:1',
            'members.*.name' => 'required|string|max:255',
            'members.*.email' => 'required|email',
            'members.*.dob' => 'nullable|string',
            'members.*.gender' => 'nullable|string',
            'members.*.phoneNumber' => 'nullable|string',
            'members.*.education' => 'nullable|string',
            'members.*.occupation' => 'nullable|string',
            'members.*.address' => 'nullable|string',
            'members.*.zoneId' => 'nullable|uuid',
            'members.*.zonePositionId' => 'nullable|uuid',
            'members.*.areaId' => 'nullable|uuid',
            'members.*.areaPositionId' => 'nullable|uuid',
            'members.*.provinceId' => 'nullable|uuid',
            'members.*.provincePositionId' => 'nullable|uuid',
            'members.*.parishPositionId' => 'nullable|uuid',
            'members.*.departmentId' => 'nullable|uuid',
        ];
    }
}
