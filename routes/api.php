<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/redirect', [AuthController::class, 'redirect'])->name('auth.redirect');
        Route::get('/callback', [AuthController::class, 'callback'])->name('auth.callback');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::apiResource('questions', QuestionController::class);
Route::apiResource('categories', CategoryController::class)->only('index');
Route::apiResource('tags', TagController::class)->only('index');
