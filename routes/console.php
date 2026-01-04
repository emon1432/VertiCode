<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\PlatformProfile;
use App\Jobs\SyncPlatformProfileJob;
use App\Platforms\Codeforces\CodeforcesAdapter;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Schedule::call(function () {
    PlatformProfile::where('is_active', true)
        ->chunkById(100, function ($profiles) {
            foreach ($profiles as $profile) {
                dispatch(
                    new SyncPlatformProfileJob(
                        $profile,
                        CodeforcesAdapter::class
                    )
                );
            }
        });
})->hourly();
