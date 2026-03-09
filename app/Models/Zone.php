<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasUuids, SoftDeletes;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    const DELETED_AT = 'deletedAt';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['name', 'status'];

    public function areas()
    {
        return $this->hasMany(Area::class, 'zoneId'); // camelCase FK
    }
}
