<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    const DELETED_AT = 'deletedAt';

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'title', 'description', 'type', 'parentId',
        'startDate', 'endDate', 'startTime',
        'registrationFee', 'location',
    ];

    protected $casts = [
        'startDate' => 'date',
        'endDate' => 'date',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'deletedAt' => 'datetime',
    ];

    public function participants()
    {
        return $this->hasMany(EventParticipant::class, 'eventId');
    }
}
