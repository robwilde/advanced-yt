<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\CategoryPlaylist;
use App\Models\User;
use App\Models\UserPlaylist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryPlaylist>
 */
class CategoryPlaylistFactory extends Factory
{
    protected $model = CategoryPlaylist::class;

    public function definition(): array
    {
        $user = User::factory()->create();
        
        return [
            'user_id' => $user->id,
            'category_id' => Category::factory()->create(['user_id' => $user->id])->id,
            'user_playlist_id' => UserPlaylist::factory()->create(['user_id' => $user->id])->id,
            'priority' => fake()->numberBetween(1, 10),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
            'user_id' => $category->user_id,
        ]);
    }

    public function forPlaylist(UserPlaylist $playlist): static
    {
        return $this->state(fn (array $attributes) => [
            'user_playlist_id' => $playlist->id,
            'user_id' => $playlist->user_id,
        ]);
    }

    public function priority(int $priority): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $priority,
        ]);
    }

    public function highPriority(): static
    {
        return $this->priority(1);
    }

    public function lowPriority(): static
    {
        return $this->priority(10);
    }
}