<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use App\Models\UserPlaylist;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PlaylistCard extends Component
{
    public UserPlaylist $playlist;
    public bool $showDetails = false;

    public function mount(UserPlaylist $playlist): void
    {
        $this->playlist = $playlist->load('categories');
    }

    public function render(): View
    {
        $availableCategories = Category::forUser(Auth::id())
            ->whereNotIn('id', $this->playlist->categories->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('livewire.playlist-card', [
            'availableCategories' => $availableCategories,
        ]);
    }

    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
    }

    public function editPlaylist(): void
    {
        $this->dispatch('edit-playlist', playlistId: $this->playlist->id);
    }

    public function deletePlaylist(): void
    {
        $this->dispatch('delete-playlist', playlistId: $this->playlist->id);
    }

    public function toggleManaged(): void
    {
        $this->playlist->update(['is_managed' => !$this->playlist->is_managed]);
        $this->playlist->refresh();

        $this->dispatch('playlist-card-updated', playlistId: $this->playlist->id);

        $status = $this->playlist->is_managed ? 'managed' : 'unmanaged';
        session()->flash('message', "Playlist marked as $status successfully.");
    }

    public function linkToCategory(int $categoryId): void
    {
        $this->playlist->linkToCategory($categoryId, Auth::id());
        $this->playlist->refresh();

        $this->dispatch('playlist-card-updated', playlistId: $this->playlist->id);
        session()->flash('message', 'Playlist linked to category successfully.');
    }

    public function unlinkFromCategory(int $categoryId): void
    {
        $this->playlist->unlinkFromCategory($categoryId);
        $this->playlist->refresh();

        $this->dispatch('playlist-card-updated', playlistId: $this->playlist->id);
        session()->flash('message', 'Playlist unlinked from category successfully.');
    }

    public function getPrivacyBadgeColorProperty(): string
    {
        return match ($this->playlist->privacy_status) {
            'public' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'private' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'unlisted' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }

    public function getSyncStatusColorProperty(): string
    {
        if ($this->playlist->needsSync()) {
            return 'text-yellow-600 dark:text-yellow-400';
        }
        return 'text-green-600 dark:text-green-400';
    }
}
