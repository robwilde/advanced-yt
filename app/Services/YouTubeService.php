<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\YouTubeSubscription;
use App\Models\UserYouTubeAccount;
use App\Models\UserPlaylist;
use App\Services\Contracts\YouTubeServiceInterface;
use Google\Service\Exception;
use Google_Client;
use Google_Service_YouTube;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;

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
            'https://www.googleapis.com/auth/youtube', // Full access for playlist management
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

    /**
     * @throws Exception
     */
    public function getSubscriptions(int $maxResults = 50): array
    {
        $this->initYouTubeService();

        $response = $this->youtube->subscriptions->listSubscriptions('snippet', [
            'mine' => true,
            'maxResults' => $maxResults,
        ]);

        return $this->formatSubscriptionsResponse($response->getItems());
    }

    /**
     * Get cached subscriptions with pagination support
     */
    public function getCachedSubscriptions(int $page = 1, int $perPage = 50): array
    {
        $userId = Auth::id();
        if (!$userId) {
            return ['items' => [], 'total' => 0, 'hasMore' => false];
        }

        $query = YouTubeSubscription::forUser($userId)
            ->orderBy('subscribed_at', 'desc');

        $total = $query->count();
        $subscriptions = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(fn($sub) => [
                'id' => $sub->subscription_id,
                'title' => $sub->channel_title,
                'description' => $sub->channel_description,
                'thumbnail' => $sub->thumbnail_url,
                'channelId' => $sub->channel_id,
                'publishedAt' => $sub->subscribed_at->toISOString(),
            ])
            ->toArray();

        return [
            'items' => $subscriptions,
            'total' => $total,
            'hasMore' => ($page * $perPage) < $total,
            'currentPage' => $page,
            'perPage' => $perPage,
        ];
    }

    /**
     * Fetch all subscriptions from YouTube API and cache them
     */
    public function syncAllSubscriptions(): array
    {
        $userId = Auth::id();
        if (!$userId) {
            throw new \RuntimeException('User not authenticated');
        }

        $this->initYouTubeService();

        $allSubscriptions = [];
        $nextPageToken = null;
        $totalFetched = 0;

        try {
            DB::beginTransaction();

            do {
                $params = [
                    'mine' => true,
                    'maxResults' => 50,
                ];

                if ($nextPageToken) {
                    $params['pageToken'] = $nextPageToken;
                }

                Log::info('Fetching YouTube subscriptions page', ['params' => $params]);

                $response = $this->youtube->subscriptions->listSubscriptions('snippet', $params);
                $items = $response->getItems();
                $nextPageToken = $response->getNextPageToken();

                if (!empty($items)) {
                    $formattedItems = $this->formatSubscriptionsResponse($items);
                    $allSubscriptions = array_merge($allSubscriptions, $formattedItems);
                    $totalFetched += count($items);

                    Log::info('Fetched subscriptions batch', [
                        'count' => count($items),
                        'total' => $totalFetched,
                        'hasNextPage' => !empty($nextPageToken)
                    ]);
                }

                // Rate limiting - small delay between requests
                if ($nextPageToken) {
                    usleep(100000); // 100ms delay
                }

            } while ($nextPageToken && $totalFetched < 2000); // Safety limit

            // Clear existing subscriptions for this user
            YouTubeSubscription::where('user_id', $userId)->delete();

            // Insert new subscriptions
            $cachedAt = now();
            $subscriptionsToInsert = [];

            foreach ($allSubscriptions as $subscription) {
                $subscriptionsToInsert[] = [
                    'user_id' => $userId,
                    'subscription_id' => $subscription['id'],
                    'channel_id' => $subscription['channelId'],
                    'channel_title' => $subscription['title'],
                    'channel_description' => $subscription['description'] ?? '',
                    'thumbnail_url' => $subscription['thumbnail'],
                    'subscribed_at' => Carbon::parse($subscription['publishedAt']),
                    'cached_at' => $cachedAt,
                    'created_at' => $cachedAt,
                    'updated_at' => $cachedAt,
                ];
            }

            if (!empty($subscriptionsToInsert)) {
                // Insert in chunks to avoid memory issues
                collect($subscriptionsToInsert)->chunk(100)->each(function ($chunk) {
                    YouTubeSubscription::insert($chunk->toArray());
                });
            }

            // Update sync timestamp
            $this->updateLastSyncTime($userId);

            DB::commit();

            Log::info('Successfully synced YouTube subscriptions', [
                'userId' => $userId,
                'totalSynced' => count($allSubscriptions)
            ]);

            return [
                'success' => true,
                'total' => count($allSubscriptions),
                'message' => 'Successfully synced ' . count($allSubscriptions) . ' subscriptions'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync YouTube subscriptions', [
                'userId' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Check if user has any cached subscriptions
     */
    public function hasCachedSubscriptions(int $userId): bool
    {
        return YouTubeSubscription::where('user_id', $userId)->exists();
    }

    /**
     * Check if user needs subscription sync
     */
    public function needsSync(int $userId): bool
    {
        $account = UserYouTubeAccount::where('user_id', $userId)->first();
        return $account === null || $account->needsSync();
    }

    /**
     * Store or update YouTube account info
     *
     * @throws Exception
     * @throws JsonException
     */
    public function storeYouTubeAccount(array $tokenData): void
    {
        $userId = Auth::id();
        if (!$userId) {
            throw new RuntimeException('User not authenticated');
        }

        // Get channel info to store channel ID
        $this->setAccessToken($tokenData);
        $this->initYouTubeService();

        $channelResponse = $this->youtube->channels->listChannels('snippet', ['mine' => true]);
        $channels = $channelResponse->getItems();

        if (empty($channels)) {
            throw new RuntimeException('No YouTube channel found for authenticated user');
        }

        $channel = $channels[0];
        $channelId = $channel->getId();

        UserYouTubeAccount::updateOrCreate(
            ['user_id' => $userId],
            [
                'youtube_channel_id' => $channelId,
                'access_token' => json_encode($tokenData, JSON_THROW_ON_ERROR),
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'token_expires_at' => isset($tokenData['expires_in'])
                    ? now()->addSeconds($tokenData['expires_in'])
                    : null,
            ]
        );
    }

    /**
     * Create a new YouTube playlist
     *
     * @throws Exception
     */
    public function createPlaylist(string $title, ?string $description = null, string $privacyStatus = 'private'): array
    {
        $this->initYouTubeService();

        $playlist = new \Google_Service_YouTube_Playlist();
        $playlistSnippet = new \Google_Service_YouTube_PlaylistSnippet();
        $playlistStatus = new \Google_Service_YouTube_PlaylistStatus();

        $playlistSnippet->setTitle($title);
        if ($description) {
            $playlistSnippet->setDescription($description);
        }

        $playlistStatus->setPrivacyStatus($privacyStatus);

        $playlist->setSnippet($playlistSnippet);
        $playlist->setStatus($playlistStatus);

        $response = $this->youtube->playlists->insert('snippet,status', $playlist);

        $thumbnails = $response->getSnippet()->getThumbnails();
        $thumbnail = null;
        if ($thumbnails && $thumbnails->getHigh()) {
            $thumbnail = $thumbnails->getHigh()->getUrl();
        }

        return [
            'id' => $response->getId(),
            'title' => $response->getSnippet()->getTitle(),
            'description' => $response->getSnippet()->getDescription() ?? '',
            'thumbnail' => $thumbnail,
            'privacyStatus' => $response->getStatus()->getPrivacyStatus(),
            'itemCount' => 0,
        ];
    }

    /**
     * Update an existing YouTube playlist
     *
     * @throws Exception
     */
    public function updatePlaylist(string $playlistId, ?string $title = null, ?string $description = null, ?string $privacyStatus = null): array
    {
        $this->initYouTubeService();

        // First get the current playlist
        $response = $this->youtube->playlists->listPlaylists('snippet,status', [
            'id' => $playlistId,
        ]);

        $playlists = $response->getItems();
        if (empty($playlists)) {
            throw new RuntimeException('Playlist not found');
        }

        $playlist = $playlists[0];
        $snippet = $playlist->getSnippet();
        $status = $playlist->getStatus();

        // Update fields if provided
        if ($title !== null) {
            $snippet->setTitle($title);
        }
        if ($description !== null) {
            $snippet->setDescription($description);
        }
        if ($privacyStatus !== null) {
            $status->setPrivacyStatus($privacyStatus);
        }

        $playlist->setSnippet($snippet);
        $playlist->setStatus($status);

        $updatedPlaylist = $this->youtube->playlists->update('snippet,status', $playlist);

        $thumbnails = $updatedPlaylist->getSnippet()->getThumbnails();
        $thumbnail = null;
        if ($thumbnails && $thumbnails->getHigh()) {
            $thumbnail = $thumbnails->getHigh()->getUrl();
        }

        return [
            'id' => $updatedPlaylist->getId(),
            'title' => $updatedPlaylist->getSnippet()->getTitle(),
            'description' => $updatedPlaylist->getSnippet()->getDescription() ?? '',
            'thumbnail' => $thumbnail,
            'privacyStatus' => $updatedPlaylist->getStatus()->getPrivacyStatus(),
        ];
    }

    /**
     * Delete a YouTube playlist
     *
     * @throws Exception
     */
    public function deletePlaylist(string $playlistId): bool
    {
        $this->initYouTubeService();

        try {
            $this->youtube->playlists->delete($playlistId);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete YouTube playlist', [
                'playlistId' => $playlistId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Add a video to a playlist
     *
     * @throws Exception
     */
    public function addVideoToPlaylist(string $playlistId, string $videoId): array
    {
        $this->initYouTubeService();

        $playlistItem = new \Google_Service_YouTube_PlaylistItem();
        $playlistItemSnippet = new \Google_Service_YouTube_PlaylistItemSnippet();
        $playlistItemResource = new \Google_Service_YouTube_ResourceId();

        $playlistItemResource->setVideoId($videoId);
        $playlistItemResource->setKind('youtube#video');

        $playlistItemSnippet->setPlaylistId($playlistId);
        $playlistItemSnippet->setResourceId($playlistItemResource);

        $playlistItem->setSnippet($playlistItemSnippet);

        $response = $this->youtube->playlistItems->insert('snippet', $playlistItem);

        return [
            'id' => $response->getId(),
            'playlistId' => $response->getSnippet()->getPlaylistId(),
            'videoId' => $response->getSnippet()->getResourceId()->getVideoId(),
            'title' => $response->getSnippet()->getTitle(),
            'position' => $response->getSnippet()->getPosition(),
        ];
    }

    /**
     * Remove a video from a playlist
     *
     * @throws Exception
     */
    public function removeVideoFromPlaylist(string $playlistItemId): bool
    {
        $this->initYouTubeService();

        try {
            $this->youtube->playlistItems->delete($playlistItemId);
            return true;
        } catch (Exception $e) {
            Log::error('Failed to remove video from playlist', [
                'playlistItemId' => $playlistItemId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Sync user's playlists from YouTube API
     *
     * @throws Exception
     */
    public function syncUserPlaylists(): array
    {
        $userId = Auth::id();
        if (!$userId) {
            throw new RuntimeException('User not authenticated');
        }

        $this->initYouTubeService();

        $allPlaylists = [];
        $nextPageToken = null;

        try {
            DB::beginTransaction();

            do {
                $params = [
                    'mine' => true,
                    'maxResults' => 50,
                    'part' => 'snippet,contentDetails,status',
                ];

                if ($nextPageToken) {
                    $params['pageToken'] = $nextPageToken;
                }

                $response = $this->youtube->playlists->listPlaylists('snippet,contentDetails,status', $params);
                $items = $response->getItems();
                $nextPageToken = $response->getNextPageToken();

                foreach ($items as $item) {
                    $snippet = $item->getSnippet();
                    $contentDetails = $item->getContentDetails();
                    $status = $item->getStatus();

                    $thumbnails = $snippet->getThumbnails();
                    $thumbnail = null;
                    if ($thumbnails && $thumbnails->getHigh()) {
                        $thumbnail = $thumbnails->getHigh()->getUrl();
                    }

                    $playlistData = [
                        'user_id' => $userId,
                        'youtube_playlist_id' => $item->getId(),
                        'title' => $snippet->getTitle(),
                        'description' => $snippet->getDescription() ?? '',
                        'thumbnail_url' => $thumbnail,
                        'privacy_status' => $status->getPrivacyStatus(),
                        'item_count' => $contentDetails->getItemCount(),
                        'is_managed' => true,
                        'last_synced_at' => now(),
                    ];

                    // Update or create playlist
                    UserPlaylist::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'youtube_playlist_id' => $item->getId(),
                        ],
                        $playlistData
                    );

                    $allPlaylists[] = $playlistData;
                }

            } while ($nextPageToken);

            DB::commit();

            Log::info('Successfully synced YouTube playlists', [
                'userId' => $userId,
                'totalSynced' => count($allPlaylists)
            ]);

            return [
                'success' => true,
                'total' => count($allPlaylists),
                'message' => 'Successfully synced ' . count($allPlaylists) . ' playlists'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to sync YouTube playlists', [
                'userId' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    private function updateLastSyncTime(int $userId): void
    {
        UserYouTubeAccount::where('user_id', $userId)
            ->update(['last_synced_at' => now()]);
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

    private function formatSubscriptionsResponse(array $items): array
    {
        $subscriptions = [];

        foreach ($items as $item) {
            $snippet = $item->getSnippet();
            $thumbnails = $snippet->getThumbnails();
            $highThumbnail = $thumbnails && $thumbnails->getHigh() ? $thumbnails->getHigh()->getUrl() : null;

            $subscriptions[] = [
                'id' => $item->getId(),
                'title' => $snippet->getTitle(),
                'description' => $snippet->getDescription() ?? '',
                'thumbnail' => $highThumbnail,
                'channelId' => $snippet->getResourceId()->getChannelId(),
                'publishedAt' => $snippet->getPublishedAt(),
            ];
        }

        return $subscriptions;
    }
}
