<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModulePrivilege extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['module_id', 'name', 'slug'];

    public function module()
    {
        return $this->belongsTo(Module::class, 'moduleId');
    }
}
