<?php

namespace App\Actions;

use App\Contracts\Platforms\PlatformAdapter;
use App\Models\PlatformProfile;
use App\Models\SyncLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncPlatformProfileAction
{
    public function execute(PlatformProfile $platformProfile, PlatformAdapter $adapter): void
    {
        $start = microtime(true);

        try {
            DB::transaction(function () use ($platformProfile, $adapter) {
                // 1. Refresh to get latest data
                $platformProfile->refresh();

                // prevent double sync within 60 seconds
                if (
                    $platformProfile->last_synced_at &&
                    $platformProfile->last_synced_at->gt(now()->subSeconds(60))
                ) {
                    return;
                }

                // 2. Fetch profile
                $profileDto = $adapter->fetchProfile($platformProfile->handle);

                // 3. Determine total solved
                if ($adapter->supportsSubmissions()) {
                    try {
                        $submissions = $adapter->fetchSubmissions($platformProfile->handle);

                        $totalSolved = $submissions
                            ->unique(fn($sub) => $sub->problemId)
                            ->count();

                        Log::info("Sync: Counted {$totalSolved} unique problems from " . $submissions->count() . " submissions for {$platformProfile->handle}");
                    } catch (\Exception $e) {
                        // If submissions fetch fails (e.g., Cloudflare blocks SPOJ),
                        // fall back to profile's total_solved count
                        Log::warning("Sync: Submissions fetch failed for {$platformProfile->handle}, using profile total_solved: {$profileDto->totalSolved}");
                        $totalSolved = $profileDto->totalSolved;
                    }
                } else {
                    // 🔥 IMPORTANT: trust profile DTO
                    $totalSolved = $profileDto->totalSolved;
                }

                // 4. Update platform_profiles (FIXED)
                $platformProfile->update([
                    'rating' => $profileDto->rating,
                    'total_solved' => $totalSolved,
                    'raw' => $profileDto->raw,            // 🔥 REQUIRED
                    'last_synced_at' => now(),
                ]);
            });

            // 5. Success log
            SyncLog::create([
                'platform_profile_id' => $platformProfile->id,
                'status' => 'success',
                'duration_ms' => (int) ((microtime(true) - $start) * 1000),
            ]);
        } catch (Throwable $e) {

            // 6. Failure log
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
