<?php

use App\Http\Controllers\Admin\OthersController;
use App\Http\Controllers\Web\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::controller(WebsiteController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/leaderboard', 'leaderboard')->name('leaderboard');
    Route::get('/contact-us', 'contactUs')->name('contact.us');
    Route::post('/contact-us', 'submitContact')->name('contact.submit');
});

Route::controller(OthersController::class)->group(function () {
    Route::middleware('guest')->get('/admin/login', 'login')->name('admin.login');
    Route::post('/test-mail', 'testMail')->name('test.mail');
    Route::get('/migrate', 'migrate')->name('migration');
    Route::get('/clear', 'clear')->name('clear');
    Route::get('/composer', 'composer')->name('composer');
    Route::get('/iseed', 'iseed')->name('iseed');
});
