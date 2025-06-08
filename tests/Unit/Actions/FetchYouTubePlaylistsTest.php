<?php

declare(strict_types=1);

use App\Actions\FetchYouTubePlaylists;
use App\Services\Contracts\YouTubeServiceInterface;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->youTubeService = Mockery::mock(YouTubeServiceInterface::class);
    $this->action = new FetchYouTubePlaylists($this->youTubeService);
});

test('handle method sets access token and returns playlists', function () {
    $accessToken = ['access_token' => 'test_token', 'expires_in' => 3600];
    $expectedPlaylists = [
        [
            'id' => 'playlist123',
            'title' => 'Test Playlist',
            'description' => 'Test Description',
            'thumbnail' => 'https://example.com/thumbnail.jpg',
            'itemCount' => 10,
        ],
    ];

    // Mock DB transaction to execute the callback
    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    // Set expectations on YouTubeService
    $this->youTubeService
        ->shouldReceive('setAccessToken')
        ->once()
        ->with($accessToken);

    $this->youTubeService
        ->shouldReceive('getPlaylists')
        ->once()
        ->andReturn($expectedPlaylists);

    $playlists = $this->action->handle($accessToken);

    expect($playlists)->toBe($expectedPlaylists);
});
