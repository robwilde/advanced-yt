<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Models\UserYouTubeAccount;
use App\Services\YouTubeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SyncYouTubeSubscriptions implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300; // 5 minutes timeout
    public int $tries = 3;

    public function __construct(
        private readonly int $userId
    ) {
        $this->onQueue('default');
    }

    public function handle(YouTubeService $youtubeService): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::error('User not found for YouTube sync', ['userId' => $this->userId]);
            return;
        }

        $account = UserYouTubeAccount::where('user_id', $this->userId)->first();
        if (!$account) {
            Log::error('YouTube account not found for user', ['userId' => $this->userId]);
            return;
        }

        try {
            // Set the access token for the YouTube service
            $tokenData = json_decode($account->access_token, true);
            $youtubeService->setAccessToken($tokenData);

            // Temporarily authenticate as the user for the sync
            Auth::loginUsingId($this->userId);

            Log::info('Starting YouTube subscriptions sync', [
                'userId' => $this->userId,
                'lastSynced' => $account->last_synced_at?->toDateTimeString()
            ]);

            $result = $youtubeService->syncAllSubscriptions();

            Log::info('YouTube subscriptions sync completed', [
                'userId' => $this->userId,
                'result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('YouTube subscriptions sync failed', [
                'userId' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        } finally {
            Auth::logout();
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('YouTube subscriptions sync job failed permanently', [
            'userId' => $this->userId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
