<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'color',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(
            YouTubeSubscription::class,
            'channel_categories',
            'category_id',
            'channel_id',
            'id',
            'channel_id'
        )->withPivot('user_id')->withTimestamps();
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(
            UserPlaylist::class,
            'category_playlists',
            'category_id',
            'user_playlist_id'
        )->withPivot(['user_id', 'priority'])->withTimestamps();
    }

    public function playlistMappings(): HasMany
    {
        return $this->hasMany(CategoryPlaylist::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithChannelCounts(Builder $query): Builder
    {
        return $query->withCount('channels');
    }

    public function getChannelCountAttribute(): int
    {
        return $this->channels()->count();
    }

    public function hasChannel(string $channelId): bool
    {
        return $this->channels()->where('channel_id', $channelId)->exists();
    }

    public function addChannel(string $channelId, int $userId): void
    {
        $this->channels()->attach($channelId, [
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function removeChannel(string $channelId): void
    {
        $this->channels()->detach($channelId);
    }
}