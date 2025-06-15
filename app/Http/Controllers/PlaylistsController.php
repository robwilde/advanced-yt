<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FetchYouTubePlaylists;
use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Illuminate\View\View;
use Throwable;

final class PlaylistsController extends Controller
{
    public function __construct(
        private readonly FetchYouTubePlaylists $fetchYouTubePlaylists,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // NOTE: This is a placeholder for how the access token is retrieved
        // In a real implementation, this would need to be adjusted based on how the token is actually stored
        // It could be stored in the user model, a separate table, session, etc.
        // For now, we're assuming it's in the session
        $accessToken = session('youtube_access_token');

        $playlists = [];

        if ($accessToken) {
            try {
                $playlists = $this->fetchYouTubePlaylists->handle($accessToken);
            } catch (Throwable $e) {
                // Handle error - token might be expired or invalid
                // For now, just log it and continue with empty playlists
                Logger::error('Failed to fetch YouTube playlists: '.$e->getMessage());
            }
        }

        return view('playlists', [
            'playlists' => $playlists,
        ]);
    }
}
