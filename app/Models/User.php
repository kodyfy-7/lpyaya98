<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name', 'email', 'password', 'role_id',
        'is_admin', 'is_super_admin', 'phone_number',
        'address', 'education', 'date_of_birth', 'gender', 'occupation',
        'email_verified_at', 'deactivated_at',
        'email_verification_otp', 'email_verification_otp_expire_in',
        'password_reset_otp', 'password_reset_otp_expire_in',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_super_admin' => 'boolean',
        'email_verified_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'email_verification_otp_expire_in' => 'datetime',
        'password_reset_otp_expire_in' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId', 'id');
    }

    public function member()
    {
        return $this->hasOne(Member::class, 'userId', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id', 'userId', 'id');
    }

    public function emailVerifications()
    {
        return $this->hasMany(EmailVerification::class, 'user_id', 'userId', 'id');
    }
}
