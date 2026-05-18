<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Container\Container;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Queue\Jobs\DatabaseJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessJobs extends Command
{
    protected $signature   = 'queue:process
                                {--queue=default : The queue to process}
                                {--limit=20 : Max jobs to process per run}
                                {--tries=3 : Max attempts per job}';

    protected $description  = 'Process queued jobs without pcntl or proc_open (shared hosting safe)';

    public function handle(): void
    {
        $queue  = $this->option('queue');
        $limit  = (int) $this->option('limit');
        $tries  = (int) $this->option('tries');
        $count  = 0;

        $this->info("Processing queue [{$queue}] (max {$limit} jobs)...");

        while ($count < $limit) {
            // Pop the next available job off the DB queue
            $job = $this->getNextJob($queue);

            if (!$job) {
                $this->info("Queue empty. Processed {$count} job(s).");
                return;
            }

            $count++;
            $this->processJob($job, $tries);
        }

        $this->info("Limit of {$limit} jobs reached. Processed {$count} job(s).");
    }

    private function getNextJob(string $queue): ?DatabaseJob
    {
        /** @var DatabaseQueue $connection */
        $connection = app('queue')->connection('database');

        return $connection->pop($queue);
    }

    private function processJob(DatabaseJob $job, int $tries): void
    {
        $id = $job->getJobId();

        try {
            $this->line("  Running job [{$id}]...");

            // Mark as reserved (already done by pop()), then fire
            $job->fire();

            $this->line("  <info>✓ Done [{$id}]</info>");

            Log::info('queue:process job completed', ['job_id' => $id, 'name' => $job->getName()]);
        } catch (Throwable $e) {
            $attempts = $job->attempts();

            Log::error('queue:process job failed', [
                'job_id'   => $id,
                'name'     => $job->getName(),
                'attempt'  => $attempts,
                'error'    => $e->getMessage(),
            ]);

            if ($attempts >= $tries) {
                // Exhausted retries — move to failed_jobs
                $this->line("  <error>✗ Failed [{$id}] after {$attempts} attempt(s): {$e->getMessage()}</error>");
                $job->fail($e);
            } else {
                // Release back with a delay so the next cron tick retries it
                $this->line("  <comment>↩ Released [{$id}] for retry (attempt {$attempts}/{$tries})</comment>");
                $job->release(30);
            }
        }
    }
}