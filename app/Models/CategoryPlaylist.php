<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

final class CategoryPlaylist extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'user_playlist_id',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(UserPlaylist::class, 'user_playlist_id');
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeForPlaylist(Builder $query, int $playlistId): Builder
    {
        return $query->where('user_playlist_id', $playlistId);
    }

    public function scopeOrderedByPriority(Builder $query): Builder
    {
        return $query->orderBy('priority');
    }

    // Static helper methods
    public static function getPlaylistsForCategory(int $categoryId, int $userId): \Illuminate\Support\Collection
    {
        return self::forUser($userId)
            ->forCategory($categoryId)
            ->orderBy('priority')
            ->with('playlist')
            ->get()
            ->pluck('playlist');
    }

    public static function getCategoriesForPlaylist(int $playlistId, int $userId): \Illuminate\Support\Collection
    {
        return self::forUser($userId)
            ->forPlaylist($playlistId)
            ->with('category')
            ->get()
            ->pluck('category');
    }
}