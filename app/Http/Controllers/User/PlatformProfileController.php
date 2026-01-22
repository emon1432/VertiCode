<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\PlatformProfile;
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

        PlatformProfile::updateOrCreate(
            [
                'user_id' => $user->id,
                'platform_id' => $platform->id,
            ],
            [
                'handle' => $validated['handle'],
                'profile_url' => $platform->base_url
                    ? $platform->base_url . '/profile/' . $validated['handle']
                    : null,
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

        $platformProfile->update([
            'handle' => $validated['handle'],
            'profile_url' => $platformProfile->platform->base_url
                ? $platformProfile->platform->base_url . '/profile/' . $validated['handle']
                : null,
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
