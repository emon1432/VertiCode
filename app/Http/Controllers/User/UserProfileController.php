<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Institute;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

        return view('user.pages.profile.edit', compact('user', 'countries', 'institutes'));
    }

    public function update(Request $request, $username)
    {
        // dd($request->all(), $username, $request->section);
        $user = User::findOrFail(Auth::id());

        if ($user->username !== $username) {
            abort(403, 'Unauthorized action.');
        }

        switch ($request->section) {
            case 'profile-info':
                $request->validate([
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
                break;
        }

        return redirect()
            ->route('user.profile.edit', $user->username)
            ->with('success', 'Profile updated successfully!');
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
}
