<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*******************************************************************************************************/
/**
 * Artisan Command: delete:logs
 *
 * This command clears specific log files in the storage/logs directory.
 * It runs every hour and only executes on one server instance (when using multiple servers),
 * ensuring that logs aren't cleared multiple times concurrently.
 *
 * Logic:
 * - Define a list of log file paths to be cleared.
 * - Loop through each path, check if the file exists.
 * - If it does, clear its contents using a shell-safe echo command.
 */
Artisan::command('delete:logs', function () {
    $logFiles = [
        storage_path('logs/laravel.log'),
        storage_path('logs/scheduler.log'),
        storage_path('logs/worker.log'),
    ];

    foreach ($logFiles as $file) {
        if (file_exists($file)) {
            exec('echo "" > ' . escapeshellarg($file));
        }
    }
})->purpose('Delete logs every hour.')
->hourly()
->onOneServer();

/*******************************************************************************************************/
// CUSTOM COMMAND: Prune Telescope every 3 days at 3am utc to remove records greater than 72hours
Artisan::command('telescope:prune-custom', function () {
    $hours = env('PRUNE_HOURS', 72);
    
    $this->info("Pruning Telescope entries older than {$hours} hours...");
    \Illuminate\Support\Facades\Artisan::call('telescope:prune', [
        '--hours' => $hours,
    ]);
    $this->info('Telescope pruned successfully.');
})->purpose('Prune Telescope records older than configured hours.');

// Prune Telescope every 3 days at 3am utc to remove records greater than 72hours
Schedule::command('telescope:prune --hours=72')
    ->cron('0 3 */' . env('PRUNE_FREQUENCY', 3) . ' * *')
    ->timezone('UTC')
    ->onOneServer();
/*******************************************************************************************************/