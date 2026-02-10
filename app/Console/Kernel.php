<?php

namespace App\Console;

use App\Console\Commands\SyncPlatformsCommand;
use App\Console\Commands\TestHackerEarthSync;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        TestHackerEarthSync::class,
        SyncPlatformsCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync contests and problems from all platforms every hour
        // Only syncs platforms where last sync was >1 hour ago (smart sync)
        $schedule->command('platforms:sync')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
    }
}
