<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\UserPlaylist;
use App\Services\Contracts\YouTubeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

final class PlaylistsController extends Controller
{
    public function __construct(
        private readonly YouTubeServiceInterface $youtubeService,
    ) {}

    public function index(Request $request): View
    {
        return view('playlists');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'privacy_status' => 'required|in:private,public,unlisted',
        ]);

        try {
            $result = $this->youtubeService->createPlaylist(
                $request->input('title'),
                $request->input('description'),
                $request->input('privacy_status')
            );

            // Store in database
            UserPlaylist::create([
                'user_id' => Auth::id(),
                'youtube_playlist_id' => $result['id'],
                'title' => $result['title'],
                'description' => $result['description'] ?? '',
                'thumbnail_url' => $result['thumbnail'],
                'privacy_status' => $result['privacyStatus'],
                'item_count' => $result['itemCount'],
                'is_managed' => true,
                'last_synced_at' => now(),
            ]);

            return redirect()->route('playlists.index')
                ->with('success', 'Playlist created successfully.');
        } catch (Throwable $e) {
            Log::error('Failed to create playlist', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create playlist. Please try again.');
        }
    }

    public function update(Request $request, UserPlaylist $playlist): RedirectResponse
    {
        $this->authorize('update', $playlist);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'privacy_status' => 'required|in:private,public,unlisted',
        ]);

        try {
            $result = $this->youtubeService->updatePlaylist(
                $playlist->youtube_playlist_id,
                $request->input('title'),
                $request->input('description'),
                $request->input('privacy_status')
            );

            // Update in database
            $playlist->update([
                'title' => $result['title'],
                'description' => $result['description'] ?? '',
                'privacy_status' => $result['privacyStatus'],
                'last_synced_at' => now(),
            ]);

            return redirect()->route('playlists.index')
                ->with('success', 'Playlist updated successfully.');
        } catch (Throwable $e) {
            Log::error('Failed to update playlist', [
                'error' => $e->getMessage(),
                'playlist_id' => $playlist->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update playlist. Please try again.');
        }
    }

    public function destroy(UserPlaylist $playlist): RedirectResponse
    {
        $this->authorize('delete', $playlist);

        try {
            // Delete from YouTube first
            $success = $this->youtubeService->deletePlaylist($playlist->youtube_playlist_id);
            
            if ($success) {
                // Delete from database
                $playlist->delete();
                
                return redirect()->route('playlists.index')
                    ->with('success', 'Playlist deleted successfully.');
            }
            
            return redirect()->back()
                ->with('error', 'Failed to delete playlist from YouTube.');
        } catch (Throwable $e) {
            Log::error('Failed to delete playlist', [
                'error' => $e->getMessage(),
                'playlist_id' => $playlist->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete playlist. Please try again.');
        }
    }

    public function sync(Request $request): JsonResponse
    {
        try {
            $result = $this->youtubeService->syncUserPlaylists();
            
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'total' => $result['total'],
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to sync playlists', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync playlists. Please try again.',
            ], 500);
        }
    }
}
