<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\PlatformProfileController;
use App\Http\Controllers\User\SyncController;
use App\Http\Controllers\User\UserProfileController;

Route::get('/user/profile/{username}', [UserProfileController::class, 'show'])->name('user.profile.show');
Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'user'])->prefix('user')->name('user.')->group(function () {
    Route::prefix('profile/{username}')->name('profile.')->group(function () {
        Route::get('/edit', [UserProfileController::class, 'edit'])->name('edit');
        Route::put('/', [UserProfileController::class, 'update'])->name('update');
    });
    Route::resource('platform-profiles', PlatformProfileController::class)->only(['edit', 'update']);
    Route::post('/sync', [SyncController::class, 'sync'])->name('sync');
});
