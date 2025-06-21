<?php

declare(strict_types=1);

use App\Http\Controllers\PlaylistsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\YouTubeAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', PlaylistsController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('subscriptions', SubscriptionsController::class)
    ->middleware(['auth', 'verified'])
    ->name('subscriptions');

Route::post('subscriptions/sync', [SubscriptionsController::class, 'sync'])
    ->middleware(['auth', 'verified'])
    ->name('subscriptions.sync');

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
