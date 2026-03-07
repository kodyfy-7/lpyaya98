<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['name', 'description', 'is_default', 'is_super_admin'];

    protected $casts = [
        'is_default' => 'boolean',
        'is_super_admin' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function privileges()
    {
        return $this->hasMany(RolePrivilege::class, 'role_id');
    }

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id');
    }
}
