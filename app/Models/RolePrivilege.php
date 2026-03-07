<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RolePrivilege extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['role_id', 'privilege_id'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function privilege()
    {
        return $this->belongsTo(ModulePrivilege::class, 'privilege_id');
    }
}
