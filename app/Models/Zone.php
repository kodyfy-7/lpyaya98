<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['name', 'status'];

    public function areas()
    {
        return $this->hasMany(Area::class, 'zone_id');
    }
}
