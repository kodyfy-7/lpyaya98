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
            'emailVerifiedAt' => $this->email_verified_at,
            'deactivatedAt' => $this->deactivated_at,
            'emailVerificationOtp' => $this->email_verification_otp,
            'emailVerificationOtpExpireIn' => $this->email_verification_otp_expire_in,
            'passwordResetOtp' => $this->password_reset_otp,
            'passwordResetOtpExpireIn' => $this->password_reset_otp_expire_in,
            'roleId' => $this->role_id,
            'isAdmin' => $this->is_admin,
            'isSuperAdmin' => $this->is_super_admin,
            'phoneNumber' => $this->phone_number,
            'address' => $this->address,
            'education' => $this->education,
            'dateOfBirth' => $this->date_of_birth,
            'gender' => $this->gender,
            'occupation' => $this->occupation,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
