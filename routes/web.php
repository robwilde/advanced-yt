<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\YouTubeAuthController;

Route::view('/', 'welcome');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// YouTube Authentication Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('auth/youtube', [YouTubeAuthController::class, 'redirect'])
        ->name('auth.youtube');
    Route::get('auth/youtube/callback', [YouTubeAuthController::class, 'callback'])
        ->name('auth.youtube.callback');
});

require __DIR__.'/auth.php';
