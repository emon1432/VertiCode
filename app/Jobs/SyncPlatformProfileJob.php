<?php

namespace App\Jobs;

use App\Actions\SyncPlatformProfileAction;
use App\Contracts\Platforms\PlatformAdapter;
use App\Models\PlatformProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPlatformProfileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public PlatformProfile $platformProfile,
        public string $adapterClass
    ) {}

    public function handle(SyncPlatformProfileAction $action): void
    {
        /** @var PlatformAdapter $adapter */
        $adapter = app($this->adapterClass);

        $action->execute($this->platformProfile, $adapter);
    }
}
