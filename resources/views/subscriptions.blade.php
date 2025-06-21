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
                    @if(!$hasSubscriptions)
                        <div class="text-center py-8">
                            <h3 class="text-lg font-medium mb-4">{{ __("No YouTube subscriptions found") }}</h3>
                            @if($needsSync)
                                <p class="mb-4">{{ __("Syncing your subscriptions in the background. This may take a few minutes.") }}</p>
                                <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">{{ __("Refresh this page in a minute to see your subscriptions.") }}</p>
                            @else
                                <p class="mb-4">{{ __("You haven't connected your YouTube account or you don't have any subscriptions.") }}</p>
                            @endif
                            <a href="{{ route('auth.youtube') }}"
                               class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                                </svg>
                                {{ $needsSync ? __("Re-authenticate YouTube") : __("Connect YouTube Account") }}
                            </a>
                        </div>
                    @else
                        @livewire('subscription-manager')
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
