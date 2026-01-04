<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\RoleGroup;
use App\Models\User;
use App\View\Components\Actions;
use App\View\Components\UserInfo;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data());
        }
        return view('admin.pages.users.index');
    }

    public function create()
    {
        return view('admin.pages.users.create');
    }

    public function store(UserRequest $request)
    {
        try {
            User::create($request->except(['_token', 'image', 'password']) + [
                'password' => bcrypt($request->password),
                'image' => $request->file('image') ? imageUploadManager($request->file('image'), slugify($request->name), 'users') : null,
            ]);

            return response()->json([
                'status' => 200,
                'message' => __('User created successfully'),
                'redirect' => route('users.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    public function show(string $id)
    {
        //
    }

    public function edit(User $user)
    {
        return view('admin.pages.users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {
        try {
            $user->update($request->except(['_token', 'image', 'password']) + [
                'password' => $request->password ? bcrypt($request->password) : $user->password,
                'image' => $request->file('image') ? imageUpdateManager($request->file('image'), slugify($request->name), 'users', $user->image) : $user->image,
            ]);

            return response()->json([
                'status' => 200,
                'message' => __('User updated successfully'),
                'redirect' => route('users.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();

            return response()->json([
                'status' => 200,
                'message' => __('User deleted successfully'),
                'redirect' => route('users.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    protected function data()
    {
        return User::all()->map(function ($user) {
            $user->actions = (new Actions([
                'model' => $user,
                'resource' => 'users',
                'buttons' => [
                    'basic' => [
                        'view' => true,
                        'edit' => true,
                        'delete' => true,
                    ],
                ],
            ]))->render()->render();
            $user->name = (new UserInfo($user))->render()->render();
            return $user;
        })->toArray();
    }
}
