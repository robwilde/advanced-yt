<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Rule;

class CategoryManager extends Component
{
    #[Rule('required|string|max:255')]
    public string $name = '';
    
    #[Rule('nullable|string|max:1000')]
    public string $description = '';
    
    #[Rule('required|string|regex:/^#[0-9A-Fa-f]{6}$/')]
    public string $color = '#3B82F6';

    public ?int $editingCategoryId = null;
    public bool $showForm = false;

    public function mount(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        $categories = Category::forUser(Auth::id())
            ->withChannelCounts()
            ->orderBy('name')
            ->get();

        return view('livewire.category-manager', [
            'categories' => $categories,
        ]);
    }

    public function showCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editCategory(int $categoryId): void
    {
        $category = Category::forUser(Auth::id())->findOrFail($categoryId);
        
        $this->editingCategoryId = $categoryId;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->color = $category->color;
        $this->showForm = true;
    }

    public function saveCategory(): void
    {
        $this->validate();

        $data = [
            'user_id' => Auth::id(),
            'name' => trim($this->name),
            'description' => trim($this->description) ?: null,
            'color' => $this->color,
        ];

        if ($this->editingCategoryId) {
            $category = Category::forUser(Auth::id())->findOrFail($this->editingCategoryId);
            $category->update($data);
            $this->dispatch('category-updated', $category->name);
        } else {
            Category::create($data);
            $this->dispatch('category-created', $this->name);
        }

        $this->resetForm();
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = Category::forUser(Auth::id())->findOrFail($categoryId);
        $categoryName = $category->name;
        
        // Remove all channel associations
        $category->channels()->detach();
        
        // Delete the category
        $category->delete();

        $this->dispatch('category-deleted', $categoryName);
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->description = '';
        $this->color = '#3B82F6';
        $this->editingCategoryId = null;
        $this->showForm = false;
        $this->resetErrorBag();
    }
}