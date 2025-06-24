<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\UserPlaylist;
use App\Services\Contracts\YouTubeServiceInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

final class PlaylistVideos extends Component
{
    #[Locked]
    public string $playlistId;

    public array $videos = [];
    public array $selectedVideos = [];
    public bool $selectAll = false;
    public bool $loading = true;
    public bool $bulkDeleting = false;
    public bool $clearingPlaylist = false;
    public ?UserPlaylist $playlist = null;

    public function mount(string $playlistId): void
    {
        $this->playlistId = $playlistId;
        $this->loadPlaylist();
        $this->loadVideos();
    }

    public function loadPlaylist(): void
    {
        $userId = Auth::id();
        if (!$userId) {
            return;
        }

        $this->playlist = UserPlaylist::where('user_id', $userId)
            ->where('youtube_playlist_id', $this->playlistId)
            ->first();
    }

    public function loadVideos(): void
    {
        try {
            $this->loading = true;
            $youtubeService = app(YouTubeServiceInterface::class);
            $this->videos = $youtubeService->getPlaylistItems($this->playlistId);
        } catch (Throwable $e) {
            $this->addError('load', 'Failed to load playlist videos: ' . $e->getMessage());
            $this->videos = [];
        } finally {
            $this->loading = false;
        }
    }

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedVideos = array_column($this->videos, 'id');
        } else {
            $this->selectedVideos = [];
        }
    }

    public function updatedSelectedVideos(): void
    {
        $this->selectAll = count($this->selectedVideos) === count($this->videos);
    }

    public function deleteVideo(string $playlistItemId): void
    {
        try {
            $youtubeService = app(YouTubeServiceInterface::class);
            $success = $youtubeService->removeVideoFromPlaylist($playlistItemId);

            if ($success) {
                $this->videos = array_filter($this->videos, static fn($video) => $video['id'] !== $playlistItemId);
                $this->selectedVideos = array_filter($this->selectedVideos, static fn($id) => $id !== $playlistItemId);
                $this->dispatch('video-deleted');
            } else {
                $this->addError('delete', 'Failed to delete video from playlist.');
            }
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Insufficient permissions')) {
                $this->addError('delete', 'Insufficient permissions. Please reconnect your YouTube account to grant full access.');
            } else {
                $this->addError('delete', 'An error occurred while deleting the video.');
            }
        }
    }

    public function deleteSelectedVideos(): void
    {
        if (empty($this->selectedVideos)) {
            $this->addError('bulk-delete', 'No videos selected for deletion.');
            return;
        }

        $this->bulkDeleting = true;
        $deleted = 0;
        $failed = 0;

        try {
            $youtubeService = app(YouTubeServiceInterface::class);

            foreach ($this->selectedVideos as $playlistItemId) {
                try {
                    $success = $youtubeService->removeVideoFromPlaylist($playlistItemId);
                    if ($success) {
                        $deleted++;
                    } else {
                        $failed++;
                    }
                } catch (Throwable $e) {
                    $failed++;
                }
            }

            // Remove deleted videos from the array
            $this->videos = array_filter($this->videos, fn($video) => ! in_array($video['id'], $this->selectedVideos, true));
            $this->selectedVideos = [];
            $this->selectAll = false;

            if ($deleted > 0) {
                $this->dispatch('videos-deleted', count: $deleted);
            }

            if ($failed > 0) {
                $this->addError('bulk-delete', "Successfully deleted $deleted videos, but $failed failed to delete.");
            }

        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Insufficient permissions')) {
                $this->addError('bulk-delete', 'Insufficient permissions. Please reconnect your YouTube account to grant full access.');
            } else {
                $this->addError('bulk-delete', 'An error occurred during bulk deletion.');
            }
        } finally {
            $this->bulkDeleting = false;
        }
    }

    public function clearPlaylist(): void
    {
        if (empty($this->videos)) {
            $this->addError('clear', 'Playlist is already empty.');
            return;
        }

        $this->clearingPlaylist = true;
        $allVideoIds = array_column($this->videos, 'id');
        $deleted = 0;
        $failed = 0;

        try {
            $youtubeService = app(YouTubeServiceInterface::class);

            foreach ($allVideoIds as $playlistItemId) {
                try {
                    $success = $youtubeService->removeVideoFromPlaylist($playlistItemId);
                    if ($success) {
                        $deleted++;
                    } else {
                        $failed++;
                    }
                } catch (Throwable $e) {
                    $failed++;
                }
            }

            // Clear all videos from the array
            $this->videos = [];
            $this->selectedVideos = [];
            $this->selectAll = false;

            if ($deleted > 0) {
                $this->dispatch('playlist-cleared', count: $deleted);
            }

            if ($failed > 0) {
                $this->addError('clear', "Successfully removed $deleted videos, but $failed failed to remove.");
            }

        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Insufficient permissions')) {
                $this->addError('clear', 'Insufficient permissions. Please reconnect your YouTube account to grant full access.');
            } else {
                $this->addError('clear', 'An error occurred while clearing the playlist.');
            }
        } finally {
            $this->clearingPlaylist = false;
        }
    }

    public function render(): View
    {
        return view('livewire.playlist-videos')->layout('layouts.app');
    }
}
