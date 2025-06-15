<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Playlists') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(empty($playlists))
                        <div class="text-center py-8">
                            <h3 class="text-lg font-medium mb-4">{{ __("No YouTube playlists found") }}</h3>
                            <p class="mb-4">{{ __("You haven't connected your YouTube account or you don't have any playlists.") }}</p>
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
                            <h3 class="text-lg font-medium">{{ __("Your YouTube Playlists") }}</h3>
                            <a href="{{ route('subscriptions') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                {{ __("View Subscriptions") }}
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            @foreach($playlists as $playlist)
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow duration-300">
                                    @if(isset($playlist['thumbnail']))
                                        <img src="{{ $playlist['thumbnail'] }}" alt="{{ $playlist['title'] }}" class="w-full h-24 object-cover">
                                    @else
                                        <div class="w-full h-24 bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                            <span class="text-gray-500 dark:text-gray-400 text-xs">{{ __("No thumbnail") }}</span>
                                        </div>
                                    @endif
                                    <div class="p-3">
                                        <h4 class="font-semibold text-sm mb-1 truncate">{{ $playlist['title'] }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mb-2 line-clamp-2">{{ $playlist['description'] }}</p>
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 px-1.5 py-0.5 rounded-full">
                                                {{ $playlist['itemCount'] }} {{ __("videos") }}
                                            </span>
                                            <a href="#" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ __("View details") }}
                                            </a>
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
</x-app-layout>
