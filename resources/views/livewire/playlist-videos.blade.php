<div>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                @if($playlist)
                                    {{ $playlist->title }}
                                @else
                                    Playlist Videos
                                @endif
                            </h2>
                            @if($playlist && $playlist->description)
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $playlist->description }}
                                </p>
                            @endif
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ count($videos) }} videos
                            </p>
                        </div>

                        <div class="flex items-center space-x-3">
                            <!-- Bulk Actions -->
                            @if(count($videos) > 0)
                                <div class="flex items-center space-x-2">
                                    <button wire:click="deleteSelectedVideos" 
                                            @disabled(empty($selectedVideos) || $bulkDeleting || $clearingPlaylist)
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 hover:text-red-500 disabled:text-gray-400 disabled:cursor-not-allowed">
                                        @if($bulkDeleting)
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Deleting...
                                        @else
                                            Delete Selected ({{ count($selectedVideos) }})
                                        @endif
                                    </button>
                                    
                                    <button wire:click="clearPlaylist"
                                            wire:confirm="Are you sure you want to remove all videos from this playlist? This action cannot be undone."
                                            @disabled($bulkDeleting || $clearingPlaylist)
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 hover:text-red-500 border border-red-300 rounded-md hover:border-red-400 disabled:text-gray-400 disabled:cursor-not-allowed disabled:border-gray-300">
                                        @if($clearingPlaylist)
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Clearing...
                                        @else
                                            Clear Playlist
                                        @endif
                                    </button>
                                </div>
                            @endif

                            <!-- Back Button -->
                            <a href="{{ route('playlists.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to Playlists
                            </a>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    @if($errors->any())
                        <div class="mt-4 space-y-2">
                            @foreach($errors->all() as $error)
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-md text-sm">
                                    {{ $error }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Videos List -->
                <div class="p-6">
                    @if($loading)
                        <div class="flex items-center justify-center py-12">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                            <span class="ml-3 text-gray-600 dark:text-gray-400">Loading videos...</span>
                        </div>
                    @elseif(empty($videos))
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No videos</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">This playlist is empty.</p>
                        </div>
                    @else
                        <!-- Select All Checkbox -->
                        <div class="mb-4 flex items-center">
                            <input type="checkbox" 
                                   wire:model.live="selectAll" 
                                   wire:click="toggleSelectAll"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                                Select all videos
                            </label>
                        </div>

                        <!-- Videos Grid -->
                        <div class="space-y-4">
                            @foreach($videos as $video)
                                <div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <!-- Checkbox -->
                                    <div class="flex-shrink-0 pt-1">
                                        <input type="checkbox" 
                                               wire:model.live="selectedVideos" 
                                               value="{{ $video['id'] }}"
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded">
                                    </div>

                                    <!-- Thumbnail -->
                                    <div class="flex-shrink-0">
                                        @if($video['thumbnail'])
                                            <img src="{{ $video['thumbnail'] }}" 
                                                 alt="{{ $video['title'] }}"
                                                 class="w-32 h-20 object-cover rounded-md">
                                        @else
                                            <div class="w-32 h-20 bg-gray-300 dark:bg-gray-600 rounded-md flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Video Details -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                    {{ $video['title'] }}
                                                </h3>
                                                @if($video['description'])
                                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                                        {{ Str::limit($video['description'], 120) }}
                                                    </p>
                                                @endif
                                                <div class="mt-2 flex items-center text-xs text-gray-400 dark:text-gray-500">
                                                    <span>Published: {{ \Carbon\Carbon::parse($video['publishedAt'])->format('M j, Y') }}</span>
                                                    @if($video['videoId'])
                                                        <span class="mx-2">•</span>
                                                        <a href="https://www.youtube.com/watch?v={{ $video['videoId'] }}" 
                                                           target="_blank" 
                                                           class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                                                            Watch on YouTube
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex-shrink-0 ml-4">
                                                <button wire:click="deleteVideo('{{ $video['id'] }}')" 
                                                        wire:confirm="Are you sure you want to remove this video from the playlist?"
                                                        class="text-red-600 hover:text-red-500 p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>