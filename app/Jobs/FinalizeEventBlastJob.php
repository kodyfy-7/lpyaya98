<?php

namespace App\Jobs;

use App\Models\EventBlast;
use App\Models\EventBlastParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class FinalizeEventBlastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly string $blastId) {}

    public function handle(): void
    {
        // Count each status in a single aggregation query
        $counts = EventBlastParticipant::where('event_blast_id', $this->blastId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        EventBlast::where('id', $this->blastId)->update([
            'status'          => 'completed',
            'total_sent'      => $counts->get('sent', 0),
            'total_failed'    => $counts->get('failed', 0),
            'total_skipped'   => $counts->get('skipped', 0),
            'completed_at'    => now(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        // Don't leave the blast stuck in "processing" forever
        EventBlast::where('id', $this->blastId)
            ->where('status', 'processing')
            ->update(['status' => 'failed']);
    }
}