<?php

namespace App\Jobs;

use App\Models\EventBlast;
use App\Models\EventBlastParticipant;
use App\Models\EventParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class DispatchEventBlastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1; // Coordinator should not retry — it would re-dispatch duplicates

    public function __construct(public readonly string $blastId) {}

    public function handle(): void
    {
        $blast = EventBlast::findOrFail($this->blastId);

        $blast->update([
            'status'        => 'processing',
            'dispatched_at' => now(),
        ]);

        // Pull only the columns we need — avoid hydrating full models in memory
        $participants = EventParticipant::where('eventId', $blast->event_id)
        ->where('is_valid', true)
        ->select('id', 'email')
        ->get();

        $blast->update(['total_participants' => $participants->count()]);

        $blastParticipantRows = [];
        $validIds             = [];
        $skippedIds           = [];

        foreach ($participants as $participant) {
            $hasEmail = !empty(trim((string) $participant->email));

            $blastParticipantRows[] = [
                'id'                   => \Illuminate\Support\Str::uuid()->toString(),
                'event_blast_id'       => $blast->id,
                'event_participant_id' => $participant->id,
                'email'                => $participant->email ?? '',
                'status'               => $hasEmail ? 'pending' : 'skipped',
                'failure_reason'       => $hasEmail ? null : 'No email address on record',
                'created_at'           => now(),
                'updated_at'           => now(),
            ];

            if ($hasEmail) {
                $validIds[] = $participant->id;
            } else {
                $skippedIds[] = $participant->id;
            }
        }

        // Bulk insert all participant rows in one query
        foreach (array_chunk($blastParticipantRows, 500) as $chunk) {
            EventBlastParticipant::insert($chunk);
        }

        // Update skipped count immediately
        if (!empty($skippedIds)) {
            $blast->update(['total_skipped' => count($skippedIds)]);
        }

        // Chunk valid participants into send jobs, finalize at the end
        $blastParticipantIdChunks = EventBlastParticipant::where('event_blast_id', $blast->id)
            ->where('status', 'pending')
            ->pluck('id')
            ->chunk(25)
            ->map(fn ($chunk) => $chunk->values()->all())
            ->all();

        if (empty($blastParticipantIdChunks)) {
            // Nothing to send — finalize immediately
            FinalizeEventBlastJob::dispatch($blast->id);
            return;
        }

        $sendJobs = array_map(
            fn ($chunk) => new SendEventBlastJob($blast->id, $chunk),
            $blastParticipantIdChunks
        );

        // Chain finalize after all send jobs complete
        Bus::chain([
            ...$sendJobs,
            new FinalizeEventBlastJob($blast->id),
        ])->dispatch();
    }

    public function failed(\Throwable $e): void
    {
        EventBlast::where('id', $this->blastId)->update(['status' => 'failed']);
    }
}