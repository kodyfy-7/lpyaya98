<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'amount', 'status', 'payment_method', 'reference',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
