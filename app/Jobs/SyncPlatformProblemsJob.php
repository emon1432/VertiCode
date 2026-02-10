<?php

namespace App\Jobs;

use App\Actions\SyncPlatformProblemsAction;
use App\Contracts\Platforms\ProblemSyncAdapter;
use App\Models\Platform;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPlatformProblemsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $platformId,
        public string $adapterClass,
        public ?string $contestId = null
    ) {}

    public function handle(SyncPlatformProblemsAction $action): void
    {
        $platform = Platform::find($this->platformId);

        if (!$platform || $platform->status !== 'Active') {
            return;
        }

        /** @var ProblemSyncAdapter $adapter */
        $adapter = app($this->adapterClass);

        $action->execute($platform, $adapter, $this->contestId);
    }
}
