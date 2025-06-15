<div>
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 dark:bg-green-800 dark:border-green-600 dark:text-green-100 px-4 py-3 rounded">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 dark:bg-red-800 dark:border-red-600 dark:text-red-100 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header and Controls -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Your YouTube Subscriptions') }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ trans_choice('{0} No subscriptions|{1} :count subscription|[2,*] :count subscriptions', $subscriptions->total(), ['count' => $subscriptions->total()]) }}
                </p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('categories.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('Manage Categories') }}
                </a>
                <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    {{ __('View Playlists') }}
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Search channels') }}
                    </label>
                    <input type="text" 
                           id="search" 
                           wire:model.live.debounce.300ms="search" 
                           class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                           placeholder="{{ __('Search by channel name...') }}">
                </div>

                <!-- Category Filter -->
                <div>
                    <label for="categoryFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Filter by category') }}
                    </label>
                    <select wire:model.live="categoryFilter" 
                            id="categoryFilter"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort By -->
                <div>
                    <label for="sortBy" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        {{ __('Sort by') }}
                    </label>
                    <select wire:model.live="sortBy" 
                            id="sortBy"
                            class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="channel_title">{{ __('Channel name') }}</option>
                        <option value="subscribed_at">{{ __('Subscribe date') }}</option>
                        <option value="cached_at">{{ __('Last updated') }}</option>
                    </select>
                </div>

                <!-- Show Uncategorized -->
                <div class="flex items-center pt-6">
                    <input type="checkbox" 
                           id="showUncategorizedOnly" 
                           wire:model.live="showUncategorizedOnly" 
                           class="rounded border-gray-300 dark:border-gray-600 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <label for="showUncategorizedOnly" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        {{ __('Uncategorized only') }}
                    </label>
                </div>
            </div>

            @if($search || $categoryFilter || $showUncategorizedOnly)
                <div class="flex justify-end">
                    <button wire:click="clearFilters" 
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                        {{ __('Clear filters') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Subscriptions Grid -->
    @if($subscriptions->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 mb-6">
            @foreach($subscriptions as $subscription)
                <div class="bg-white dark:bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300 relative">
                    <!-- Thumbnail -->
                    @if($subscription->thumbnail_url)
                        <img src="{{ $subscription->thumbnail_url }}" 
                             alt="{{ $subscription->channel_title }}" 
                             class="w-full h-24 object-cover">
                    @else
                        <div class="w-full h-24 bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ __('No thumbnail') }}</span>
                        </div>
                    @endif

                    <!-- Content -->
                    <div class="p-3">
                        <h4 class="font-semibold text-sm mb-2 truncate text-gray-900 dark:text-gray-100">
                            {{ $subscription->channel_title }}
                        </h4>

                        <!-- Categories -->
                        @if($subscription->categories->count() > 0)
                            <div class="mb-3 flex flex-wrap gap-1">
                                @foreach($subscription->categories as $category)
                                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full text-white" 
                                          style="background-color: {{ $category->color }}">
                                        {{ $category->name }}
                                        <button wire:click="removeFromCategory('{{ $subscription->channel_id }}', {{ $category->id }})" 
                                                class="ml-1 hover:bg-black hover:bg-opacity-20 rounded-full p-0.5"
                                                title="{{ __('Remove from category') }}">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Category Assignment -->
                        @if($categories->count() > 0)
                            <div class="mb-3">
                                <select wire:change="assignToCategory('{{ $subscription->channel_id }}', $event.target.value)" 
                                        class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">{{ __('Add to category...') }}</option>
                                    @foreach($categories as $category)
                                        @if(!$subscription->hasCategory($category->id))
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex justify-between items-center">
                            <span class="text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 px-1.5 py-0.5 rounded-full">
                                {{ __('Channel') }}
                            </span>
                            <a href="https://www.youtube.com/channel/{{ $subscription->channel_id }}" 
                               target="_blank" 
                               class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                {{ __('Visit channel') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $subscriptions->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v1a1 1 0 01-1 1v9a2 2 0 01-2 2H4a2 2 0 01-2-2V7a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                @if($search || $categoryFilter || $showUncategorizedOnly)
                    {{ __('No subscriptions match your filters') }}
                @else
                    {{ __('No subscriptions found') }}
                @endif
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($search || $categoryFilter || $showUncategorizedOnly)
                    {{ __('Try adjusting your search criteria or clearing the filters.') }}
                @else
                    {{ __('Connect your YouTube account to see your subscriptions.') }}
                @endif
            </p>
            @if($search || $categoryFilter || $showUncategorizedOnly)
                <button wire:click="clearFilters" 
                        class="mt-3 inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    {{ __('Clear filters') }}
                </button>
            @endif
        </div>
    @endif
</div>