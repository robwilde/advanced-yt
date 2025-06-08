<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Contracts\YouTubeServiceInterface;
use App\Services\YouTubeService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the YouTube service implementation
        $this->app->bind(
            YouTubeService::class,
            function ($app) {
                $youtubeConfig = config('services.youtube', []);
                return new YouTubeService($youtubeConfig);
            },
        );

        // Bind the interface to the implementation
        $this->app->bind(
            YouTubeServiceInterface::class,
            YouTubeService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
