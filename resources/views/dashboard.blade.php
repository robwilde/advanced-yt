<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                            Welcome to Advanced YouTube Manager
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Organize your YouTube subscriptions and automate playlist management.
                        </p>
                    </div>

                    <!-- Quick Navigation Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Subscriptions Card -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Subscriptions
                                </h4>
                                <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                </svg>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                Manage your YouTube channel subscriptions and organize them into categories.
                            </p>
                            <a href="{{ route('subscriptions') }}" 
                               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors">
                                View Subscriptions
                                <svg class="ml-2 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Categories Card -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Categories
                                </h4>
                                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                                </svg>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                Create and manage categories to organize your channels and automate playlist creation.
                            </p>
                            <a href="{{ route('categories.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors">
                                Manage Categories
                                <svg class="ml-2 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Playlists Card -->
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Playlists
                                </h4>
                                <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                Create and manage YouTube playlists, and map them to categories for automation.
                            </p>
                            <a href="{{ route('playlists.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors">
                                Manage Playlists
                                <svg class="ml-2 -mr-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Getting Started Section -->
                    <div class="mt-12 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-6 border border-indigo-200 dark:border-indigo-800">
                        <h4 class="text-lg font-medium text-indigo-900 dark:text-indigo-100 mb-4">
                            Getting Started
                        </h4>
                        <div class="space-y-3 text-indigo-800 dark:text-indigo-200">
                            <div class="flex items-start">
                                <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-medium mr-3 mt-0.5">1</span>
                                <span>Connect your YouTube account and sync your subscriptions</span>
                            </div>
                            <div class="flex items-start">
                                <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-medium mr-3 mt-0.5">2</span>
                                <span>Create categories to organize your channels (e.g., Tech, Science, Gaming)</span>
                            </div>
                            <div class="flex items-start">
                                <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-medium mr-3 mt-0.5">3</span>
                                <span>Create or sync playlists and map them to categories</span>
                            </div>
                            <div class="flex items-start">
                                <span class="flex-shrink-0 w-6 h-6 bg-indigo-600 text-white rounded-full flex items-center justify-center text-sm font-medium mr-3 mt-0.5">4</span>
                                <span>Enable automation to automatically add new videos to appropriate playlists</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>