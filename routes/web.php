<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OthersController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('web.welcome');
})->name('home');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'user'])->group(function () {
    Route::get('/user/profile', function () {
        $user = Auth::user();
        return view('user.profile', compact('user'));
    })->name('profile.show');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'admin'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });
    Route::resource('users', UserController::class);
    Route::resource('settings', SettingController::class)->only('index', 'update');
});

Route::controller(OthersController::class)->group(function () {
    Route::middleware('guest')->get('/admin/login', 'login')->name('admin.login');
    Route::post('/test-mail', 'testMail')->name('test.mail');
    Route::get('/migrate', 'migrate')->name('migration');
    Route::get('/clear', 'clear')->name('clear');
    Route::get('/composer', 'composer')->name('composer');
    Route::get('/iseed', 'iseed')->name('iseed');
});
