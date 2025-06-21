<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use App\Models\YouTubeSubscription;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionManager extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $categoryFilter = null;
    public string $sortBy = 'channel_title';
    public string $sortDirection = 'asc';
    public bool $showUncategorizedOnly = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => null],
        'sortBy' => ['except' => 'channel_title'],
        'sortDirection' => ['except' => 'asc'],
        'showUncategorizedOnly' => ['except' => false],
    ];

    public function mount(): void
    {
        //
    }

    public function render()
    {
        $categories = Category::forUser(Auth::id())->orderBy('name')->get();
        
        $subscriptions = $this->getSubscriptions();

        return view('livewire.subscription-manager', [
            'subscriptions' => $subscriptions,
            'categories' => $categories,
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedShowUncategorizedOnly(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    #[On('subscription-updated')]
    public function onSubscriptionUpdated(): void
    {
        // This will cause the parent component to re-render if needed
        // The individual cards handle their own state
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'categoryFilter', 'showUncategorizedOnly']);
        $this->resetPage();
    }

    private function getSubscriptions(): LengthAwarePaginator
    {
        $query = YouTubeSubscription::forUser(Auth::id())
            ->withCategories();

        // Apply search filter
        if ($this->search) {
            $query->where('channel_title', 'like', '%' . $this->search . '%');
        }

        // Apply category filter
        if ($this->categoryFilter) {
            $query->inCategory($this->categoryFilter);
        }

        // Apply uncategorized filter
        if ($this->showUncategorizedOnly) {
            $query->uncategorized(Auth::id());
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(20);
    }
}