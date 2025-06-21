<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_playlist_id')->constrained()->onDelete('cascade');
            $table->integer('priority')->default(1); // Order of playlist assignment for videos
            $table->timestamps();

            // Ensure unique category-playlist combinations per user
            $table->unique(['user_id', 'category_id', 'user_playlist_id'], 'unique_user_category_playlist');
            
            // Indexes for efficient queries
            $table->index(['category_id', 'priority']);
            $table->index(['user_playlist_id']);
            $table->index(['user_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_playlists');
    }
};