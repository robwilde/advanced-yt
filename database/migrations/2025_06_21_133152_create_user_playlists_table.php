<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('youtube_playlist_id')->unique(); // YouTube's playlist ID
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->enum('privacy_status', ['public', 'unlisted', 'private'])->default('private');
            $table->integer('item_count')->default(0);
            $table->boolean('is_managed')->default(true); // Whether this is managed by our app
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_managed']);
            $table->index(['user_id', 'privacy_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_playlists');
    }
};