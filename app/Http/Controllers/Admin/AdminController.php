<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\View\Components\Actions;
use App\View\Components\UserInfo;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return response()->json($this->data());
        }
        return view('admin.pages.admins.index');
    }

    public function create()
    {
        return view('admin.pages.admins.create');
    }

    public function store(UserRequest $request)
    {
        try {
            User::create($request->except(['_token', 'image', 'password']) + [
                'password' => bcrypt($request->password),
                'image' => $request->file('image') ? imageUploadManager($request->file('image'), slugify($request->name), 'admins') : null,
            ]);

            return response()->json([
                'status' => 200,
                'message' => __('Admin created successfully'),
                'redirect' => route('admins.index'),
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

    public function edit(User $admin)
    {
        return view('admin.pages.admins.edit', compact('admin'));
    }

    public function update(UserRequest $request, User $admin)
    {
        try {
            $admin->update($request->except(['_token', 'image', 'password']) + [
                'password' => $request->password ? bcrypt($request->password) : $admin->password,
                'image' => $request->file('image') ? imageUpdateManager($request->file('image'), slugify($request->name), 'admins', $admin->image) : $admin->image,
            ]);

            return response()->json([
                'status' => 200,
                'message' => __('Admin updated successfully'),
                'redirect' => route('admins.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => __('Whoops! Something went wrong. Please try again later. Error: ') . $e->getMessage(),
                'redirect' => null,
            ], 500);
        }
    }

    public function destroy(User $admin)
    {
        try {
            $admin->delete();

            return response()->json([
                'status' => 200,
                'message' => __('Admin deleted successfully'),
                'redirect' => route('admins.index'),
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
        return User::where('role', 'admin')->get()->map(function ($admin) {
            $admin->actions = (new Actions([
                'model' => $admin,
                'resource' => 'admins',
                'buttons' => [
                    'basic' => [
                        'view' => true,
                        'edit' => true,
                        'delete' => true,
                    ],
                ],
            ]))->render()->render();
            $admin->name = (new UserInfo($admin))->render()->render();
            return $admin;
        })->toArray();
    }
}
