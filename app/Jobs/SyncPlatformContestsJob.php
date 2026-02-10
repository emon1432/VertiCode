<?php

namespace App\Jobs;

use App\Actions\SyncPlatformContestsAction;
use App\Contracts\Platforms\ContestSyncAdapter;
use App\Models\Platform;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPlatformContestsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $platformId,
        public string $adapterClass
    ) {}

    public function handle(SyncPlatformContestsAction $action): void
    {
        $platform = Platform::find($this->platformId);

        if (!$platform || $platform->status !== 'Active') {
            return;
        }

        /** @var ContestSyncAdapter $adapter */
        $adapter = app($this->adapterClass);

        $action->execute($platform, $adapter);
    }
}
