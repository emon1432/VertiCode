<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PlatformProfile;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $platformProfiles = PlatformProfile::query()
            ->with('platform')
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($profile) {

                $extra = [];

                // LeetCode-specific breakdown
                if (
                    $profile->platform->name === 'leetcode' &&
                    is_array($profile->raw)
                ) {
                    $stats = collect(
                        $profile->raw['submitStatsGlobal']['acSubmissionNum'] ?? []
                    )->keyBy('difficulty');

                    $extra = [
                        'easy'   => (int) ($stats['Easy']['count'] ?? 0),
                        'medium' => (int) ($stats['Medium']['count'] ?? 0),
                        'hard'   => (int) ($stats['Hard']['count'] ?? 0),
                    ];
                }

                return [
                    'id' => $profile->id,
                    'platform' => $profile->platform->display_name,
                    'platform_key' => $profile->platform->name,
                    'handle' => $profile->handle,
                    'rating' => $profile->rating,
                    'total_solved' => $profile->total_solved,
                    'profile_url' => $profile->profile_url,
                    'last_synced_at' => $profile->last_synced_at,
                    'sync_status' => $profile->last_synced_at ? 'synced' : 'not_synced',
                    'extra' => $extra,
                ];
            });

        $summary = [
            'total_platforms' => $platformProfiles->count(),
            'total_solved' => $platformProfiles->sum('total_solved'),
            'max_rating' => $platformProfiles->max('rating'),
        ];

        return view('user.dashboard', [
            'user' => $user,
            'summary' => $summary,
            'platformProfiles' => $platformProfiles,
        ]);
    }
}
