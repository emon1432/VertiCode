<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OthersController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
})->name('home');

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index')->name('dashboard');
    });
    Route::resource('users', UserController::class);
    Route::resource('trash', TrashController::class)->only('index');
    Route::controller(TrashController::class)->group(function () {
        Route::post('/trash/restore/{table}/{id}', 'restore')->name('trash.restore');
        Route::delete('/trash/destroy/{table}/{id}', 'destroy')->name('trash.destroy');
    });
    Route::resource('settings', SettingController::class)->only('index', 'update');
});

Route::controller(OthersController::class)->group(function () {
    Route::post('/test-mail', 'testMail')->name('test.mail');
    Route::get('/migrate', 'migrate')->name('migration');
    Route::get('/clear', 'clear')->name('clear');
    Route::get('/composer', 'composer')->name('composer');
    Route::get('/iseed', 'iseed')->name('iseed');
});
