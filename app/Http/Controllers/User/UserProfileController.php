<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show($username)
    {
        $user = User::where('username', $username)
            ->with('platformProfiles.platform')
            ->firstOrFail();

        return view('user.pages.profile.show', compact('user'));
    }

    public function edit($username)
    {
        $user = Auth::user();

        // Ensure user can only edit their own profile
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        return view('user.pages.profile.edit', compact('user'));
    }

    public function update(Request $request, $username)
    {
        $user = Auth::user();

        // Ensure user can only update their own profile
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        // Validate the request
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'bio' => ['nullable', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Store new profile picture
            $path = $request->file('profile_picture')->store('profiles', 'public');
            $validated['profile_photo_path'] = $path;
        }

        // Update user profile
        $user->update($validated);

        return redirect()
            ->route('user.profile.show', $user->username)
            ->with('success', 'Profile updated successfully!');
    }
}
