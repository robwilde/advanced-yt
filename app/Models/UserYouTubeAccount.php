<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserYouTubeAccount extends Model
{
    protected $table = 'user_youtube_accounts';
    protected $fillable = [
        'user_id',
        'youtube_channel_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'last_synced_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(YouTubeSubscription::class, 'user_id', 'user_id');
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at?->isPast() ?? false;
    }

    public function needsSync(): bool
    {
        return $this->last_synced_at === null || $this->last_synced_at->diffInHours() > 24;
    }
}
