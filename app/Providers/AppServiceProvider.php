<?php

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
        $this->app->bind(
            YouTubeServiceInterface::class,
            function ($app) {
                $youtubeConfig = config('services.youtube', []);

                return new YouTubeService($youtubeConfig);
            },
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
