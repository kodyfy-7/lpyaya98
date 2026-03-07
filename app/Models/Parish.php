<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parish extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['area_id', 'name', 'status'];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }
}
