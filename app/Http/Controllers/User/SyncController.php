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

class SyncController extends Controller
{
    public function sync()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check sync cooldown
        $cooldownMinutes = config('platforms.sync_cooldown_minutes', 120);
        if ($user->last_synced_at) {
            $nextAvailableAt = $user->last_synced_at->addMinutes($cooldownMinutes);
            if (now()->lt($nextAvailableAt)) {
                $remainingMinutes = now()->diffInMinutes($nextAvailableAt, false);
                $remainingText = $this->formatCooldownTime(abs($remainingMinutes));
                return back()->with('error', "Please wait {$remainingText} before syncing again.");
            }
        }

        $profiles = PlatformProfile::with('platform')
            ->where('user_id', $user->id)
            ->active()
            ->get();

        if ($profiles->isEmpty()) {
            return back()->with('error', 'No connected platforms to sync yet.');
        }

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
        ]);


        return back()->with(
            'success',
            'All platforms are syncing. This may take a few moments.'
        );
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
}
