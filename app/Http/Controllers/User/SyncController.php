<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPlatformProfileJob;
use App\Models\PlatformProfile;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\LeetCode\LeetCodeAdapter;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\CodeChef\CodeChefAdapter;
use App\Platforms\HackerEarth\HackerEarthAdapter;
use App\Platforms\HackerRank\HackerRankAdapter;
use App\Platforms\Spoj\SpojAdapter;
use App\Platforms\Timus\TimusAdapter;
use App\Platforms\Uva\UvaAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check sync cooldown
        $cooldownMinutes = (int) config('platforms.sync_cooldown_minutes', 120);
        if ($user->last_synced_at) {
            $nextAvailableAt = $user->last_synced_at->copy()->addMinutes($cooldownMinutes);
            if (now()->lt($nextAvailableAt)) {
                $remainingMinutes = now()->diffInMinutes($nextAvailableAt, false);
                $remainingText = $this->formatCooldownTime(abs($remainingMinutes));

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => "Please wait {$remainingText} before syncing again."
                    ], 429);
                }
                return back()->with('error', "Please wait {$remainingText} before syncing again.");
            }
        }

        $profiles = PlatformProfile::with('platform')
            ->where('user_id', $user->id)
            ->active()
            ->get();

        if ($profiles->isEmpty()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'No connected platforms to sync yet.'
                ], 400);
            }
            return back()->with('error', 'No connected platforms to sync yet.');
        }

        // Generate unique sync session ID
        $syncSessionId = uniqid('sync_', true);

        foreach ($profiles as $profile) {
            $adapterClass = match ($profile->platform->name) {
                'codeforces' => CodeforcesAdapter::class,
                'leetcode'   => LeetCodeAdapter::class,
                'atcoder'    => AtCoderAdapter::class,
                'codechef'   => CodeChefAdapter::class,
                'spoj'       => SpojAdapter::class,
                'hackerrank' => HackerRankAdapter::class,
                'hackerearth'  => HackerEarthAdapter::class,
                'uva'         => UvaAdapter::class,
                'timus'      => TimusAdapter::class,
                default      => abort(400, 'Unsupported platform'),
            };

            if ($adapterClass) {
                dispatch(
                    new SyncPlatformProfileJob(
                        $profile->id,
                        $adapterClass
                    )
                );
            }
        }

        // 🔥 single source of truth for dashboard
        $user->update([
            'last_synced_at' => now(),
            'sync_session_id' => $syncSessionId,
        ]);

        $message = 'All platforms are syncing. This may take a few moments.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'success' => true,
                'syncSessionId' => $syncSessionId,
                'totalProfiles' => $profiles->count(),
            ]);
        }

        session(['sync_session_id' => $syncSessionId]);
        session(['sync_total_profiles' => $profiles->count()]);
        return back()->with('success', $message);

    }

    /**
     * Format cooldown time for user-friendly display
     */
    private function formatCooldownTime(int $minutes): string
    {
        if ($minutes < 1) {
            return 'a few seconds';
        }

        if ($minutes < 60) {
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        $text = $hours . ' hour' . ($hours > 1 ? 's' : '');
        if ($remainingMinutes > 0) {
            $text .= ' and ' . $remainingMinutes . ' minute' . ($remainingMinutes > 1 ? 's' : '');
        }

        return $text;
    }

    /**
     * Get current sync status for the user
     */
    public function getSyncStatus(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $cooldownMinutes = (int) config('platforms.sync_cooldown_minutes', 120);
        $canSync = true;
        $remainingSeconds = 0;
        $nextAvailableAt = null;

        if ($user->last_synced_at) {
            $nextAvailableAt = $user->last_synced_at->copy()->addMinutes($cooldownMinutes);
            if (now()->lt($nextAvailableAt)) {
                $canSync = false;
                $remainingSeconds = now()->diffInSeconds($nextAvailableAt, false);
            }
        }

        $hasActiveProfiles = $user->platformProfiles()->where('status', 'Active')->exists();

        return response()->json([
            'canSync' => $canSync && $hasActiveProfiles,
            'hasActiveProfiles' => $hasActiveProfiles,
            'remainingSeconds' => max(0, $remainingSeconds),
            'nextAvailableAt' => $nextAvailableAt ? $nextAvailableAt->toIso8601String() : null,
        ]);
    }

    /**
     * Get real-time sync progress
     */
    public function getSyncProgress(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $syncSessionId = $request->input('syncSessionId') ?? session('sync_session_id');
        $totalProfiles = $request->input('totalProfiles') ?? session('sync_total_profiles', 0);

        if (!$syncSessionId || $totalProfiles === 0) {
            return response()->json([
                'syncing' => false,
                'completed' => 0,
                'total' => 0,
                'progress' => 100,
                'duration' => 0,
            ]);
        }

        // Get sync logs since last_synced_at
        $syncStartTime = $user->last_synced_at;

        $syncLogs = \App\Models\SyncLog::whereHas('platformProfile', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->where('created_at', '>=', $syncStartTime)
        ->get();

        $completedCount = $syncLogs->count();
        $successCount = $syncLogs->where('status', 'success')->count();
        $failedCount = $syncLogs->where('status', 'failed')->count();
        $totalDuration = $syncLogs->sum('duration_ms');

        $progress = $totalProfiles > 0 ? round(($completedCount / $totalProfiles) * 100) : 0;
        $syncing = $completedCount < $totalProfiles;

        // Calculate elapsed time from sync start
        $elapsedSeconds = $syncStartTime ? now()->diffInSeconds($syncStartTime) : 0;

        return response()->json([
            'syncing' => $syncing,
            'completed' => $completedCount,
            'total' => $totalProfiles,
            'success' => $successCount,
            'failed' => $failedCount,
            'progress' => min($progress, 100),
            'duration' => $totalDuration, // ms
            'elapsedSeconds' => $elapsedSeconds,
        ]);
    }
}
