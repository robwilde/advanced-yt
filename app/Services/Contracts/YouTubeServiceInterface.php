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

    // Playlist CRUD operations
    public function createPlaylist(string $title, ?string $description = null, string $privacyStatus = 'private'): array;

    public function updatePlaylist(string $playlistId, ?string $title = null, ?string $description = null, ?string $privacyStatus = null): array;

    public function deletePlaylist(string $playlistId): bool;

    public function addVideoToPlaylist(string $playlistId, string $videoId): array;

    public function removeVideoFromPlaylist(string $playlistItemId): bool;

    public function syncUserPlaylists(): array;
}
