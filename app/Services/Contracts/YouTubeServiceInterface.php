<?php

namespace App\Services\Contracts;

interface YouTubeServiceInterface
{
    public function getAuthUrl(): string;

    public function fetchAccessTokenWithAuthCode(string $code): array;

    public function setAccessToken(array $token): void;

    public function getPlaylists(int $maxResults = 25): array;

    public function getPlaylistItems(string $playlistId): array;
}
