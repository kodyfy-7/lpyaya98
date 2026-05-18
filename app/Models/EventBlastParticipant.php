<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBlastParticipant extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_blast_id',
        'event_participant_id',
        'email',
        'status',
        'failure_reason',
    ];

    public function blast(): BelongsTo
    {
        return $this->belongsTo(EventBlast::class, 'event_blast_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(EventParticipant::class, 'event_participant_id');
    }
}