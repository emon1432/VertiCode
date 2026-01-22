<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OthersController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\PlatformProfileController;
use App\Http\Controllers\User\SyncController;
use App\Http\Controllers\User\UserProfileController;

Route::get('/', function () {
    return view('web.welcome');
})->name('home');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'user'])->prefix('user')->name('user.')->group(function () {
    Route::controller(UserProfileController::class)->group(function () {
        Route::get('/profile/{username}', 'show')->name('profile');
        Route::get('/profile/{username}/edit', 'edit')->name('profile.edit');
        Route::put('/profile/{username}', 'update')->name('profile.update');
    });
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::resource('platform-profiles', PlatformProfileController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('/platform-profiles/{platformProfile}/sync', [SyncController::class, 'sync'])->name('platform-profiles.sync');
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
