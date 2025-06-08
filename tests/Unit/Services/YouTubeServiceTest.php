<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

/** @noinspection PhpUnhandledExceptionInspection */

use App\Services\YouTubeService;

beforeEach(function () {
    $this->googleClient = Mockery::mock(Google_Client::class);
    $this->googleYouTubeService = Mockery::mock(Google_Service_YouTube::class);

    // Set up common expectations for the Google_Client mock
    $this->googleClient
        ->shouldReceive('setClientId')
        ->once()
        ->with('test-client-id');
    $this->googleClient
        ->shouldReceive('setClientSecret')
        ->once()
        ->with('test-client-secret');
    $this->googleClient
        ->shouldReceive('setRedirectUri')
        ->once()
        ->with('http://localhost/callback');
    $this->googleClient
        ->shouldReceive('setDeveloperKey')
        ->once()
        ->with('test-api-key');
    $this->googleClient
        ->shouldReceive('setScopes')
        ->once()
        ->with(['https://www.googleapis.com/auth/youtube.readonly']);
    $this->googleClient
        ->shouldReceive('setAccessType')
        ->once()
        ->with('offline');
    $this->googleClient
        ->shouldReceive('setPrompt')
        ->once()
        ->with('consent');

    // Create the YouTubeService with the mocked client
    $this->youtubeService = new YouTubeService([
        'client_id' => 'test-client-id',
        'client_secret' => 'test-client-secret',
        'redirect_uri' => 'http://localhost/callback',
        'api_key' => 'test-api-key',
    ], $this->googleClient);

    // Replace the private YouTube service with our mock using reflection
    $reflection = new ReflectionClass($this->youtubeService);
    $youtubeProperty = $reflection->getProperty('youtube');
    $youtubeProperty->setValue($this->youtubeService, $this->googleYouTubeService);
});

test('getAuthUrl returns the correct auth URL', function () {
    $expectedUrl = 'https://accounts.google.com/o/oauth2/v2/auth?response_type=code&access_type=offline&client_id=test-client-id&redirect_uri=http%3A%2F%2Flocalhost%2Fcallback&state&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fyoutube.readonly&prompt=consent';

    $this->googleClient
        ->shouldReceive('createAuthUrl')
        ->once()
        ->andReturn($expectedUrl);

    $authUrl = $this->youtubeService->getAuthUrl();

    expect($authUrl)->toBe($expectedUrl);
});

test('fetchAccessTokenWithAuthCode returns the token from Google client', function () {
    $expectedToken = ['access_token' => 'test_token', 'expires_in' => 3600];

    $this->googleClient
        ->shouldReceive('fetchAccessTokenWithAuthCode')
        ->once()
        ->with('test_code')
        ->andReturn($expectedToken);

    $token = $this->youtubeService->fetchAccessTokenWithAuthCode('test_code');

    expect($token)->toBe($expectedToken);
});

test('setAccessToken sets the token on the Google client', function () {
    $token = ['access_token' => 'test_token', 'expires_in' => 3600];

    $this->googleClient
        ->shouldReceive('setAccessToken')
        ->once()
        ->with($token);

    $this->youtubeService->setAccessToken($token);
});

test('getPlaylists returns formatted playlists', function () {
    // Create mock playlist response
    $playlist = Mockery::mock(Google_Service_YouTube_Playlist::class);
    $snippet = Mockery::mock(Google_Service_YouTube_PlaylistSnippet::class);
    $contentDetails = Mockery::mock(Google_Service_YouTube_PlaylistContentDetails::class);
    $thumbnails = Mockery::mock(Google_Service_YouTube_ThumbnailDetails::class);
    $thumbnail = Mockery::mock(Google_Service_YouTube_Thumbnail::class);

    $playlist
        ->shouldReceive('getId')
        ->andReturn('playlist123');
    $playlist
        ->shouldReceive('getSnippet')
        ->andReturn($snippet);
    $playlist
        ->shouldReceive('getContentDetails')
        ->andReturn($contentDetails);

    $snippet
        ->shouldReceive('getTitle')
        ->andReturn('Test Playlist');
    $snippet
        ->shouldReceive('getDescription')
        ->andReturn('Test Description');
    $snippet
        ->shouldReceive('getThumbnails')
        ->andReturn($thumbnails);

    $thumbnails
        ->shouldReceive('getHigh')
        ->andReturn($thumbnail);
    $thumbnail
        ->shouldReceive('getUrl')
        ->andReturn('https://example.com/thumbnail.jpg');

    $contentDetails
        ->shouldReceive('getItemCount')
        ->andReturn(10);

    // Create mock response
    $playlistsResponse = Mockery::mock(Google_Service_YouTube_PlaylistListResponse::class);
    $playlistsResponse
        ->shouldReceive('getItems')
        ->andReturn([$playlist]);

    // Mock YouTube playlists service
    $playlistsService = Mockery::mock();
    $this->googleYouTubeService->playlists = $playlistsService;

    $playlistsService
        ->shouldReceive('listPlaylists')
        ->once()
        ->with('snippet,contentDetails', ['mine' => true, 'maxResults' => 25])
        ->andReturn($playlistsResponse);

    $playlists = $this->youtubeService->getPlaylists();

    expect($playlists)
        ->toBeArray()
        ->and($playlists)
        ->toHaveCount(1)
        ->and($playlists[0]['id'])
        ->toBe('playlist123')
        ->and($playlists[0]['title'])
        ->toBe('Test Playlist')
        ->and($playlists[0]['description'])
        ->toBe('Test Description')
        ->and($playlists[0]['thumbnail'])
        ->toBe('https://example.com/thumbnail.jpg')
        ->and($playlists[0]['itemCount'])
        ->toBe(10);
});

test('getPlaylistItems returns formatted playlist items', function () {
    // Create mock playlist item response
    $playlistItem = Mockery::mock(Google_Service_YouTube_PlaylistItem::class);
    $snippet = Mockery::mock(Google_Service_YouTube_PlaylistItemSnippet::class);
    $contentDetails = Mockery::mock(Google_Service_YouTube_PlaylistItemContentDetails::class);
    $thumbnails = Mockery::mock(Google_Service_YouTube_ThumbnailDetails::class);
    $thumbnail = Mockery::mock(Google_Service_YouTube_Thumbnail::class);

    $playlistItem
        ->shouldReceive('getId')
        ->andReturn('item123');
    $playlistItem
        ->shouldReceive('getSnippet')
        ->andReturn($snippet);
    $playlistItem
        ->shouldReceive('getContentDetails')
        ->andReturn($contentDetails);

    $snippet
        ->shouldReceive('getTitle')
        ->andReturn('Test Video');
    $snippet
        ->shouldReceive('getDescription')
        ->andReturn('Test Video Description');
    $snippet
        ->shouldReceive('getThumbnails')
        ->andReturn($thumbnails);
    $snippet
        ->shouldReceive('getPublishedAt')
        ->andReturn('2023-01-01T00:00:00Z');

    $thumbnails
        ->shouldReceive('getHigh')
        ->andReturn($thumbnail);
    $thumbnail
        ->shouldReceive('getUrl')
        ->andReturn('https://example.com/video-thumbnail.jpg');

    $contentDetails
        ->shouldReceive('getVideoId')
        ->andReturn('video123');

    // Create mock response
    $playlistItemsResponse = Mockery::mock(Google_Service_YouTube_PlaylistItemListResponse::class);
    $playlistItemsResponse
        ->shouldReceive('getItems')
        ->andReturn([$playlistItem]);

    // Mock YouTube playlistItems service
    $playlistItemsService = Mockery::mock();
    $this->googleYouTubeService->playlistItems = $playlistItemsService;

    $playlistItemsService
        ->shouldReceive('listPlaylistItems')
        ->once()
        ->with('snippet,contentDetails', ['playlistId' => 'playlist123', 'maxResults' => 50])
        ->andReturn($playlistItemsResponse);

    $items = $this->youtubeService->getPlaylistItems('playlist123');

    expect($items)
        ->toBeArray()
        ->and($items)
        ->toHaveCount(1)
        ->and($items[0]['id'])
        ->toBe('item123')
        ->and($items[0]['title'])
        ->toBe('Test Video')
        ->and($items[0]['description'])
        ->toBe('Test Video Description')
        ->and($items[0]['thumbnail'])
        ->toBe('https://example.com/video-thumbnail.jpg')
        ->and($items[0]['videoId'])
        ->toBe('video123')
        ->and($items[0]['publishedAt'])
        ->toBe('2023-01-01T00:00:00Z');
});
