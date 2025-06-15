<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Contracts\YouTubeServiceInterface;
use App\Services\YouTubeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class YouTubeAuthController extends Controller
{
    public function __construct(
        private readonly YouTubeServiceInterface $youTubeService,
        private readonly YouTubeService $youTubeServiceConcrete,
    ) {}

    public function redirect(): RedirectResponse
    {
        $authUrl = $this->youTubeService->getAuthUrl();

        return redirect()->away($authUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        $code = $request->query('code');

        if (! $code) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Authorization failed. Please try again.');
        }

        try {
            $accessToken = $this->youTubeService->fetchAccessTokenWithAuthCode($code);

            // Store the access token in the session (for backward compatibility)
            session(['youtube_access_token' => $accessToken]);

            // Store the access token in the database for caching system
            $this->youTubeServiceConcrete->storeYouTubeAccount($accessToken);

            return redirect()
                ->route('dashboard')
                ->with('success', 'Successfully connected to YouTube!');
        } catch (Throwable $e) {
            Log::error('YouTube authentication error: '.$e->getMessage());

            return redirect()
                ->route('dashboard')
                ->with('error', 'Failed to authenticate with YouTube. Please try again.');
        }
    }
}
