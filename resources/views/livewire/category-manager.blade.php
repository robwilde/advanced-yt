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

    <!-- Header with Create Button -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('Your Categories') }}</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ __('Organize your YouTube subscriptions into custom categories.') }}
            </p>
        </div>
        @if(!$showForm)
            <button wire:click="showCreateForm" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                {{ __('Create Category') }}
            </button>
        @endif
    </div>

    <!-- Category Form -->
    @if($showForm)
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-6">
            <h5 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $editingCategoryId ? __('Edit Category') : __('Create New Category') }}
            </h5>
            
            <form wire:submit="saveCategory" class="space-y-4">
                <!-- Category Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           wire:model="name" 
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="{{ __('e.g., Tech, Science, Gaming') }}"
                           maxlength="255">
                    @error('name') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Category Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Description') }}
                    </label>
                    <textarea id="description" 
                              wire:model="description" 
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="{{ __('Optional description for this category') }}"
                              maxlength="1000"></textarea>
                    @error('description') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                    @enderror
                </div>

                <!-- Category Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('Color') }}
                    </label>
                    <div class="mt-1 flex items-center space-x-3">
                        <input type="color" 
                               id="color" 
                               wire:model="color" 
                               class="h-10 w-16 rounded border border-gray-300 dark:border-gray-600">
                        <input type="text" 
                               wire:model="color" 
                               class="block w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                               pattern="^#[0-9A-Fa-f]{6}$"
                               placeholder="#3B82F6">
                    </div>
                    @error('color') 
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
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ $editingCategoryId ? __('Update Category') : __('Create Category') }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Categories List -->
    @if($categories->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories as $category)
                <div class="bg-white dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 p-4 hover:shadow-md transition-shadow duration-200">
                    <!-- Category Header -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-4 h-4 rounded-full" style="background-color: {{ $category->color }}"></div>
                            <h6 class="font-medium text-gray-900 dark:text-gray-100">{{ $category->name }}</h6>
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
                                    <button wire:click="editCategory({{ $category->id }})" 
                                            @click="open = false"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        {{ __('Edit') }}
                                    </button>
                                    <button wire:click="deleteCategory({{ $category->id }})" 
                                            @click="open = false"
                                            wire:confirm="{{ __('Are you sure you want to delete this category? All channel assignments will be removed.') }}"
                                            class="block w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        {{ __('Delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Description -->
                    @if($category->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ $category->description }}</p>
                    @endif

                    <!-- Channel Count -->
                    <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v1a1 1 0 01-1 1v9a2 2 0 01-2 2H4a2 2 0 01-2-2V7a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
                        </svg>
                        {{ trans_choice('{0} No channels|{1} :count channel|[2,*] :count channels', $category->channels_count, ['count' => $category->channels_count]) }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('No categories') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Get started by creating your first category.') }}</p>
        </div>
    @endif
</div>