<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'admin'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });
    Route::resource('platforms', PlatformController::class);
    Route::prefix('platforms')->name('platforms.')->controller(PlatformController::class)->group(function () {
        Route::post('{platform}/sync-contests', 'syncContests')->name('sync.contests');
        Route::post('{platform}/sync-problems', 'syncProblems')->name('sync.problems');
        Route::post('sync-all', 'syncAll')->name('sync.all');
    });
    Route::resource('users', UserController::class)->only(['index', 'show']);
    Route::resource('admins', AdminController::class);
    Route::resource('settings', SettingController::class)->only('index', 'update');
});
