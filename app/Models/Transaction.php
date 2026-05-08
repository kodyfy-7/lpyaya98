<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasUuids, SoftDeletes;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const DELETED_AT = 'deleted_at';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'amount', 'status', 'payment_method', 'reference', 'currency',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
