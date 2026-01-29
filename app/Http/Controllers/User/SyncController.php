<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPlatformProfileJob;
use App\Models\PlatformProfile;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\LeetCode\LeetCodeAdapter;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\CodeChef\CodeChefAdapter;
use App\Platforms\HackerRank\HackerRankAdapter;
use App\Platforms\Spoj\SpojAdapter;
use Illuminate\Support\Facades\Auth;

class SyncController extends Controller
{
    public function sync()
    {
        $user = Auth::user();

        $profiles = PlatformProfile::with('platform')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($profiles as $profile) {
            $adapterClass = match ($profile->platform->name) {
                'codeforces' => CodeforcesAdapter::class,
                'leetcode'   => LeetCodeAdapter::class,
                'atcoder'    => AtCoderAdapter::class,
                'codechef'   => CodeChefAdapter::class,
                'spoj'       => SpojAdapter::class,
                'hackerrank' => HackerRankAdapter::class,
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
        Auth::user()->forceFill([
            'last_synced_at' => now(),
        ])->save();


        return back()->with(
            'success',
            'All platforms are syncing. This may take a few moments.'
        );
    }
}
