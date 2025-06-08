<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Contracts\YouTubeServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class YouTubeAuthController extends Controller
{
    public function __construct(
        private readonly YouTubeServiceInterface $youTubeService,
    ) {}

    public function redirect(): RedirectResponse
    {
        $authUrl = $this->youTubeService->getAuthUrl();

        return redirect()->away($authUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        $code = $request->query('code');

        if (!$code) {
            return redirect()->route('dashboard')
                ->with('error', 'Authorization failed. Please try again.');
        }

        try {
            $accessToken = $this->youTubeService->fetchAccessTokenWithAuthCode($code);

            // Store the access token in the session
            session(['youtube_access_token' => $accessToken]);

            return redirect()->route('dashboard')
                ->with('success', 'Successfully connected to YouTube!');
        } catch (\Throwable $e) {
            logger()->error('YouTube authentication error: ' . $e->getMessage());

            return redirect()->route('dashboard')
                ->with('error', 'Failed to authenticate with YouTube. Please try again.');
        }
    }
}
