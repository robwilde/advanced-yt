<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('youtube_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subscription_id'); // YouTube subscription ID
            $table->string('channel_id'); // Subscribed channel ID
            $table->string('channel_title');
            $table->text('channel_description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->timestamp('subscribed_at'); // When user subscribed to channel
            $table->timestamp('cached_at'); // When we cached this data
            $table->timestamps();

            $table->unique(['user_id', 'subscription_id']);
            $table->index(['user_id', 'cached_at']);
            $table->index(['user_id', 'subscribed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youtube_subscriptions');
    }
};
