<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'emailVerifiedAt' => $this->emailVerifiedAt,
            'deactivatedAt' => $this->deactivatedAt,
            'emailVerificationOtp' => $this->emailVerificationOtp,
            'emailVerificationOtpExpireIn' => $this->emailVerificationOtpExpireIn,
            'passwordResetOtp' => $this->passwordResetOtp,
            'passwordResetOtpExpireIn' => $this->passwordResetOtpExpireIn,
            'roleId' => $this->roleId,
            'role' => new RoleResource($this->whenLoaded('role')),
            'isAdmin' => $this->isAdmin,
            'isSuperAdmin' => $this->isSuperAdmin,
            'phoneNumber' => $this->phoneNumber,
            'address' => $this->address,
            'education' => $this->education,
            'dateOfBirth' => $this->dateOfBirth,
            'gender' => $this->gender,
            'occupation' => $this->occupation,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
