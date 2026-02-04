<?php

use App\Http\Controllers\Admin\OthersController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('web.welcome');
})->name('home');

Route::controller(OthersController::class)->group(function () {
    Route::middleware('guest')->get('/admin/login', 'login')->name('admin.login');
    Route::post('/test-mail', 'testMail')->name('test.mail');
    Route::get('/migrate', 'migrate')->name('migration');
    Route::get('/clear', 'clear')->name('clear');
    Route::get('/composer', 'composer')->name('composer');
    Route::get('/iseed', 'iseed')->name('iseed');
});
