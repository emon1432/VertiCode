<?php

namespace App\Actions;

use App\Contracts\Platforms\PlatformAdapter;
use App\Models\PlatformProfile;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncPlatformProfileAction
{
    public function execute(PlatformProfile $platformProfile, PlatformAdapter $adapter): void
    {
        $start = microtime(true);

        try {
            DB::transaction(function () use ($platformProfile, $adapter) {

                // 1. Fetch profile
                $profileDto = $adapter->fetchProfile($platformProfile->handle);

                // 2. Fetch submissions (if supported)
                $totalSolved = 0;

                if ($adapter->supportsSubmissions()) {
                    $submissions = $adapter->fetchSubmissions($platformProfile->handle);

                    // Deduplicate by problemId
                    $totalSolved = $submissions
                        ->unique(fn($sub) => $sub->problemId)
                        ->count();
                }

                // 3. Update platform_profiles
                $platformProfile->update([
                    'rating' => $profileDto->rating,
                    'total_solved' => $totalSolved,
                    'last_synced_at' => now(),
                ]);
            });

            // 4. Success log
            SyncLog::create([
                'platform_profile_id' => $platformProfile->id,
                'status' => 'success',
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            ]);
        } catch (Throwable $e) {

            // 5. Failure log
            SyncLog::create([
                'platform_profile_id' => $platformProfile->id,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            ]);

            throw $e;
        }
    }
}
