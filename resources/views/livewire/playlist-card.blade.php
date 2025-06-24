<div class="bg-white dark:bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300 relative">
    <!-- Thumbnail -->
    <div class="w-full h-24 relative bg-gray-200 dark:bg-gray-600">
        @if($playlist->thumbnail_url)
            <img src="{{ $playlist->thumbnail_url }}" 
                 alt="{{ $playlist->title }}" 
                 class="w-full h-24 object-cover"
                 loading="lazy"
                 decoding="async">
        @else
            <div class="w-full h-24 bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                <svg class="w-8 h-8 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                </svg>
            </div>
        @endif
        
        <!-- Privacy Badge -->
        <div class="absolute top-2 right-2">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $this->privacyBadgeColor }}">
                {{ ucfirst($playlist->privacy_status) }}
            </span>
        </div>
    </div>

    <!-- Content -->
    <div class="p-3">
        <!-- Title and Description Toggle -->
        <div class="flex items-start justify-between mb-2">
            <h4 class="font-semibold text-sm text-gray-900 dark:text-gray-100 flex-1 leading-tight">
                {{ $playlist->title }}
            </h4>
            @if($playlist->description)
                <button wire:click="toggleDetails" 
                        class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0"
                        title="{{ $showDetails ? __('Hide details') : __('Show details') }}">
                    <svg class="w-4 h-4 transform transition-transform {{ $showDetails ? 'rotate-180' : '' }}" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Expandable Details -->
        @if($showDetails)
            <div class="mb-3 p-2 bg-gray-50 dark:bg-gray-600 rounded text-xs text-gray-700 dark:text-gray-300 space-y-2">
                @if($playlist->description)
                    <div>
                        <strong>{{ __('Description:') }}</strong>
                        <div class="mt-1">{{ Str::limit($playlist->description, 150) }}</div>
                    </div>
                @endif
                <div class="flex items-center justify-between">
                    <span>
                        <strong>{{ __('Videos:') }}</strong> {{ $playlist->item_count }}
                    </span>
                    <span class="{{ $this->syncStatusColor }}">
                        @if($playlist->needsSync())
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('Needs sync') }}
                        @else
                            <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ __('Up to date') }}
                        @endif
                    </span>
                </div>
            </div>
        @endif

        <!-- Categories -->
        @if($playlist->categories->count() > 0)
            <div class="mb-3 flex flex-wrap gap-1">
                @foreach($playlist->categories as $category)
                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full text-white" 
                          style="background-color: {{ $category->color }}">
                        {{ $category->name }}
                        <button wire:click="unlinkFromCategory({{ $category->id }})" 
                                class="ml-1 hover:bg-black hover:bg-opacity-20 rounded-full p-0.5"
                                title="{{ __('Unlink from category') }}">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </span>
                @endforeach
            </div>
        @endif

        <!-- Category Assignment -->
        @if($availableCategories->count() > 0)
            <div class="mb-3">
                <select wire:change="linkToCategory($event.target.value)" 
                        class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('Link to category...') }}</option>
                    @foreach($availableCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Status and Actions Row -->
        <div class="flex items-center justify-between">
            <!-- Management Status -->
            <div class="flex items-center space-x-2">
                <button wire:click="toggleManaged" 
                        class="inline-flex items-center text-xs px-2 py-1 rounded-full font-medium transition-colors {{ $playlist->is_managed ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100' }}"
                        title="{{ $playlist->is_managed ? __('Click to unmanage') : __('Click to manage') }}">
                    @if($playlist->is_managed)
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('Managed') }}
                    @else
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('Unmanaged') }}
                    @endif
                </button>
                
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $playlist->item_count }} {{ __('videos') }}
                </span>
            </div>

            <!-- Actions Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                    </svg>
                </button>
                
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-10">
                    <div class="py-1">
                        <a href="https://www.youtube.com/playlist?list={{ $playlist->youtube_playlist_id }}" 
                           target="_blank"
                           @click="open = false"
                           class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z"/>
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-1a1 1 0 10-2 0v1H5V7h1a1 1 0 000-2H5z"/>
                            </svg>
                            {{ __('View on YouTube') }}
                        </a>
                        <button wire:click="editPlaylist" 
                                @click="open = false"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('Edit') }}
                        </button>
                        <button wire:click="deletePlaylist" 
                                @click="open = false"
                                wire:confirm="{{ __('Are you sure you want to delete this playlist? This action cannot be undone.') }}"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading States -->
    <div wire:loading class="absolute inset-0 bg-white bg-opacity-75 dark:bg-gray-700 dark:bg-opacity-75 flex items-center justify-center">
        <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
    </div>
</div>