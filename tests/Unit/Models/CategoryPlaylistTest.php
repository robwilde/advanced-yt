<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;
use App\Models\UserPlaylist;
use App\Models\CategoryPlaylist;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create(['user_id' => $this->user->id]);
    $this->playlist = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
});

describe('CategoryPlaylist Model', function () {
    it('can create a category playlist mapping', function () {
        $mapping = CategoryPlaylist::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'user_playlist_id' => $this->playlist->id,
            'priority' => 1,
        ]);

        expect($mapping)
            ->toBeInstanceOf(CategoryPlaylist::class)
            ->user_id->toBe($this->user->id)
            ->category_id->toBe($this->category->id)
            ->user_playlist_id->toBe($this->playlist->id)
            ->priority->toBe(1);
    });

    it('belongs to user, category, and playlist', function () {
        $mapping = CategoryPlaylist::create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'user_playlist_id' => $this->playlist->id,
            'priority' => 1,
        ]);

        expect($mapping->user)
            ->toBeInstanceOf(User::class)
            ->id->toBe($this->user->id);

        expect($mapping->category)
            ->toBeInstanceOf(Category::class)
            ->id->toBe($this->category->id);

        expect($mapping->playlist)
            ->toBeInstanceOf(UserPlaylist::class)
            ->id->toBe($this->playlist->id);
    });

    describe('scopes', function () {
        beforeEach(function () {
            $this->otherUser = User::factory()->create();
            $this->otherCategory = Category::factory()->create(['user_id' => $this->otherUser->id]);
            $this->otherPlaylist = UserPlaylist::factory()->create(['user_id' => $this->otherUser->id]);

            $this->userMapping = CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->category->id,
                'user_playlist_id' => $this->playlist->id,
                'priority' => 1,
            ]);

            $this->otherUserMapping = CategoryPlaylist::create([
                'user_id' => $this->otherUser->id,
                'category_id' => $this->otherCategory->id,
                'user_playlist_id' => $this->otherPlaylist->id,
                'priority' => 2,
            ]);
        });

        it('can scope by user', function () {
            $mappings = CategoryPlaylist::forUser($this->user->id)->get();

            expect($mappings)
                ->toHaveCount(1)
                ->first()->id->toBe($this->userMapping->id);
        });

        it('can scope by category', function () {
            $mappings = CategoryPlaylist::forCategory($this->category->id)->get();

            expect($mappings)
                ->toHaveCount(1)
                ->first()->id->toBe($this->userMapping->id);
        });

        it('can scope by playlist', function () {
            $mappings = CategoryPlaylist::forPlaylist($this->playlist->id)->get();

            expect($mappings)
                ->toHaveCount(1)
                ->first()->id->toBe($this->userMapping->id);
        });

        it('can order by priority', function () {
            // Create multiple mappings with different priorities
            $mapping2 = CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->category->id,
                'user_playlist_id' => UserPlaylist::factory()->create(['user_id' => $this->user->id])->id,
                'priority' => 3,
            ]);

            $mapping3 = CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->category->id,
                'user_playlist_id' => UserPlaylist::factory()->create(['user_id' => $this->user->id])->id,
                'priority' => 2,
            ]);

            $mappings = CategoryPlaylist::forCategory($this->category->id)
                ->orderedByPriority()
                ->get();

            expect($mappings)
                ->toHaveCount(3)
                ->sequence(
                    fn ($mapping) => $mapping->priority->toBe(1),
                    fn ($mapping) => $mapping->priority->toBe(2),
                    fn ($mapping) => $mapping->priority->toBe(3)
                );
        });
    });

    describe('static helper methods', function () {
        beforeEach(function () {
            $this->playlist2 = UserPlaylist::factory()->create(['user_id' => $this->user->id]);
            $this->category2 = Category::factory()->create(['user_id' => $this->user->id]);

            // Create mappings
            CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->category->id,
                'user_playlist_id' => $this->playlist->id,
                'priority' => 1,
            ]);

            CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->category->id,
                'user_playlist_id' => $this->playlist2->id,
                'priority' => 2,
            ]);

            CategoryPlaylist::create([
                'user_id' => $this->user->id,
                'category_id' => $this->category2->id,
                'user_playlist_id' => $this->playlist->id,
                'priority' => 1,
            ]);
        });

        it('can get playlists for a category', function () {
            $playlists = CategoryPlaylist::getPlaylistsForCategory($this->category->id, $this->user->id);

            expect($playlists)
                ->toHaveCount(2)
                ->sequence(
                    fn ($playlist) => $playlist->id->toBe($this->playlist->id),
                    fn ($playlist) => $playlist->id->toBe($this->playlist2->id)
                );
        });

        it('can get categories for a playlist', function () {
            $categories = CategoryPlaylist::getCategoriesForPlaylist($this->playlist->id, $this->user->id);

            expect($categories)
                ->toHaveCount(2)
                ->each->toBeInstanceOf(Category::class);
        });
    });
});