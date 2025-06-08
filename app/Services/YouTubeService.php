<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Contracts\YouTubeServiceInterface;
use Google\Service\Exception;
use Google_Client;
use Google_Service_YouTube;

final class YouTubeService implements YouTubeServiceInterface
{
    private readonly Google_Client $client;
    private ?Google_Service_YouTube $youtube = null;

    public function __construct(array $youtubeConfig, ?Google_Client $client = null)
    {
        $this->client = $client ?? new Google_Client();
        $this->client->setClientId($youtubeConfig['client_id'] ?? '');
        $this->client->setClientSecret($youtubeConfig['client_secret'] ?? '');
        $this->client->setRedirectUri($youtubeConfig['redirect_uri'] ?? '');
        $this->client->setDeveloperKey($youtubeConfig['api_key'] ?? '');
        $this->client->setScopes([
            'https://www.googleapis.com/auth/youtube.readonly',
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function fetchAccessTokenWithAuthCode(string $code): array
    {
        return $this->client->fetchAccessTokenWithAuthCode($code);
    }

    public function setAccessToken(array $token): void
    {
        $this->client->setAccessToken($token);
    }

    /**
     * @throws Exception
     */
    public function getPlaylists(int $maxResults = 25): array
    {
        $this->initYouTubeService();

        $response = $this->youtube->playlists->listPlaylists('snippet,contentDetails', [
            'mine' => true,
            'maxResults' => $maxResults,
        ]);

        return $this->formatPlaylistsResponse($response->getItems());
    }

    /**
     * @throws Exception
     */
    public function getPlaylistItems(string $playlistId): array
    {
        $response = $this->youtube->playlistItems->listPlaylistItems(
            'snippet,contentDetails',
            [
                'playlistId' => $playlistId,
                'maxResults' => 50,
            ]
        );

        $items = [];
        foreach ($response->getItems() as $item) {
            $snippet = $item->getSnippet();
            $contentDetails = $item->getContentDetails();

            $thumbnails = $snippet->getThumbnails();
            $highThumbnail = $thumbnails && $thumbnails->getHigh() ? $thumbnails->getHigh()->getUrl() : null;

            $items[] = [
                'id' => $item->getId(),
                'title' => $snippet->getTitle(),
                'description' => $snippet->getDescription(),
                'thumbnail' => $highThumbnail,
                'videoId' => $contentDetails->getVideoId(),
                'publishedAt' => $snippet->getPublishedAt(),
            ];
        }
        return $items;
    }

    private function initYouTubeService(): void
    {
        if ($this->youtube === null) {
            $this->youtube = new Google_Service_YouTube($this->client);
        }
    }

    private function formatPlaylistsResponse(array $items): array
    {
        $playlists = [];

        foreach ($items as $item) {
            $playlists[] = [
                'id' => $item->getId(),
                'title' => $item->getSnippet()->getTitle(),
                'description' => $item->getSnippet()->getDescription(),
                'thumbnail' => $item->getSnippet()->getThumbnails()->getHigh()->getUrl(),
                'itemCount' => $item->getContentDetails()->getItemCount(),
            ];
        }

        return $playlists;
    }

    private function formatPlaylistItemsResponse(array $items): array
    {
        $playlistItems = [];

        foreach ($items as $item) {
            $playlistItems[] = [
                'id' => $item->getId(),
                'title' => $item->getSnippet()->getTitle(),
                'description' => $item->getSnippet()->getDescription(),
                'thumbnail' => $item->getSnippet()->getThumbnails()->getHigh()->getUrl(),
                'videoId' => $item->getContentDetails()->getVideoId(),
                'publishedAt' => $item->getSnippet()->getPublishedAt(),
            ];
        }

        return $playlistItems;
    }
}
