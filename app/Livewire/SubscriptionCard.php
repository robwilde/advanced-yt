<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use App\Models\YouTubeSubscription;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SubscriptionCard extends Component
{
    public YouTubeSubscription $subscription;
    public bool $showDescription = false;
    
    public function mount(YouTubeSubscription $subscription): void
    {
        $this->subscription = $subscription->load('categories');
    }

    public function render()
    {
        $availableCategories = Category::forUser(Auth::id())
            ->whereNotIn('id', $this->subscription->categories->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('livewire.subscription-card', [
            'availableCategories' => $availableCategories,
        ]);
    }

    public function toggleDescription(): void
    {
        $this->showDescription = !$this->showDescription;
    }

    public function assignToCategory(int $categoryId): void
    {
        $this->subscription->addToCategory($categoryId, Auth::id());
        $this->subscription->refresh();
        
        $this->dispatch('subscription-updated', subscriptionId: $this->subscription->id);
        session()->flash('message', 'Channel assigned to category successfully.');
    }

    public function removeFromCategory(int $categoryId): void
    {
        $this->subscription->removeFromCategory($categoryId);
        $this->subscription->refresh();
        
        $this->dispatch('subscription-updated', subscriptionId: $this->subscription->id);
        session()->flash('message', 'Channel removed from category successfully.');
    }
}