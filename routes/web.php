<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OthersController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\PlatformProfileController;
use App\Http\Controllers\User\SyncController;
use App\Http\Controllers\User\UserProfileController;

Route::get('/', function () {
    return view('web.welcome');
})->name('home');

// Public profile routes
Route::get('/user/profile/{username}', [UserProfileController::class, 'show'])->name('user.profile.show');

// Authenticated user routes
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        // User profile management
        Route::prefix('profile/{username}')
            ->name('profile.')
            ->group(function () {
                Route::get('/edit', [UserProfileController::class, 'edit'])->name('edit');
                Route::put('/', [UserProfileController::class, 'update'])->name('update');
            });

        // Platform profiles
        Route::resource('platform-profiles', PlatformProfileController::class)->only(['edit', 'update']);

        // Sync functionality
        Route::post('/sync', [SyncController::class, 'sync'])->name('sync');
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
