<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\CommentController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('questions', QuestionController::class);

// Question voting routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('questions/{question}/vote', [QuestionController::class, 'vote']);
});

Route::apiResource('tags', TagController::class)->only(['index']);

Route::apiResource('questions.answers', AnswerController::class)->shallow()->only(['store', 'update', 'destroy']);
Route::apiResource('questions.comments', CommentController::class)->shallow()->only(['index', 'store']);
Route::apiResource('answers.comments', CommentController::class)->shallow()->only(['index', 'store']);
Route::apiResource('comments', CommentController::class)->shallow()->only(['update', 'destroy']);
Route::post('comments/{comment}/vote', [CommentController::class, 'vote'])->middleware('auth:sanctum');
Route::post('answers/{answer}/vote', [AnswerController::class, 'vote'])->middleware('auth:sanctum');

Route::apiResource('categories', CategoryController::class);
