<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    const DELETED_AT = 'deletedAt';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name', 'email', 'password', 'roleId',
        'isAdmin', 'isSuperAdmin', 'phoneNumber',
        'address', 'education', 'dateOfBirth', 'gender', 'occupation',
        'emailVerifiedAt', 'deactivatedAt',
        'emailVerificationOtp', 'emailVerificationOtpExpireIn',
        'passwordResetOtp', 'passwordResetOtpExpireIn',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'isAdmin' => 'boolean',
        'isSuperAdmin' => 'boolean',
        'emailVerifiedAt' => 'datetime',
        'deactivatedAt' => 'datetime',
        'emailVerificationOtpExpireIn' => 'datetime',
        'passwordResetOtpExpireIn' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'deletedAt' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    // public function members()
    // {
    //     return $this->hasMany(Member::class, 'userId');
    // }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'userId');
    }

    public function emailVerifications()
    {
        return $this->hasMany(EmailVerification::class, 'userId');
    }

    public function member()
    {
        return $this->hasOne(Member::class, 'userId');
    }
}
