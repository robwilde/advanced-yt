<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SyncYouTubeSubscriptions;
use App\Services\YouTubeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

final class SubscriptionsController extends Controller
{
    public function __construct(
        private readonly YouTubeService $youtubeService,
    ) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if (!$user) {
            return view('subscriptions', [
                'hasSubscriptions' => false,
                'needsSync' => false,
            ]);
        }

        // Check if we have any cached subscriptions
        $hasSubscriptions = $this->youtubeService->hasCachedSubscriptions($user->id);
        $needsSync = $this->youtubeService->needsSync($user->id);

        // If we have no cached subscriptions and user has YouTube access, try sync
        if (!$hasSubscriptions && session('youtube_access_token')) {
            SyncYouTubeSubscriptions::dispatch($user->id);
            $needsSync = true;
        }

        return view('subscriptions', [
            'hasSubscriptions' => $hasSubscriptions,
            'needsSync' => $needsSync,
        ]);
    }

    /**
     * Manually trigger subscription sync
     */
    public function sync(Request $request): RedirectResponse
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
