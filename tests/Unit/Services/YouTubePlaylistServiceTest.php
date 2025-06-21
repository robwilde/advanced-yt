<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserPlaylist;
use App\Services\YouTubeService;
use Google_Client;
use Google_Service_YouTube;
use Google_Service_YouTube_Playlist;
use Google_Service_YouTube_PlaylistSnippet;
use Google_Service_YouTube_PlaylistStatus;
use Google_Service_YouTube_PlaylistsListResponse;
use Google_Service_YouTube_PlaylistItem;
use Google_Service_YouTube_PlaylistItemSnippet;
use Google_Service_YouTube_ResourceId;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->user = User::factory()->create();
    Auth::login($this->user);

    // Mock Google Client with all expected setup methods
    $this->mockClient = Mockery::mock(Google_Client::class);
    $this->mockClient->shouldReceive('setClientId')->once();
    $this->mockClient->shouldReceive('setClientSecret')->once();
    $this->mockClient->shouldReceive('setRedirectUri')->once();
    $this->mockClient->shouldReceive('setDeveloperKey')->once();
    $this->mockClient->shouldReceive('setScopes')->once();
    $this->mockClient->shouldReceive('setAccessType')->once();
    $this->mockClient->shouldReceive('setPrompt')->once();
    $this->mockClient->shouldReceive('setAccessToken')->once();
    
    $this->mockYouTube = Mockery::mock(Google_Service_YouTube::class);
    
    $this->service = new YouTubeService([
        'client_id' => 'test_client_id',
        'client_secret' => 'test_client_secret',
        'redirect_uri' => 'http://localhost/callback',
        'api_key' => 'test_api_key',
    ], $this->mockClient);

    // Mock the YouTube service initialization
    $this->service->setAccessToken(['access_token' => 'test_token']);
});

afterEach(function () {
    Mockery::close();
});

describe('YouTube Playlist Service', function () {
    describe('createPlaylist', function () {
        it('can create a new playlist', function () {
            // Mock YouTube service
            $mockPlaylists = Mockery::mock();
            $this->mockYouTube->playlists = $mockPlaylists;

            // Mock playlist response
            $mockPlaylist = Mockery::mock(Google_Service_YouTube_Playlist::class);
            $mockSnippet = Mockery::mock(Google_Service_YouTube_PlaylistSnippet::class);
            $mockStatus = Mockery::mock(Google_Service_YouTube_PlaylistStatus::class);

            $mockPlaylist->shouldReceive('getId')->andReturn('PLtest123');
            $mockPlaylist->shouldReceive('getSnippet')->andReturn($mockSnippet);
            $mockPlaylist->shouldReceive('getStatus')->andReturn($mockStatus);

            $mockSnippet->shouldReceive('getTitle')->andReturn('Test Playlist');
            $mockSnippet->shouldReceive('getDescription')->andReturn('Test Description');
            $mockSnippet->shouldReceive('getThumbnails')->andReturn(null);

            $mockStatus->shouldReceive('getPrivacyStatus')->andReturn('private');

            $mockPlaylists->shouldReceive('insert')
                ->with('snippet,status', Mockery::type(Google_Service_YouTube_Playlist::class))
                ->andReturn($mockPlaylist);

            // Use reflection to set the YouTube service
            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('youtube');
            $property->setAccessible(true);
            $property->setValue($this->service, $this->mockYouTube);

            $result = $this->service->createPlaylist('Test Playlist', 'Test Description', 'private');

            expect($result)
                ->toBeArray()
                ->id->toBe('PLtest123')
                ->title->toBe('Test Playlist')
                ->description->toBe('Test Description')
                ->privacyStatus->toBe('private')
                ->itemCount->toBe(0);
        });
    });

    describe('updatePlaylist', function () {
        it('can update an existing playlist', function () {
            // Mock YouTube service
            $mockPlaylists = Mockery::mock();
            $this->mockYouTube->playlists = $mockPlaylists;

            // Mock existing playlist
            $mockExistingPlaylist = Mockery::mock(Google_Service_YouTube_Playlist::class);
            $mockSnippet = Mockery::mock(Google_Service_YouTube_PlaylistSnippet::class);
            $mockStatus = Mockery::mock(Google_Service_YouTube_PlaylistStatus::class);
            $mockResponse = Mockery::mock(Google_Service_YouTube_PlaylistsListResponse::class);

            $mockResponse->shouldReceive('getItems')->andReturn([$mockExistingPlaylist]);
            $mockExistingPlaylist->shouldReceive('getSnippet')->andReturn($mockSnippet);
            $mockExistingPlaylist->shouldReceive('getStatus')->andReturn($mockStatus);
            $mockExistingPlaylist->shouldReceive('setSnippet')->with($mockSnippet);
            $mockExistingPlaylist->shouldReceive('setStatus')->with($mockStatus);

            $mockSnippet->shouldReceive('setTitle')->with('Updated Title');
            $mockSnippet->shouldReceive('getTitle')->andReturn('Updated Title');
            $mockSnippet->shouldReceive('getDescription')->andReturn('Updated Description');
            $mockSnippet->shouldReceive('getThumbnails')->andReturn(null);

            $mockStatus->shouldReceive('setPrivacyStatus')->with('public');
            $mockStatus->shouldReceive('getPrivacyStatus')->andReturn('public');

            $mockPlaylists->shouldReceive('listPlaylists')
                ->with('snippet,status', ['id' => 'PLtest123'])
                ->andReturn($mockResponse);

            $mockPlaylists->shouldReceive('update')
                ->with('snippet,status', $mockExistingPlaylist)
                ->andReturn($mockExistingPlaylist);

            $mockExistingPlaylist->shouldReceive('getId')->andReturn('PLtest123');

            // Use reflection to set the YouTube service
            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('youtube');
            $property->setAccessible(true);
            $property->setValue($this->service, $this->mockYouTube);

            $result = $this->service->updatePlaylist('PLtest123', 'Updated Title', null, 'public');

            expect($result)
                ->toBeArray()
                ->id->toBe('PLtest123')
                ->title->toBe('Updated Title')
                ->privacyStatus->toBe('public');
        });

        it('throws exception when playlist not found', function () {
            $mockPlaylists = Mockery::mock();
            $this->mockYouTube->playlists = $mockPlaylists;
            $mockResponse = Mockery::mock(Google_Service_YouTube_PlaylistsListResponse::class);

            $mockResponse->shouldReceive('getItems')->andReturn([]);
            $mockPlaylists->shouldReceive('listPlaylists')
                ->with('snippet,status', ['id' => 'PLnonexistent'])
                ->andReturn($mockResponse);

            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('youtube');
            $property->setAccessible(true);
            $property->setValue($this->service, $this->mockYouTube);

            expect(fn () => $this->service->updatePlaylist('PLnonexistent', 'New Title'))
                ->toThrow(RuntimeException::class, 'Playlist not found');
        });
    });

    describe('deletePlaylist', function () {
        it('can delete a playlist successfully', function () {
            $mockPlaylists = Mockery::mock();
            $this->mockYouTube->playlists = $mockPlaylists;

            $mockPlaylists->shouldReceive('delete')
                ->with('PLtest123')
                ->once();

            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('youtube');
            $property->setAccessible(true);
            $property->setValue($this->service, $this->mockYouTube);

            $result = $this->service->deletePlaylist('PLtest123');

            expect($result)->toBeTrue();
        });

        it('handles deletion errors gracefully', function () {
            Log::shouldReceive('error')->once();

            // Pre-set the YouTube service to avoid initYouTubeService() call
            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('youtube');
            $property->setAccessible(true);
            $property->setValue($this->service, $this->mockYouTube);

            $mockPlaylists = Mockery::mock();
            $this->mockYouTube->playlists = $mockPlaylists;

            $mockPlaylists->shouldReceive('delete')
                ->with('PLtest123')
                ->andThrow(new Exception('API Error'));

            $result = $this->service->deletePlaylist('PLtest123');

            expect($result)->toBeFalse();
        });
    });

    describe('addVideoToPlaylist', function () {
        it('can add a video to a playlist', function () {
            $mockPlaylistItems = Mockery::mock();
            $this->mockYouTube->playlistItems = $mockPlaylistItems;

            $mockPlaylistItem = Mockery::mock(Google_Service_YouTube_PlaylistItem::class);
            $mockSnippet = Mockery::mock(Google_Service_YouTube_PlaylistItemSnippet::class);
            $mockResourceId = Mockery::mock(Google_Service_YouTube_ResourceId::class);

            $mockPlaylistItem->shouldReceive('getId')->andReturn('PLItemtest123');
            $mockPlaylistItem->shouldReceive('getSnippet')->andReturn($mockSnippet);

            $mockSnippet->shouldReceive('getPlaylistId')->andReturn('PLtest123');
            $mockSnippet->shouldReceive('getResourceId')->andReturn($mockResourceId);
            $mockSnippet->shouldReceive('getTitle')->andReturn('Test Video');
            $mockSnippet->shouldReceive('getPosition')->andReturn(0);

            $mockResourceId->shouldReceive('getVideoId')->andReturn('VIDtest123');

            $mockPlaylistItems->shouldReceive('insert')
                ->with('snippet', Mockery::type(Google_Service_YouTube_PlaylistItem::class))
                ->andReturn($mockPlaylistItem);

            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('youtube');
            $property->setAccessible(true);
            $property->setValue($this->service, $this->mockYouTube);

            $result = $this->service->addVideoToPlaylist('PLtest123', 'VIDtest123');

            expect($result)
                ->toBeArray()
                ->id->toBe('PLItemtest123')
                ->playlistId->toBe('PLtest123')
                ->videoId->toBe('VIDtest123')
                ->title->toBe('Test Video')
                ->position->toBe(0);
        });
    });

    describe('syncUserPlaylists', function () {
        it('can sync user playlists from YouTube', function () {
            $mockPlaylists = Mockery::mock();
            $this->mockYouTube->playlists = $mockPlaylists;

            // Mock playlist data
            $mockPlaylist = Mockery::mock(Google_Service_YouTube_Playlist::class);
            $mockSnippet = Mockery::mock(Google_Service_YouTube_PlaylistSnippet::class);
            $mockStatus = Mockery::mock(Google_Service_YouTube_PlaylistStatus::class);
            $mockContentDetails = Mockery::mock();
            $mockResponse = Mockery::mock(Google_Service_YouTube_PlaylistsListResponse::class);

            $mockResponse->shouldReceive('getItems')->andReturn([$mockPlaylist]);
            $mockResponse->shouldReceive('getNextPageToken')->andReturn(null);

            $mockPlaylist->shouldReceive('getId')->andReturn('PLtest123');
            $mockPlaylist->shouldReceive('getSnippet')->andReturn($mockSnippet);
            $mockPlaylist->shouldReceive('getStatus')->andReturn($mockStatus);
            $mockPlaylist->shouldReceive('getContentDetails')->andReturn($mockContentDetails);

            $mockSnippet->shouldReceive('getTitle')->andReturn('Test Playlist');
            $mockSnippet->shouldReceive('getDescription')->andReturn('Test Description');
            $mockSnippet->shouldReceive('getThumbnails')->andReturn(null);

            $mockStatus->shouldReceive('getPrivacyStatus')->andReturn('private');
            $mockContentDetails->shouldReceive('getItemCount')->andReturn(5);

            $mockPlaylists->shouldReceive('listPlaylists')
                ->with('snippet,contentDetails,status', Mockery::type('array'))
                ->andReturn($mockResponse);

            $reflection = new ReflectionClass($this->service);
            $property = $reflection->getProperty('youtube');
            $property->setAccessible(true);
            $property->setValue($this->service, $this->mockYouTube);

            $result = $this->service->syncUserPlaylists();

            expect($result)
                ->toBeArray()
                ->success->toBeTrue()
                ->total->toBe(1);

            $this->assertDatabaseHas('user_playlists', [
                'user_id' => $this->user->id,
                'youtube_playlist_id' => 'PLtest123',
                'title' => 'Test Playlist',
                'privacy_status' => 'private',
                'item_count' => 5,
            ]);
        });
    });
});