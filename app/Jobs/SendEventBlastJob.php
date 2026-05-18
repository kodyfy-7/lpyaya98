<?php

namespace App\Jobs;

use App\Mail\EventBlastMail;
use App\Models\EventBlast;
use App\Models\EventBlastParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEventBlastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public array $backoff = [30, 120]; // seconds between retries

    public function __construct(
        public readonly string $blastId,
        public readonly array $blastParticipantIds, // chunk of event_blast_participants.id
    ) {}

    public function handle(): void
    {
        $blast = EventBlast::findOrFail($this->blastId);

        $blastParticipants = EventBlastParticipant::whereIn('id', $this->blastParticipantIds)
            ->where('status', 'pending') // skip any already handled on a prior retry
            ->get();

        foreach ($blastParticipants as $bp) {
            try {
                Mail::to($bp->email)
                    ->send(new EventBlastMail($blast->subject, $blast->html_content));

                $bp->update(['status' => 'sent']);
            } catch (\Throwable $e) {
                // Per-email failure is swallowed so the job doesn't fail/retry for one bad address
                $bp->update([
                    'status'         => 'failed',
                    'failure_reason' => $e->getMessage(),
                ]);
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        // If the job itself exhausts all retries (e.g. SMTP completely down),
        // mark the whole chunk as failed so the finalize job counts them correctly
        EventBlastParticipant::whereIn('id', $this->blastParticipantIds)
            ->where('status', 'pending')
            ->update([
                'status'         => 'failed',
                'failure_reason' => 'Job failed after all retries: ' . $e->getMessage(),
            ]);
    }
}