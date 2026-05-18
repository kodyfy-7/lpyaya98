<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Schedule::command('queue:work', [
//     '--stop-when-empty',
//     '--max-time=55',
//     '--tries=3',
//     '--queue=default',
// ])->everyMinute()->withoutOverlapping();
// Schedule::command('queue:work', [
//     '--stop-when-empty',
//     '--max-time=55',
//     '--tries=3',
//     '--queue=default',
// ])
// ->everyMinute()
// ->withoutOverlapping()
// ->appendOutputTo(storage_path('logs/queue-worker.log'));

// Schedule::command('queue:listen', [
//     '--tries=3',
//     '--queue=default',
//     '--timeout=55',
// ])->everyMinute()->withoutOverlapping();

Schedule::command('queue:process', [
    '--queue=default',
    '--limit=20',
    '--tries=3',
])->everyMinute()->withoutOverlapping();