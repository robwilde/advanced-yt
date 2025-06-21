<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }

    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $color,
        ]);
    }

    public function tech(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Tech',
            'description' => 'Technology and programming content',
            'color' => '#3B82F6',
        ]);
    }

    public function science(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Science',
            'description' => 'Science and educational content',
            'color' => '#10B981',
        ]);
    }

    public function gaming(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Gaming',
            'description' => 'Gaming and entertainment content',
            'color' => '#8B5CF6',
        ]);
    }
}