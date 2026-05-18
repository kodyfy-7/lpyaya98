<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventBlast extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'subject',
        'html_content',
        'status',
        'total_participants',
        'total_sent',
        'total_failed',
        'total_skipped',
        'dispatched_at',
        'completed_at',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'completed_at'  => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function blastParticipants(): HasMany
    {
        return $this->hasMany(EventBlastParticipant::class, 'event_blast_id');
    }
}