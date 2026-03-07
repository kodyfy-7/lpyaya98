<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['zone_id', 'name', 'status'];

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function parishes()
    {
        return $this->hasMany(Parish::class, 'area_id');
    }
}
