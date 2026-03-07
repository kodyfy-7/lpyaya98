<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['name', 'slug', 'level', 'module'];

    protected $casts = [
        'level' => 'array', // JSONB stored as array
    ];
}
