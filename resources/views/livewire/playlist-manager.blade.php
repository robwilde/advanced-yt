<div>
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 dark:bg-green-800 dark:border-green-600 dark:text-green-100 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if ($errors->has('form') || $errors->has('delete') || $errors->has('sync') || $errors->has('toggle'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 dark:bg-red-800 dark:border-red-600 dark:text-red-100 px-4 py-3 rounded">
            @foreach (['form', 'delete', 'sync', 'toggle'] as $key)
                @if ($errors->has($key))
                    {{ $errors->first($key) }}
                @endif
            @endforeach
        </div>
    @endif

    <!-- Header with Statistics and Actions -->
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center mb-6 space-y-4 lg:space-y-0">
        <div>
            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Your Playlists') }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Manage your YouTube playlists and link them to categories for automation.') }}
            </p>
            <div class="flex space-x-4 mt-2 text-xs text-gray-500 dark:text-gray-400">
                <span>{{ $totalPlaylists }} total</span>
                <span>{{ $managedPlaylists }} managed</span>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            @if(!$showForm)
                <button wire:click="showCreateForm" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('Create Playlist') }}
                </button>
            @endif
            
            <button wire:click="syncPlaylists" 
                    wire:loading.attr="disabled"
                    wire:target="syncPlaylists"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" 
                     wire:loading.class="animate-spin" wire:target="syncPlaylists">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span wire:loading.remove wire:target="syncPlaylists">{{ __('Sync from YouTube') }}</span>
                <span wire:loading wire:target="syncPlaylists">{{ __('Syncing...') }}</span>
            </button>
        </div>
    </div>

    <!-- Playlist Form -->
    @if($showForm)
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
            <h5 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingPlaylistId ? __('Edit Playlist') : __('Create New Playlist') }}
            </h5>
            
            <form wire:submit="savePlaylist" class="space-y-4">
                <!-- Playlist Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Title') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="title" 
                           wire:model="title" 
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="{{ __('Enter playlist title') }}"
                           maxlength="255">
                    @error('title') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Playlist Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Description') }}
                    </label>
                    <textarea id="description" 
                              wire:model="description" 
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="{{ __('Optional description for this playlist') }}"
                              maxlength="1000"></textarea>
                    @error('description') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Privacy Status -->
                <div>
                    <label for="privacyStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Privacy') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="privacyStatus" 
                            wire:model="privacyStatus" 
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="private">{{ __('Private') }}</option>
                        <option value="public">{{ __('Public') }}</option>
                        <option value="unlisted">{{ __('Unlisted') }}</option>
                    </select>
                    @error('privacyStatus') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4">
                    <button type="button" 
                            wire:click="cancelEdit"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-400 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            wire:target="savePlaylist"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150">
                        <span wire:loading.remove wire:target="savePlaylist">
                            {{ $editingPlaylistId ? __('Update Playlist') : __('Create Playlist') }}
                        </span>
                        <span wire:loading wire:target="savePlaylist">{{ __('Saving...') }}</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Filters and Search -->
    <div class="bg-white dark:bg-gray-700 rounded-lg p-4 mb-6 border border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Search') }}
                </label>
                <input type="text" 
                       id="search"
                       wire:model.live.debounce.300ms="search" 
                       placeholder="{{ __('Search playlists...') }}"
                       class="block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Privacy Filter -->
            <div>
                <label for="privacyFilter" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Privacy') }}
                </label>
                <select id="privacyFilter" 
                        wire:model.live="privacyFilter"
                        class="block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('All') }}</option>
                    <option value="private">{{ __('Private') }}</option>
                    <option value="public">{{ __('Public') }}</option>
                    <option value="unlisted">{{ __('Unlisted') }}</option>
                </select>
            </div>

            <!-- Managed Filter -->
            <div>
                <label for="managedFilter" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('Management') }}
                </label>
                <select id="managedFilter" 
                        wire:model.live="managedFilter"
                        class="block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('All') }}</option>
                    <option value="managed">{{ __('Managed') }}</option>
                    <option value="unmanaged">{{ __('Unmanaged') }}</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="flex items-end">
                <button wire:click="clearFilters" 
                        class="w-full px-3 py-2 text-xs bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                    {{ __('Clear Filters') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Playlists Grid -->
    @if($playlists->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-6">
            @foreach($playlists as $playlist)
                <livewire:playlist-card 
                    :playlist="$playlist" 
                    :key="'playlist-' . $playlist->id"
                    @edit-playlist="editPlaylist($event.detail.playlistId)"
                    @delete-playlist="deletePlaylist($event.detail.playlistId)" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $playlists->links() }}
        </div>
    @else
        <div class="text-center py-12">
            @if($search || $privacyFilter || $managedFilter)
                <!-- No results for filters -->
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No playlists found') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Try adjusting your search or filters.') }}</p>
                <div class="mt-4">
                    <button wire:click="clearFilters" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('Clear filters') }}
                    </button>
                </div>
            @else
                <!-- No playlists at all -->
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No playlists') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating a playlist or syncing from YouTube.') }}</p>
                <div class="mt-6 flex flex-col sm:flex-row justify-center space-y-2 sm:space-y-0 sm:space-x-3">
                    @if(!$showForm)
                        <button wire:click="showCreateForm" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('Create your first playlist') }}
                        </button>
                    @endif
                    <button wire:click="syncPlaylists" 
                            wire:loading.attr="disabled"
                            wire:target="syncPlaylists"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                        <span wire:loading.remove wire:target="syncPlaylists">{{ __('Sync from YouTube') }}</span>
                        <span wire:loading wire:target="syncPlaylists">{{ __('Syncing...') }}</span>
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>

@script
<script>
    // Handle events dispatched from the component
    $wire.on('playlist-created', (title) => {
        // Could add toast notification here
        console.log('Playlist created:', title);
    });

    $wire.on('playlist-updated', (title) => {
        console.log('Playlist updated:', title);
    });

    $wire.on('playlist-deleted', (title) => {
        console.log('Playlist deleted:', title);
    });

    $wire.on('playlists-synced', (total) => {
        console.log('Playlists synced:', total);
    });
</script>
@endscript