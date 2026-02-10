<?php

namespace App\Actions;

use App\Contracts\Platforms\ContestSyncAdapter;
use App\DataTransferObjects\Platform\ContestDTO;
use App\Models\Contest;
use App\Models\Platform;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncPlatformContestsAction
{
    public function execute(Platform $platform, ContestSyncAdapter $adapter): array
    {
        if (!$adapter->supportsContests()) {
            return [
                'success' => false,
                'message' => "Platform {$platform->name} does not support contest syncing",
                'synced' => 0,
            ];
        }

        try {
            $contests = $adapter->fetchContests();
            $synced = 0;
            $errors = [];

            foreach ($contests as $contestDTO) {
                try {
                    $this->syncContest($platform, $contestDTO);
                    $synced++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to sync contest {$contestDTO->platformContestId}: {$e->getMessage()}";
                    Log::error("Contest sync error", [
                        'platform' => $platform->name,
                        'contest_id' => $contestDTO->platformContestId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Mark sync as complete
            $this->markSyncComplete($platform, $synced);

            return [
                'success' => true,
                'message' => "Synced {$synced} contests from {$platform->name}",
                'synced' => $synced,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error("Platform contests sync failed", [
                'platform' => $platform->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => "Failed to sync contests: {$e->getMessage()}",
                'synced' => 0,
            ];
        }
    }

    protected function syncContest(Platform $platform, ContestDTO $dto): void
    {
        Contest::updateOrCreate(
            [
                'platform_id' => $platform->id,
                'platform_contest_id' => $dto->platformContestId,
            ],
            [
                'slug' => $dto->slug,
                'name' => $dto->name,
                'description' => $dto->description,
                'type' => $dto->type->value,
                'phase' => $dto->phase,
                'duration_seconds' => $dto->durationSeconds,
                'start_time' => $dto->startTime,
                'end_time' => $dto->endTime,
                'url' => $dto->url,
                'participant_count' => $dto->participantCount,
                'is_rated' => $dto->isRated,
                'tags' => $dto->tags,
                'raw' => $dto->raw,
                'status' => 'active',
            ]
        );
    }

    public function markSyncComplete(Platform $platform, int $count): void
    {
        $platform->markContestsSynced($count);
    }
}
