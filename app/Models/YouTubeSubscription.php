<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class YouTubeSubscription extends Model
{
    protected $table = 'youtube_subscriptions';
    protected $fillable = [
        'user_id',
        'subscription_id',
        'channel_id',
        'channel_title',
        'channel_description',
        'thumbnail_url',
        'subscribed_at',
        'cached_at',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'cached_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecentlySubscribed(Builder $query, int $days = 30): Builder
    {
        return $query->where('subscribed_at', '>=', now()->subDays($days));
    }

    public function scopeOrderedBySubscriptionDate(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('subscribed_at', $direction);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'channel_categories',
            'channel_id',
            'category_id',
            'channel_id',
            'id'
        )->withPivot('user_id')->withTimestamps();
    }

    public function scopeWithCategories(Builder $query): Builder
    {
        return $query->with('categories');
    }

    public function scopeCategorized(Builder $query, int $userId): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeUncategorized(Builder $query, int $userId): Builder
    {
        return $query->whereDoesntHave('categories', function (Builder $q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    public function hasCategory(int $categoryId): bool
    {
        return $this->categories()->where('categories.id', $categoryId)->exists();
    }

    public function addToCategory(int $categoryId, int $userId): void
    {
        if (!$this->hasCategory($categoryId)) {
            $this->categories()->attach($categoryId, [
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function removeFromCategory(int $categoryId): void
    {
        $this->categories()->detach($categoryId);
    }

    public function getCategoryNamesAttribute(): string
    {
        return $this->categories->pluck('name')->join(', ');
    }
}
