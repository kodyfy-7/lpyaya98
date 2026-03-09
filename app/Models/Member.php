<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasUuids, SoftDeletes;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    const DELETED_AT = 'deletedAt';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'userId', 'zoneId', 'zonePositionId',
        'areaId', 'areaPositionId',
        'provinceId', 'provincePositionId',
        'parishId', 'parishPositionId',
        'departmentId',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zoneId');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'areaId');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'provinceId');
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class, 'parishId');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'departmentId');
    }

    public function zonePosition()
    {
        return $this->belongsTo(Position::class, 'zonePositionId');
    }

    public function areaPosition()
    {
        return $this->belongsTo(Position::class, 'areaPositionId');
    }

    public function provincePosition()
    {
        return $this->belongsTo(Position::class, 'provincePositionId');
    }

    public function parishPosition()
    {
        return $this->belongsTo(Position::class, 'parishPositionId');
    }
}
