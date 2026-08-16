<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::post('/applications', [ApplicationController::class, 'store'])->name('applications.store');
