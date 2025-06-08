<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface YouTubeServiceInterface
{
    public function getAuthUrl(): string;

    public function fetchAccessTokenWithAuthCode(string $code): array;

    public function setAccessToken(array $token): void;

    public function getPlaylists(int $maxResults = 25): array;

    public function getPlaylistItems(string $playlistId): array;

    public function getSubscriptions(int $maxResults = 50): array;
}
