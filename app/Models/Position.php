<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['name', 'level', 'status'];

    // public function privileges()
    // {
    //     return $this->hasMany(PositionPrivilege::class, 'position_id');
    // }

    public function positionPrivileges()
    {
        return $this->hasMany(PositionPrivilege::class, 'positionId');
    }
}
