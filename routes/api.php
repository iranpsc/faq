<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
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

// Question recommendation and popular routes (must be before resource routes)
Route::prefix('questions')->group(function () {
    Route::get('/recommended', [DashboardController::class, 'recommendedQuestions']);
    Route::get('/popular', [DashboardController::class, 'popularQuestions']);
    Route::get('/search', [QuestionController::class, 'search']);
});

Route::apiResource('questions', QuestionController::class);

// Category popular route (must be before resource routes)
Route::get('categories/popular', [CategoryController::class, 'popular']);

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

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/active-users', [DashboardController::class, 'activeUsers']);
});
