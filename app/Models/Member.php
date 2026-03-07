<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    // use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id', 'zone_id', 'zone_position_id',
        'area_id', 'area_position_id',
        'province_id', 'province_position_id',
        'parish_id', 'parish_position_id',
        'department_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class, 'parish_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function zonePosition()
    {
        return $this->belongsTo(Position::class, 'zone_position_id');
    }

    public function areaPosition()
    {
        return $this->belongsTo(Position::class, 'area_position_id');
    }

    public function provincePosition()
    {
        return $this->belongsTo(Position::class, 'province_position_id');
    }

    public function parishPosition()
    {
        return $this->belongsTo(Position::class, 'parish_position_id');
    }
}
