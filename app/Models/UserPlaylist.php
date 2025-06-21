<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

final class UserPlaylist extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'youtube_playlist_id',
        'title',
        'description',
        'thumbnail_url',
        'privacy_status',
        'item_count',
        'is_managed',
        'last_synced_at',
    ];

    protected $casts = [
        'is_managed' => 'boolean',
        'last_synced_at' => 'datetime',
        'item_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'category_playlists',
            'user_playlist_id',
            'category_id'
        )->withPivot(['user_id', 'priority'])->withTimestamps();
    }

    public function categoryMappings(): HasMany
    {
        return $this->hasMany(CategoryPlaylist::class);
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeManaged(Builder $query): Builder
    {
        return $query->where('is_managed', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('privacy_status', 'public');
    }

    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('privacy_status', 'private');
    }

    public function scopeNeedsSync(Builder $query, int $hoursThreshold = 24): Builder
    {
        return $query->where(function (Builder $q) use ($hoursThreshold) {
            $q->whereNull('last_synced_at')
              ->orWhere('last_synced_at', '<', Carbon::now()->subHours($hoursThreshold));
        });
    }

    // Helper methods
    public function needsSync(int $hoursThreshold = 24): bool
    {
        return $this->last_synced_at === null || 
               $this->last_synced_at->isBefore(Carbon::now()->subHours($hoursThreshold));
    }

    public function isLinkedToCategory(int $categoryId): bool
    {
        return $this->categories()->where('categories.id', $categoryId)->exists();
    }

    public function linkToCategory(int $categoryId, int $userId, int $priority = 1): void
    {
        if (!$this->isLinkedToCategory($categoryId)) {
            $this->categories()->attach($categoryId, [
                'user_id' => $userId,
                'priority' => $priority,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function unlinkFromCategory(int $categoryId): void
    {
        $this->categories()->detach($categoryId);
    }

    public function updateItemCount(int $count): void
    {
        $this->update([
            'item_count' => $count,
            'last_synced_at' => now(),
        ]);
    }
}