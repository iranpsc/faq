<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
})->name('home');

Route::get('/question/{id}', function () {
    return view('question');
})->name('question.show');
