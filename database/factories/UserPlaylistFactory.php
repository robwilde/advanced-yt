<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPlaylist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPlaylist>
 */
class UserPlaylistFactory extends Factory
{
    protected $model = UserPlaylist::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'youtube_playlist_id' => 'PL' . fake()->regexify('[A-Za-z0-9]{32}'),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'thumbnail_url' => fake()->imageUrl(320, 180, 'playlists'),
            'privacy_status' => fake()->randomElement(['private', 'public', 'unlisted']),
            'item_count' => fake()->numberBetween(0, 100),
            'is_managed' => true,
            'last_synced_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function managed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_managed' => true,
        ]);
    }

    public function unmanaged(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_managed' => false,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'privacy_status' => 'private',
        ]);
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'privacy_status' => 'public',
        ]);
    }

    public function unlisted(): static
    {
        return $this->state(fn (array $attributes) => [
            'privacy_status' => 'unlisted',
        ]);
    }

    public function needsSync(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_synced_at' => fake()->dateTimeBetween('-1 month', '-25 hours'),
        ]);
    }

    public function recentlySync(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_synced_at' => fake()->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_count' => 0,
        ]);
    }
}