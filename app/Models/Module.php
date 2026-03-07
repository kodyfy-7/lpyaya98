<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['name', 'slug'];

    public function privileges()
    {
        return $this->hasMany(ModulePrivilege::class, 'module_id');
    }
}
