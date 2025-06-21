<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\CategoryPlaylist;
use App\Models\User;
use App\Models\UserPlaylist;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('Playlist Management Feature', function () {
    describe('Database Relationships', function () {
        it('can create complete category-playlist mappings', function () {
            // Create test data
            $category = Category::factory()->create(['user_id' => $this->user->id]);
            $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);

            // Create mapping
            $mapping = CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'user_playlist_id' => $playlist->id,
                'priority' => 1,
            ]);

            // Verify database structure
            $this->assertDatabaseHas('category_playlists', [
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'user_playlist_id' => $playlist->id,
                'priority' => 1,
            ]);

            // Verify relationships work
            expect($category->fresh()->playlists)->toHaveCount(1);
            expect($playlist->fresh()->categories)->toHaveCount(1);
            expect($mapping->fresh()->user->id)->toBe($this->user->id);
        });

        it('enforces unique category-playlist combinations per user', function () {
            $category = Category::factory()->create(['user_id' => $this->user->id]);
            $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);

            // Create first mapping
            CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'user_playlist_id' => $playlist->id,
                'priority' => 1,
            ]);

            // Attempt to create duplicate should fail
            expect(fn () => CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'user_playlist_id' => $playlist->id,
                'priority' => 2,
            ]))->toThrow(\Illuminate\Database\QueryException::class);
        });

        it('allows same category-playlist combination for different users', function () {
            $otherUser = User::factory()->create();
            $category1 = Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tech']);
            $category2 = Category::factory()->create(['user_id' => $otherUser->id, 'name' => 'Tech']);
            $playlist1 = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
            $playlist2 = UserPlaylist::factory()->create(['user_id' => $otherUser->id]);

            // Both users can have their own mappings
            $mapping1 = CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $category1->id,
                'user_playlist_id' => $playlist1->id,
                'priority' => 1,
            ]);

            $mapping2 = CategoryPlaylist::create([
                'user_id' => $otherUser->id,
                'category_id' => $category2->id,
                'user_playlist_id' => $playlist2->id,
                'priority' => 1,
            ]);

            expect($mapping1)->toBeInstanceOf(CategoryPlaylist::class);
            expect($mapping2)->toBeInstanceOf(CategoryPlaylist::class);
        });
    });

    describe('Complex Queries and Workflows', function () {
        beforeEach(function () {
            // Create test data structure
            $this->categories = [
                'tech' => Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Tech']),
                'science' => Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Science']),
                'gaming' => Category::factory()->create(['user_id' => $this->user->id, 'name' => 'Gaming']),
            ];

            $this->playlists = [
                'primary_tech' => UserPlaylist::factory()->create(['user_id' => $this->user->id, 'title' => 'Primary Tech']),
                'secondary_tech' => UserPlaylist::factory()->create(['user_id' => $this->user->id, 'title' => 'Secondary Tech']),
                'science_videos' => UserPlaylist::factory()->create(['user_id' => $this->user->id, 'title' => 'Science Videos']),
                'gaming_highlights' => UserPlaylist::factory()->create(['user_id' => $this->user->id, 'title' => 'Gaming Highlights']),
            ];
        });

        it('can handle complex many-to-many relationships with priorities', function () {
            // Tech category maps to two playlists with different priorities
            CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->categories['tech']->id,
                'user_playlist_id' => $this->playlists['primary_tech']->id,
                'priority' => 1,
            ]);

            CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->categories['tech']->id,
                'user_playlist_id' => $this->playlists['secondary_tech']->id,
                'priority' => 2,
            ]);

            // Science category maps to one playlist
            CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->categories['science']->id,
                'user_playlist_id' => $this->playlists['science_videos']->id,
                'priority' => 1,
            ]);

            // One playlist can serve multiple categories
            CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->categories['gaming']->id,
                'user_playlist_id' => $this->playlists['gaming_highlights']->id,
                'priority' => 1,
            ]);

            // Test querying playlists for tech category (should be ordered by priority)
            $techPlaylists = CategoryPlaylist::getPlaylistsForCategory(
                $this->categories['tech']->id,
                $this->user->id
            );

            expect($techPlaylists)
                ->toHaveCount(2)
                ->first()->title->toBe('Primary Tech'); // Priority 1 should come first

            // Test querying categories for a playlist
            $techCategories = CategoryPlaylist::getCategoriesForPlaylist(
                $this->playlists['primary_tech']->id,
                $this->user->id
            );

            expect($techCategories)
                ->toHaveCount(1)
                ->first()->name->toBe('Tech');
        });

        it('can efficiently query playlists that need sync', function () {
            // Make existing playlists recently synced
            foreach ($this->playlists as $playlist) {
                $playlist->update(['last_synced_at' => now()]);
            }

            // Create playlists with different sync statuses
            $this->playlists['needs_sync'] = UserPlaylist::factory()
                ->needsSync()
                ->create(['user_id' => $this->user->id]);

            $this->playlists['recently_synced'] = UserPlaylist::factory()
                ->recentlySync()
                ->create(['user_id' => $this->user->id]);

            $needsSyncPlaylists = UserPlaylist::forUser($this->user->id)
                ->needsSync()
                ->get();

            expect($needsSyncPlaylists)
                ->toHaveCount(1)
                ->first()->id->toBe($this->playlists['needs_sync']->id);
        });

        it('can filter playlists by management status and privacy', function () {
            // Clear existing playlists from beforeEach to avoid interference
            UserPlaylist::where('user_id', $this->user->id)->delete();
            
            // Create playlists with different statuses
            $managedPrivate = UserPlaylist::factory()
                ->managed()
                ->private()
                ->create(['user_id' => $this->user->id]);

            $unmanagedPublic = UserPlaylist::factory()
                ->unmanaged()
                ->public()
                ->create(['user_id' => $this->user->id]);

            $managedPublic = UserPlaylist::factory()
                ->managed()
                ->public()
                ->create(['user_id' => $this->user->id]);

            // Test filtering
            $managedPlaylists = UserPlaylist::forUser($this->user->id)
                ->managed()
                ->get();

            $publicPlaylists = UserPlaylist::forUser($this->user->id)
                ->public()
                ->get();

            expect($managedPlaylists)->toHaveCount(2); // managedPrivate + managedPublic
            expect($publicPlaylists)->toHaveCount(2); // unmanagedPublic + managedPublic
        });
    });

    describe('Data Integrity and Cascading', function () {
        it('maintains referential integrity on user deletion', function () {
            $category = Category::factory()->create(['user_id' => $this->user->id]);
            $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
            
            $mapping = CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'user_playlist_id' => $playlist->id,
                'priority' => 1,
            ]);

            // Delete user should cascade to all related records
            $this->user->delete();

            // Verify all related records are deleted
            $this->assertDatabaseMissing('categories', ['id' => $category->id]);
            $this->assertDatabaseMissing('user_playlists', ['id' => $playlist->id]);
            $this->assertDatabaseMissing('category_playlists', ['id' => $mapping->id]);
        });

        it('maintains referential integrity on category deletion', function () {
            $category = Category::factory()->create(['user_id' => $this->user->id]);
            $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
            
            $mapping = CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'user_playlist_id' => $playlist->id,
                'priority' => 1,
            ]);

            // Delete category should cascade to mappings but not playlists
            $category->delete();

            $this->assertDatabaseMissing('category_playlists', ['id' => $mapping->id]);
            $this->assertDatabaseHas('user_playlists', ['id' => $playlist->id]);
        });

        it('maintains referential integrity on playlist deletion', function () {
            $category = Category::factory()->create(['user_id' => $this->user->id]);
            $playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
            
            $mapping = CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $category->id,
                'user_playlist_id' => $playlist->id,
                'priority' => 1,
            ]);

            // Delete playlist should cascade to mappings but not categories
            $playlist->delete();

            $this->assertDatabaseMissing('category_playlists', ['id' => $mapping->id]);
            $this->assertDatabaseHas('categories', ['id' => $category->id]);
        });
    });
});