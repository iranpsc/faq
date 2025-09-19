<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/callback', [AuthController::class, 'callback'])->name('auth.callback');

Route::get('/{any}', function () {
    return app()->version();
})->where('any', '.*')->name('home');
