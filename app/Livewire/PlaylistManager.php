<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\UserPlaylist;
use App\Services\Contracts\YouTubeServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

class PlaylistManager extends Component
{
    use WithPagination;

    // Form fields for playlist creation/editing
    #[Rule('required|string|max:255')]
    public string $title = '';

    #[Rule('nullable|string|max:1000')]
    public string $description = '';

    #[Rule('required|in:private,public,unlisted')]
    public string $privacyStatus = 'private';

    // State management
    public ?int $editingPlaylistId = null;
    public bool $showForm = false;
    public bool $isSyncing = false;

    // Filtering and search
    public string $search = '';
    public string $privacyFilter = '';
    public string $managedFilter = '';
    public bool $showManagedOnly = false;
    public string $sortBy = 'title';
    public string $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'privacyFilter' => ['except' => ''],
        'managedFilter' => ['except' => ''],
        'showManagedOnly' => ['except' => false],
        'sortBy' => ['except' => 'title'],
        'sortDirection' => ['except' => 'asc'],
    ];


    public function mount(): void
    {
        $this->resetForm();
    }

    public function render(): View
    {
        $playlists = $this->getPlaylists();
        $totalPlaylists = UserPlaylist::forUser(Auth::id())->count();
        $managedPlaylists = UserPlaylist::forUser(Auth::id())->managed()->count();

        return view('livewire.playlist-manager', [
            'playlists' => $playlists,
            'totalPlaylists' => $totalPlaylists,
            'managedPlaylists' => $managedPlaylists,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPrivacyFilter(): void
    {
        $this->resetPage();
    }

    public function updatedManagedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedShowManagedOnly(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function showCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editPlaylist(int $playlistId): void
    {
        $playlist = UserPlaylist::forUser(Auth::id())->findOrFail($playlistId);

        $this->editingPlaylistId = $playlistId;
        $this->title = $playlist->title;
        $this->description = $playlist->description ?? '';
        $this->privacyStatus = $playlist->privacy_status;
        $this->showForm = true;
    }

    public function savePlaylist(): void
    {
        $this->validate();

        try {
            if ($this->editingPlaylistId) {
                $this->updateExistingPlaylist();
            } else {
                $this->createNewPlaylist();
            }

            $this->resetForm();
            $this->dispatch('playlist-saved');
        } catch (Throwable $e) {
            $this->addError('form', 'An error occurred while saving the playlist. Please try again.');
        }
    }

    public function deletePlaylist(int $playlistId): void
    {
        try {
            $playlist = UserPlaylist::forUser(Auth::id())->findOrFail($playlistId);
            $playlistTitle = $playlist->title;

            $youtubeService = app(YouTubeServiceInterface::class);

            // Delete from YouTube first
            $success = $youtubeService->deletePlaylist($playlist->youtube_playlist_id);

            if ($success) {
                // Delete from database
                $playlist->delete();
                $this->dispatch('playlist-deleted', $playlistTitle);
            } else {
                $this->addError('delete', 'Failed to delete playlist from YouTube.');
            }
        } catch (Throwable $e) {
            if (str_contains($e->getMessage(), 'Insufficient permissions')) {
                $this->addError('delete', 'Insufficient permissions. Please reconnect your YouTube account to grant full access.');
            } else {
                $this->addError('delete', 'An error occurred while deleting the playlist.');
            }
        }
    }

    public function syncPlaylists(): void
    {
        $this->isSyncing = true;

        try {
            $youtubeService = app(YouTubeServiceInterface::class);
            $result = $youtubeService->syncUserPlaylists();
            $this->dispatch('playlists-synced', $result['total']);
        } catch (Throwable $e) {
            $this->addError('sync', 'Failed to sync playlists. Please try again.');
        } finally {
            $this->isSyncing = false;
        }
    }

    public function toggleManaged(int $playlistId): void
    {
        try {
            $playlist = UserPlaylist::forUser(Auth::id())->findOrFail($playlistId);
            $playlist->update(['is_managed' => !$playlist->is_managed]);
            $this->dispatch('playlist-updated', $playlist->title);
        } catch (Throwable $e) {
            $this->addError('toggle', 'Failed to update playlist status.');
        }
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'privacyFilter', 'managedFilter', 'showManagedOnly']);
        $this->resetPage();
    }

    #[On('playlist-card-updated')]
    public function onPlaylistCardUpdated(): void
    {
        // Refresh the component when a card is updated
    }

    private function createNewPlaylist(): void
    {
        $youtubeService = app(YouTubeServiceInterface::class);

        $result = $youtubeService->createPlaylist(
            $this->title,
            $this->description ?: null,
            $this->privacyStatus
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

        $this->dispatch('playlist-created', $this->title);
    }

    private function updateExistingPlaylist(): void
    {
        $playlist = UserPlaylist::forUser(Auth::id())->findOrFail($this->editingPlaylistId);

        $youtubeService = app(YouTubeServiceInterface::class);

        $result = $youtubeService->updatePlaylist(
            $playlist->youtube_playlist_id,
            $this->title,
            $this->description ?: null,
            $this->privacyStatus
        );

        // Update in database
        $playlist->update([
            'title' => $result['title'],
            'description' => $result['description'] ?? '',
            'privacy_status' => $result['privacyStatus'],
            'last_synced_at' => now(),
        ]);

        $this->dispatch('playlist-updated', $this->title);
    }

    private function getPlaylists(): LengthAwarePaginator
    {
        $query = UserPlaylist::forUser(Auth::id())
            ->with('categories');

        // Apply search filter
        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Apply privacy filter
        if ($this->privacyFilter) {
            match ($this->privacyFilter) {
                'private' => $query->private(),
                'public' => $query->public(),
                'unlisted' => $query->where('privacy_status', 'unlisted'),
                default => null,
            };
        }

        // Apply managed filter
        if ($this->managedFilter) {
            match ($this->managedFilter) {
                'managed' => $query->managed(),
                'unmanaged' => $query->where('is_managed', false),
                default => null,
            };
        }

        // Apply managed only filter
        if ($this->showManagedOnly) {
            $query->managed();
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(20);
    }

    private function resetForm(): void
    {
        $this->title = '';
        $this->description = '';
        $this->privacyStatus = 'private';
        $this->editingPlaylistId = null;
        $this->showForm = false;
        $this->resetErrorBag();
    }
}
