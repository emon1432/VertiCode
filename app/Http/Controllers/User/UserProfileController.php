<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function show($username)
    {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    }

    public function edit($username)
    {
        // Logic to edit user profile
    }

    public function update(Request $request, $username)
    {
        // Logic to update user profile
    }
}
