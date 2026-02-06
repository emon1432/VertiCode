<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Institute;
use App\Models\Platform;
use App\Models\PlatformProfile;
use App\Models\User;
use App\Platforms\AtCoder\AtCoderAdapter;
use App\Platforms\CodeChef\CodeChefAdapter;
use App\Platforms\Codeforces\CodeforcesAdapter;
use App\Platforms\HackerEarth\HackerEarthAdapter;
use App\Platforms\HackerRank\HackerRankAdapter;
use App\Platforms\LeetCode\LeetCodeAdapter;
use App\Platforms\Spoj\SpojAdapter;
use App\Platforms\Timus\TimusAdapter;
use App\Platforms\Uva\UvaAdapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show($username)
    {
        $user = User::where('username', $username)
            ->with('platformProfiles.platform')
            ->firstOrFail();
        $platforms = Platform::with('platformProfiles')->orderBy('name')->get();
        return view('user.pages.profile.show', compact('user', 'platforms'));
    }

    public function edit(Request $request, $username)
    {
        $user = User::findOrFail(Auth::id());
        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            return $this->countriesAndInstitutes($request);
        }

        $countries = collect();
        $institutes = collect();
        $platforms = Platform::with('platformProfiles')->orderBy('name')->get();
        return view('user.pages.profile.edit', compact('user', 'countries', 'institutes', 'platforms'));
    }

    public function update(Request $request, $username)
    {
        $user = User::findOrFail(Auth::id());

        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        switch ($request->section) {
            case 'profile-info':
                $return = $this->profileInfo($request, $user);
                break;
            case 'profile-platform':
                $return = $this->platformPreferences($request, $user);
                break;
            case 'social-links':
                $return = $this->socialLinks($request, $user);
                break;
            case 'profile-security':
                switch ($request->input('sub-section')) {
                    case 'changePasswordCollapse':
                        $return = $this->changePassword($request, $user);
                        break;
                    default:
                        abort(400, 'Invalid sub-section.');
                        break;
                }
                break;
            default:
                abort(400, 'Invalid section.');
                break;
        }

        if (isset($return)) {
            return $return;
        }
    }

    protected function countriesAndInstitutes(Request $request)
    {
        $type = $request->get('type');
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        switch ($type) {
            case 'countries':
                $query = Country::where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orderBy('name');

                $total = $query->count();
                $results = $query->offset($offset)
                    ->limit($perPage)
                    ->get()
                    ->map(function ($country) {
                        return [
                            'id' => $country->id,
                            'text' => $country->name . ' (' . $country->code . ')'
                        ];
                    });

                return response()->json([
                    'results' => $results,
                    'pagination' => [
                        'more' => ($offset + $perPage) < $total
                    ]
                ]);
                break;
            case 'institutes':
                $query = Institute::where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('country', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhere('website', 'like', '%' . $search . '%')
                    ->orderBy('name');

                $total = $query->count();
                $results = $query->offset($offset)
                    ->limit($perPage)
                    ->get()
                    ->map(function ($institute) {
                        return [
                            'id' => $institute->id,
                            'text' => $institute->name . ' (' . $institute->country->name . ')' . ' (' . $institute->website . ')'
                        ];
                    });

                return response()->json([
                    'results' => $results,
                    'pagination' => [
                        'more' => ($offset + $perPage) < $total
                    ]
                ]);
                break;
            default:
                return response()->json([
                    'results' => [],
                    'pagination' => [
                        'more' => false
                    ]
                ]);
                break;
        }
    }

    protected function profileInfo(Request $request, User $user)
    {
        $validate = validator($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'country_id' => 'nullable|exists:countries,id',
            'institute_id' => 'nullable|exists:institutes,id',
            'bio' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validate->fails()) {
            return redirect(
                route('user.profile.edit', $user->username) . '#' . $request->section
            )->withErrors($validate)->withInput();
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;
        $user->country_id = $request->country_id;
        $user->institute_id = $request->institute_id;
        $user->bio = $request->bio;
        if ($request->hasFile('image')) {
            $user->image = imageUploadManager($request->file('image'), $request->username, 'users');
        }
        $user->save();

        return redirect(
            route('user.profile.edit', $user->username) . '#' . $request->section
        )->with('success', 'Profile Information Updated Successfully!');
    }

    protected function platformPreferences(Request $request, User $user)
    {
        $validate = validator($request->all(), [
            'platforms' => 'required|array',
            'platforms.*' => 'nullable|string|max:100',
            'section' => 'required|string|in:profile-platform',
        ]);

        if ($validate->fails()) {
            return redirect(
                route('user.profile.edit', $user->username) . '#' . $request->section
            )->withErrors($validate)->withInput();
        }

        $platformsInput = $request->input('platforms', []);

        foreach ($platformsInput as $platformId => $handle) {
            if (empty($handle)) {
                PlatformProfile::where('user_id', $user->id)
                    ->where('platform_id', $platformId)
                    ->delete();
                continue;
            }
            $platform = Platform::find($platformId);
            if ($platform) {
                $profile = PlatformProfile::where('user_id', $user->id)
                    ->where('platform_id', $platformId)
                    ->first();

                if ($profile) {
                    $profile->handle = $handle;
                    $profile->profile_url = $platform->profile_url . $handle;
                    $profile->save();
                } else {
                    PlatformProfile::create([
                        'user_id' => $user->id,
                        'platform_id' => $platformId,
                        'handle' => $handle,
                        'profile_url' => $platform->profile_url . $handle,
                    ]);
                }
            }
        }

        return redirect(
            route('user.profile.edit', $user->username) . '#' . $request->section
        )->with('success', 'Platform Preferences Updated Successfully!');
    }

    protected function socialLinks(Request $request, User $user)
    {
        $validate = validator($request->all(), [
            'social_links.website' => 'nullable|string|max:255',
            'social_links.facebook' => 'nullable|string|max:100',
            'social_links.instagram' => 'nullable|string|max:100',
            'social_links.twitter' => 'nullable|string|max:100',
            'social_links.github' => 'nullable|string|max:100',
            'social_links.linkedin' => 'nullable|string|max:100',
        ]);

        if ($validate->fails()) {
            return redirect(
                route('user.profile.edit', $user->username) . '#' . $request->section
            )->withErrors($validate)->withInput();
        }

        $user->website = $request->input('social_links.website');
        $user->facebook = $request->input('social_links.facebook');
        $user->instagram = $request->input('social_links.instagram');
        $user->twitter = $request->input('social_links.twitter');
        $user->github = $request->input('social_links.github');
        $user->linkedin = $request->input('social_links.linkedin');
        $user->save();

        return redirect(
            route('user.profile.edit', $user->username) . '#' . $request->section
        )->with('success', 'Social Links Updated Successfully!');
    }

    protected function changePassword(Request $request, User $user)
    {
        $validate = validator($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validate->fails()) {
            return redirect(
                route('user.profile.edit', $user->username) . '#' . $request->section
            )->withErrors($validate)
                ->withInput()
                ->with('sub-section', $request->input('sub-section'));
        }

        if (!password_verify($request->current_password, $user->password)) {
            return redirect(
                route('user.profile.edit', $user->username) . '#' . $request->section
            )->withErrors(['current_password' => 'Current password is incorrect'])
                ->withInput()
                ->with('sub-section', $request->input('sub-section'));
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return redirect(
            route('user.profile.edit', $user->username) . '#' . $request->section
        )->with('success', 'Password Update Successfully!');
    }
}
