<?php /** @noinspection PhpUnhandledExceptionInspection */

use App\Actions\FetchYouTubePlaylistItems;
use App\Services\Contracts\YouTubeServiceInterface;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->youTubeService = Mockery::mock(YouTubeServiceInterface::class);
    $this->action = new FetchYouTubePlaylistItems($this->youTubeService);
});

test('handle method sets access token and returns playlist items', function () {
    $accessToken = ['access_token' => 'test_token', 'expires_in' => 3600];
    $playlistId = 'playlist123';
    $expectedItems = [
        [
            'id' => 'item123',
            'title' => 'Test Video',
            'description' => 'Test Video Description',
            'thumbnail' => 'https://example.com/video-thumbnail.jpg',
            'videoId' => 'video123',
            'publishedAt' => '2023-01-01T00:00:00Z',
        ],
    ];

    // Mock DB transaction to execute the callback
    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) {
            return $callback();
        });

    // Set expectations on YouTubeService
    $this->youTubeService->shouldReceive('setAccessToken')
        ->once()
        ->with($accessToken);

    $this->youTubeService->shouldReceive('getPlaylistItems')
        ->once()
        ->with($playlistId)
        ->andReturn($expectedItems);

    $items = $this->action->handle($accessToken, $playlistId);

    expect($items)->toBe($expectedItems);
});
