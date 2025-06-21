<div class="bg-white dark:bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300 relative">
    <!-- Thumbnail -->
    <div class="w-full h-24 relative bg-gray-200 dark:bg-gray-600">
        @if($subscription->thumbnail_url)
            <img src="{{ $subscription->thumbnail_url }}" 
                 alt="{{ $subscription->channel_title }}" 
                 class="w-full h-24 object-cover"
                 loading="lazy"
                 decoding="async">
        @else
            <div class="w-full h-24 bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                <span class="text-gray-500 dark:text-gray-400 text-xs">{{ __('No thumbnail') }}</span>
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-3">
        <!-- Channel Title and Description Toggle -->
        <div class="flex items-start justify-between mb-2">
            <h4 class="font-semibold text-sm truncate text-gray-900 dark:text-gray-100 flex-1">
                {{ $subscription->channel_title }}
            </h4>
            @if($subscription->channel_description)
                <button wire:click="toggleDescription" 
                        class="ml-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 flex-shrink-0"
                        title="{{ $showDescription ? __('Hide description') : __('Show description') }}">
                    <svg class="w-4 h-4 transform transition-transform {{ $showDescription ? 'rotate-180' : '' }}" 
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            @endif
        </div>

        <!-- Expandable Description -->
        @if($showDescription && $subscription->channel_description)
            <div class="mb-3 p-2 bg-gray-50 dark:bg-gray-600 rounded text-xs text-gray-700 dark:text-gray-300">
                {{ Str::limit($subscription->channel_description, 200) }}
            </div>
        @endif

        <!-- Categories -->
        @if($subscription->categories->count() > 0)
            <div class="mb-3 flex flex-wrap gap-1">
                @foreach($subscription->categories as $category)
                    <span class="inline-flex items-center text-xs px-2 py-1 rounded-full text-white" 
                          style="background-color: {{ $category->color }}">
                        {{ $category->name }}
                        <button wire:click="removeFromCategory({{ $category->id }})" 
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
        @if($availableCategories->count() > 0)
            <div class="mb-3">
                <select wire:change="assignToCategory($event.target.value)" 
                        class="w-full text-xs rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    <option value="">{{ __('Add to category...') }}</option>
                    @foreach($availableCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
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

    <!-- Loading States -->
    <div wire:loading class="absolute inset-0 bg-white bg-opacity-75 dark:bg-gray-700 dark:bg-opacity-75 flex items-center justify-center">
        <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
    </div>
</div>