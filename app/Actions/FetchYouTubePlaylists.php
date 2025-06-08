<?php

declare(strict_types=1);

namespace App\Actions;

use App\Services\Contracts\YouTubeServiceInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class FetchYouTubePlaylists
{
    public function __construct(
        private YouTubeServiceInterface $youTubeService,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(array $accessToken): array
    {
        return DB::transaction(function () use ($accessToken): array {
            $this->youTubeService->setAccessToken($accessToken);

            return $this->youTubeService->getPlaylists();
        });
    }
}
