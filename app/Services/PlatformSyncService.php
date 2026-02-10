<?php

namespace App\Services;

use App\Jobs\SyncPlatformContestsJob;
use App\Jobs\SyncPlatformProblemsJob;
use App\Models\Platform;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\LeetCode\LeetCodeAdapter;
use App\Platforms\CodeChef\CodeChefAdapter;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\HackerRank\HackerRankAdapter;
use App\Platforms\Spoj\SpojAdapter;
use App\Platforms\HackerEarth\HackerEarthAdapter;
use App\Platforms\Timus\TimusAdapter;
use App\Platforms\Uva\UvaAdapter;
use Illuminate\Support\Collection;

class PlatformSyncService
{
    /**
     * Platform adapter mapping.
     */
    protected array $platformAdapters = [
        'codeforces' => CodeforcesAdapter::class,
        'leetcode' => LeetCodeAdapter::class,
        'codechef' => CodeChefAdapter::class,
        'atcoder' => AtCoderAdapter::class,
        'hackerrank' => HackerRankAdapter::class,
        'spoj' => SpojAdapter::class,
        'hackerearth' => HackerEarthAdapter::class,
        'timus' => TimusAdapter::class,
        'uva' => UvaAdapter::class,
    ];

    /**
     * Dispatch platform-specific contest sync jobs.
     */
    public function dispatchContestSyncs(?bool $onlyNeeded = true): Collection
    {
        $results = collect();

        $platforms = Platform::active()->get();

        foreach ($platforms as $platform) {
            // Skip if sync not needed (unless forced)
            if ($onlyNeeded && !$platform->needsContestSync()) {
                $results->push([
                    'platform' => $platform->name,
                    'type' => 'contests',
                    'status' => 'skipped',
                    'reason' => 'Last sync was less than 1 hour ago',
                ]);
                continue;
            }

            $adapterClass = $this->platformAdapters[$platform->name] ?? null;

            if (!$adapterClass || !class_exists($adapterClass)) {
                $results->push([
                    'platform' => $platform->name,
                    'type' => 'contests',
                    'status' => 'failed',
                    'reason' => 'Adapter not found',
                ]);
                continue;
            }

            SyncPlatformContestsJob::dispatch($platform->id, $adapterClass);

            $results->push([
                'platform' => $platform->name,
                'type' => 'contests',
                'status' => 'dispatched',
            ]);
        }

        return $results;
    }

    /**
     * Dispatch platform-specific problem sync jobs.
     */
    public function dispatchProblemSyncs(?bool $onlyNeeded = true): Collection
    {
        $results = collect();

        $platforms = Platform::active()->get();

        foreach ($platforms as $platform) {
            // Skip if sync not needed (unless forced)
            if ($onlyNeeded && !$platform->needsProblemSync()) {
                $results->push([
                    'platform' => $platform->name,
                    'type' => 'problems',
                    'status' => 'skipped',
                    'reason' => 'Last sync was less than 1 hour ago',
                ]);
                continue;
            }

            $adapterClass = $this->platformAdapters[$platform->name] ?? null;

            if (!$adapterClass || !class_exists($adapterClass)) {
                $results->push([
                    'platform' => $platform->name,
                    'type' => 'problems',
                    'status' => 'failed',
                    'reason' => 'Adapter not found',
                ]);
                continue;
            }

            SyncPlatformProblemsJob::dispatch($platform->id, $adapterClass);

            $results->push([
                'platform' => $platform->name,
                'type' => 'problems',
                'status' => 'dispatched',
            ]);
        }

        return $results;
    }

    /**
     * Dispatch all platform-specific sync jobs.
     */
    public function dispatchAllSyncs(?bool $onlyNeeded = true): Collection
    {
        $contestResults = $this->dispatchContestSyncs($onlyNeeded);
        $problemResults = $this->dispatchProblemSyncs($onlyNeeded);

        return $contestResults->merge($problemResults);
    }

    /**
     * Get list of available adapters.
     */
    public function getAvailableAdapters(): array
    {
        return $this->platformAdapters;
    }
}
