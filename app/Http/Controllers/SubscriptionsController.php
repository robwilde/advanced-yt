<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\FetchYouTubeSubscriptions;
use App\Jobs\SyncYouTubeSubscriptions;
use App\Models\UserYouTubeAccount;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

final class SubscriptionsController extends Controller
{
    public function __construct(
        private readonly FetchYouTubeSubscriptions $fetchYouTubeSubscriptions,
        private readonly YouTubeService $youtubeService,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $page = (int) $request->get('page', 1);
        $perPage = 50; // Show 50 subscriptions per page (10 rows of 5)
        
        $subscriptions = [];
        $pagination = ['total' => 0, 'hasMore' => false, 'currentPage' => 1, 'perPage' => $perPage];
        $needsSync = false;

        if (!$user) {
            return view('subscriptions', [
                'subscriptions' => $subscriptions,
                'pagination' => $pagination,
                'needsSync' => $needsSync,
            ]);
        }

        try {
            // Check if we have cached subscriptions
            $result = $this->youtubeService->getCachedSubscriptions($page, $perPage);
            $subscriptions = $result['items'];
            $pagination = [
                'total' => $result['total'],
                'hasMore' => $result['hasMore'],
                'currentPage' => $result['currentPage'],
                'perPage' => $result['perPage'],
            ];

            // Check if we need to sync (no cached data or data is stale)
            $needsSync = $this->youtubeService->needsSync($user->id);
            
            // If we have no cached subscriptions and user has YouTube access, try sync
            if (empty($subscriptions) && session('youtube_access_token')) {
                // Dispatch background job for sync
                SyncYouTubeSubscriptions::dispatch($user->id);
                $needsSync = true;
            }
            
        } catch (Throwable $e) {
            Log::error('Failed to fetch cached YouTube subscriptions: '.$e->getMessage());
            
            // Fallback to direct API call if cache fails
            $accessToken = session('youtube_access_token');
            if ($accessToken) {
                try {
                    $subscriptions = $this->fetchYouTubeSubscriptions->handle($accessToken);
                } catch (Throwable $fallbackError) {
                    Log::error('Fallback YouTube API call also failed: '.$fallbackError->getMessage());
                }
            }
        }

        return view('subscriptions', [
            'subscriptions' => $subscriptions,
            'pagination' => $pagination,
            'needsSync' => $needsSync,
        ]);
    }

    /**
     * Manually trigger subscription sync
     */
    public function sync(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        
        if (!$user || !session('youtube_access_token')) {
            return redirect()->route('subscriptions')
                ->with('error', 'YouTube authentication required');
        }

        try {
            // Dispatch background job
            SyncYouTubeSubscriptions::dispatch($user->id);
            
            return redirect()->route('subscriptions')
                ->with('success', 'Subscription sync started. This may take a few minutes for large subscription lists.');
                
        } catch (Throwable $e) {
            Log::error('Failed to dispatch YouTube sync job: '.$e->getMessage());
            
            return redirect()->route('subscriptions')
                ->with('error', 'Failed to start subscription sync. Please try again.');
        }
    }
}