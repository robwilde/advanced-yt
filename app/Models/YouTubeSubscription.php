<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
}
