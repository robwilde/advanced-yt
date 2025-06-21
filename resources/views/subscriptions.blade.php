<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscriptions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(empty($subscriptions))
                        <div class="text-center py-8">
                            <h3 class="text-lg font-medium mb-4">{{ __("No YouTube subscriptions found") }}</h3>
                            <p class="mb-4">{{ __("You haven't connected your YouTube account or you don't have any subscriptions.") }}</p>
                            <a href="{{ route('auth.youtube') }}"
                               class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                                </svg>
                                {{ __("Connect YouTube Account") }}
                            </a>
                        </div>
                    @else
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">{{ __("Your YouTube Subscriptions") }}</h3>
                            <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                {{ __("View Playlists") }}
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($subscriptions as $subscription)
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                                    @if(isset($subscription['thumbnail']))
                                        <img src="{{ $subscription['thumbnail'] }}" alt="{{ $subscription['title'] }}" class="w-full h-24 object-cover">
                                    @else
                                        <div class="w-full h-24 bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ __("No thumbnail") }}</span>
                                        </div>
                                    @endif
                                    <div class="p-3">
                                        <h4 class="font-semibold text-sm mb-1 truncate">{{ $subscription['title'] }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mb-2 line-clamp-2">{{ $subscription['description'] }}</p>
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 px-1.5 py-0.5 rounded-full">
                                                {{ __("Channel") }}
                                            </span>
                                            <a href="https://www.youtube.com/channel/{{ $subscription['channelId'] }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ __("Visit channel") }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if($pagination['total'] > $pagination['perPage'])
                            <div class="mt-8 flex justify-center">
                                <nav class="flex items-center space-x-2">
                                    @if($pagination['currentPage'] > 1)
                                        <a href="{{ route('subscriptions', ['page' => $pagination['currentPage'] - 1]) }}" 
                                           class="px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                            {{ __("Previous") }}
                                        </a>
                                    @endif

                                    @php
                                        $totalPages = ceil($pagination['total'] / $pagination['perPage']);
                                        $startPage = max(1, $pagination['currentPage'] - 2);
                                        $endPage = min($totalPages, $pagination['currentPage'] + 2);
                                    @endphp

                                    @if($startPage > 1)
                                        <a href="{{ route('subscriptions', ['page' => 1]) }}" 
                                           class="px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                            1
                                        </a>
                                        @if($startPage > 2)
                                            <span class="px-3 py-2 text-sm text-gray-500">...</span>
                                        @endif
                                    @endif

                                    @for($page = $startPage; $page <= $endPage; $page++)
                                        @if($page == $pagination['currentPage'])
                                            <span class="px-3 py-2 text-sm bg-blue-600 text-white rounded-md">
                                                {{ $page }}
                                            </span>
                                        @else
                                            <a href="{{ route('subscriptions', ['page' => $page]) }}" 
                                               class="px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                                {{ $page }}
                                            </a>
                                        @endif
                                    @endfor

                                    @if($endPage < $totalPages)
                                        @if($endPage < $totalPages - 1)
                                            <span class="px-3 py-2 text-sm text-gray-500">...</span>
                                        @endif
                                        <a href="{{ route('subscriptions', ['page' => $totalPages]) }}" 
                                           class="px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                            {{ $totalPages }}
                                        </a>
                                    @endif

                                    @if($pagination['hasMore'])
                                        <a href="{{ route('subscriptions', ['page' => $pagination['currentPage'] + 1]) }}" 
                                           class="px-3 py-2 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600">
                                            {{ __("Next") }}
                                        </a>
                                    @endif
                                </nav>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
