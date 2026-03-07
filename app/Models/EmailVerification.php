<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailVerification extends Model
{
    use HasUuids, SoftDeletes;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    const DELETED_AT = 'deletedAt';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['userId', 'otp', 'otpExpiresAt', 'expiredAt'];

    protected $casts = [
        'otpExpiresAt' => 'datetime',
        'expiredAt' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'deletedAt' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
