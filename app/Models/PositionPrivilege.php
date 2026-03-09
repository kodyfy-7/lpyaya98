<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PositionPrivilege extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['position_id', 'privilege_id'];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function privilege()
    {
        return $this->belongsTo(ModulePrivilege::class, 'privilegeId');
    }
}
