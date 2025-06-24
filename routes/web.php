<?php

declare(strict_types=1);

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaylistsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\YouTubeAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('subscriptions', SubscriptionsController::class)
    ->middleware(['auth', 'verified'])
    ->name('subscriptions');

Route::post('subscriptions/sync', [SubscriptionsController::class, 'sync'])
    ->middleware(['auth', 'verified'])
    ->name('subscriptions.sync');

Route::get('categories', [CategoryController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('categories.index');

Route::get('playlists', [PlaylistsController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('playlists.index');

Route::post('playlists', [PlaylistsController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('playlists.store');

Route::put('playlists/{playlist}', [PlaylistsController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('playlists.update');

Route::delete('playlists/{playlist}', [PlaylistsController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('playlists.destroy');

Route::post('playlists/sync', [PlaylistsController::class, 'sync'])
    ->middleware(['auth', 'verified'])
    ->name('playlists.sync');

Route::get('playlists/{playlistId}/videos', App\Livewire\PlaylistVideos::class)
    ->middleware(['auth', 'verified'])
    ->name('playlists.videos');

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
