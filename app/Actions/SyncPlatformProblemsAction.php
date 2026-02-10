<?php

namespace App\Actions;

use App\Contracts\Platforms\ProblemSyncAdapter;
use App\DataTransferObjects\Platform\ProblemDTO;
use App\Models\Contest;
use App\Models\Platform;
use App\Models\Problem;
use Illuminate\Support\Facades\Log;

class SyncPlatformProblemsAction
{
    public function execute(Platform $platform, ProblemSyncAdapter $adapter, ?string $contestId = null): array
    {
        if (!$adapter->supportsProblems()) {
            return [
                'success' => false,
                'message' => "Platform {$platform->name} does not support problem syncing",
                'synced' => 0,
            ];
        }

        try {
            $problems = $adapter->fetchProblems(500, $contestId);
            $synced = 0;
            $errors = [];

            foreach ($problems as $problemDTO) {
                try {
                    $this->syncProblem($platform, $problemDTO);
                    $synced++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to sync problem {$problemDTO->platformProblemId}: {$e->getMessage()}";
                    Log::error("Problem sync error", [
                        'platform' => $platform->name,
                        'problem_id' => $problemDTO->platformProblemId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Mark sync as complete
            $this->markSyncComplete($platform, $synced);

            return [
                'success' => true,
                'message' => "Synced {$synced} problems from {$platform->name}",
                'synced' => $synced,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            Log::error("Platform problems sync failed", [
                'platform' => $platform->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => "Failed to sync problems: {$e->getMessage()}",
                'synced' => 0,
            ];
        }
    }

    protected function syncProblem(Platform $platform, ProblemDTO $dto): void
    {
        $contestId = null;
        if ($dto->contestId) {
            $contest = Contest::where('platform_id', $platform->id)
                ->where('platform_contest_id', $dto->contestId)
                ->first();
            $contestId = $contest?->id;
        }

        Problem::updateOrCreate(
            [
                'platform_id' => $platform->id,
                'platform_problem_id' => $dto->platformProblemId,
            ],
            [
                'contest_id' => $contestId,
                'slug' => $dto->slug,
                'name' => $dto->name,
                'code' => $dto->code,
                'description' => $dto->description,
                'difficulty' => $dto->difficulty?->value,
                'rating' => $dto->rating,
                'points' => $dto->points,
                'accuracy' => $dto->accuracy,
                'time_limit_ms' => $dto->timeLimitMs,
                'memory_limit_mb' => $dto->memoryLimitMb,
                'total_submissions' => $dto->totalSubmissions,
                'accepted_submissions' => $dto->acceptedSubmissions,
                'solved_count' => $dto->solvedCount,
                'tags' => $dto->tags,
                'topics' => $dto->topics,
                'url' => $dto->url,
                'editorial_url' => $dto->editorialUrl,
                'raw' => $dto->raw,
                'is_premium' => $dto->isPremium,
                'status' => 'active',
            ]
        );
    }

    public function markSyncComplete(Platform $platform, int $count): void
    {
        $platform->markProblemsSynced($count);
    }
}
