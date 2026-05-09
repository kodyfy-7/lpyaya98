<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;  // ← correct import
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use HasUuids, SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['zoneId', 'name', 'status'];

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zoneId');
    }

    public function parishes(): HasMany
    {
        return $this->hasMany(Parish::class, 'areaId');
    }

    public function monthlyDues(): HasMany
    {
        return $this->hasMany(MonthlyDue::class, 'area_id');
    }
}