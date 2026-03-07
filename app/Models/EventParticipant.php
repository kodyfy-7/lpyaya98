<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventParticipant extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    const DELETED_AT = 'deletedAt';

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'eventId', 'name', 'email', 'gender', 'phoneNumber',
        'zoneId', 'areaId', 'parishId', 'location',
        'registrationApproved', 'registrationNumber', 'attended',
    ];

    protected $casts = [
        'attended' => 'boolean',
        'registrationApproved' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventId');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zoneId');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'areaId');
    }

    public function parish()
    {
        return $this->belongsTo(Parish::class, 'parishId');
    }
}
