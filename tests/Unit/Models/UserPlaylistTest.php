<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;
use App\Models\UserPlaylist;
use App\Models\CategoryPlaylist;
use Carbon\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
});

describe('UserPlaylist Model', function () {
    it('can create a user playlist', function () {
        $playlist = UserPlaylist::create([
            'user_id' => $this->user->id,
            'youtube_playlist_id' => 'PLtest123',
            'title' => 'Test Playlist',
            'description' => 'Test Description',
            'privacy_status' => 'private',
            'item_count' => 5,
            'is_managed' => true,
        ]);

        expect($playlist)
            ->toBeInstanceOf(UserPlaylist::class)
            ->title->toBe('Test Playlist')
            ->youtube_playlist_id->toBe('PLtest123')
            ->privacy_status->toBe('private')
            ->item_count->toBe(5)
            ->is_managed->toBeTrue();
    });

    it('belongs to a user', function () {
        $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);

        expect($playlist->user)
            ->toBeInstanceOf(User::class)
            ->id->toBe($this->user->id);
    });

    it('can have categories through many-to-many relationship', function () {
        $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
        
        $playlist->linkToCategory($this->category->id, $this->user->id, 1);

        expect($playlist->categories)
            ->toHaveCount(1)
            ->first()->id->toBe($this->category->id);

        expect($playlist->categories->first()->pivot)
            ->user_id->toBe($this->user->id)
            ->priority->toBe(1);
    });

    it('can check if linked to category', function () {
        $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
        
        expect($playlist->isLinkedToCategory($this->category->id))->toBeFalse();
        
        $playlist->linkToCategory($this->category->id, $this->user->id);
        
        expect($playlist->isLinkedToCategory($this->category->id))->toBeTrue();
    });

    it('can unlink from category', function () {
        $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
        $playlist->linkToCategory($this->category->id, $this->user->id);
        
        expect($playlist->isLinkedToCategory($this->category->id))->toBeTrue();
        
        $playlist->unlinkFromCategory($this->category->id);
        
        expect($playlist->fresh()->isLinkedToCategory($this->category->id))->toBeFalse();
    });

    it('can update item count', function () {
        $playlist = UserPlaylist::factory()->create([
            'user_id' => $this->user->id,
            'item_count' => 0,
            'last_synced_at' => null,
        ]);

        $playlist->updateItemCount(10);

        expect($playlist->fresh())
            ->item_count->toBe(10)
            ->last_synced_at->not->toBeNull();
    });

    it('can check if needs sync', function () {
        // Never synced
        $playlist = UserPlaylist::factory()->create([
            'user_id' => $this->user->id,
            'last_synced_at' => null,
        ]);
        expect($playlist->needsSync())->toBeTrue();

        // Recently synced
        $playlist->update(['last_synced_at' => now()]);
        expect($playlist->needsSync())->toBeFalse();

        // Old sync
        $playlist->update(['last_synced_at' => Carbon::now()->subHours(25)]);
        expect($playlist->needsSync())->toBeTrue();

        // Custom threshold
        expect($playlist->needsSync(48))->toBeFalse();
    });

    describe('scopes', function () {
        beforeEach(function () {
            $this->otherUser = User::factory()->create();
            
            $this->userPlaylist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
            $this->otherUserPlaylist = UserPlaylist::factory()->create(['user_id' => $this->otherUser->id]);
        });

        it('can scope by user', function () {
            $playlists = UserPlaylist::forUser($this->user->id)->get();

            expect($playlists)
                ->toHaveCount(1)
                ->first()->id->toBe($this->userPlaylist->id);
        });

        it('can scope managed playlists', function () {
            UserPlaylist::factory()->create([
                'user_id' => $this->user->id,
                'is_managed' => false,
            ]);

            $managedPlaylists = UserPlaylist::forUser($this->user->id)->managed()->get();

            expect($managedPlaylists)->toHaveCount(1);
        });

        it('can scope by privacy status', function () {
            UserPlaylist::factory()->create([
                'user_id' => $this->user->id,
                'privacy_status' => 'public',
            ]);

            $privatePlaylists = UserPlaylist::forUser($this->user->id)->private()->get();
            $publicPlaylists = UserPlaylist::forUser($this->user->id)->public()->get();

            expect($privatePlaylists)->toHaveCount(1);
            expect($publicPlaylists)->toHaveCount(1);
        });

        it('can scope playlists that need sync', function () {
            // Make existing playlist recently synced
            $this->userPlaylist->update(['last_synced_at' => now()]);
            
            // Create playlist that needs sync
            UserPlaylist::factory()->create([
                'user_id' => $this->user->id,
                'last_synced_at' => Carbon::now()->subHours(25),
            ]);

            $needsSyncPlaylists = UserPlaylist::forUser($this->user->id)->needsSync()->get();

            expect($needsSyncPlaylists)->toHaveCount(1);
        });
    });
});