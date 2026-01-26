<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\LeetCode\LeetCodeAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlatformProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $platforms = Platform::where('is_active', true)->get();

        $profiles = PlatformProfile::with('platform')
            ->where('user_id', $user->id)
            ->get();

        return view('user.platform-profiles.index', compact(
            'platforms',
            'profiles'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'platform' => [
                'required',
                Rule::exists('platforms', 'name'),
            ],
            'handle' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $platform = Platform::where('name', $validated['platform'])->firstOrFail();
        $adapter = match ($platform->name) {
            'codeforces' => app(CodeforcesAdapter::class),
            'leetcode'   => app(LeetCodeAdapter::class),
            'atcoder'    => app(\App\Platforms\AtCoder\AtCoderAdapter::class),
            default      => throw new \RuntimeException('Unsupported platform'),
        };
        PlatformProfile::updateOrCreate(
            [
                'user_id' => $user->id,
                'platform_id' => $platform->id,
            ],
            [
                'handle' => $validated['handle'],
                'profile_url' => $adapter->profileUrl($validated['handle']),
                'is_active' => true,
            ]
        );

        return redirect()
            ->route('user.platform-profiles.index')
            ->with('success', 'Platform profile saved successfully.');
    }

    public function update(Request $request, PlatformProfile $platformProfile)
    {
        $this->authorizeProfile($platformProfile);

        $validated = $request->validate([
            'handle' => ['required', 'string', 'max:100'],
        ]);

        $adapter = match ($platformProfile->platform->name) {
            'codeforces' => app(CodeforcesAdapter::class),
            'leetcode'   => app(LeetCodeAdapter::class),
            default      => throw new \RuntimeException('Unsupported platform'),
        };

        $platformProfile->update([
            'handle' => $validated['handle'],
            'profile_url' => $adapter->profileUrl($validated['handle']),
        ]);

        return back()->with('success', 'Handle updated successfully.');
    }

    public function destroy(PlatformProfile $platformProfile)
    {
        $this->authorizeProfile($platformProfile);

        $platformProfile->delete();

        return back()->with('success', 'Platform profile removed.');
    }

    private function authorizeProfile(PlatformProfile $platformProfile): void
    {
        if ($platformProfile->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
