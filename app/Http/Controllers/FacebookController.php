<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FacebookService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class FacebookController extends Controller
{
    /**
     * @var FacebookService
     */
    protected $facebookService;

    /**
     * Constructor with dependency injection.
     *
     * @param FacebookService $facebookService
     */
    public function __construct(FacebookService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    /**
     * Redirect user to Facebook for authentication.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login()
    {
        try {
            // Request only 'email' permission - 'public_profile' is granted by default
            $loginUrl = $this->facebookService->getLoginUrl(
                route('facebook.facebook_login_back'),
                ['email',
                    'public_profile',
                    'pages_show_list',
                    'pages_read_engagement',
                    'pages_manage_posts',
                    'pages_read_user_content',
                    'pages_messaging',
                    'pages_messaging_subscriptions'],auth()->user()->id
            );
            return redirect()->away($loginUrl);
        } catch (\Exception $e) {
            $this->facebookService->logError($e, 'login()');
            return redirect()->route('login')->with('error', 'Unable to connect to Facebook. Please try again.');
        }
    }

    /**
     * Handle Facebook OAuth callback.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback()
    {
        try {
            // Get access token from callback
            $accessToken = $this->facebookService->getAccessTokenFromCallback();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            Log::warning('Facebook Response Exception in callback', ['error' => $e->getMessage()]);
            return redirect()->route('login')->with('error', 'Facebook authentication failed. Please try again.');
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $this->facebookService->logError($e, 'callback() - SDK Exception');
            return redirect()->route('login')->with('error', 'Unable to process Facebook authentication.');
        }

        if (!isset($accessToken)) {
            Log::warning('No access token retrieved from Facebook callback');
            return redirect()->route('login')->with('error', 'Authentication was cancelled. Please try again.');
        }

        Log::info('Facebook access token retrieved', [
            'token_length' => strlen((string) $accessToken),
        ]);

        // Exchange for long-lived token (60 days)
        try {
            $longLivedTokenData = $this->facebookService->getLongLivedToken((string) $accessToken);
            $accessToken = $longLivedTokenData['access_token'];
            $expiresIn = $longLivedTokenData['expires_in'];
            $tokenExpiresAt = now()->addSeconds($expiresIn);

            Log::info('Long-lived token obtained', [
                'expires_in_days' => round($expiresIn / 86400, 1),
                'expires_at' => $tokenExpiresAt->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            // Continue with short-lived token if long-lived exchange fails
            Log::warning('Failed to get long-lived token, using short-lived', [
                'error' => $e->getMessage(),
            ]);
            $tokenExpiresAt = now()->addHours(2); // Short-lived tokens last ~2 hours
        }

        // Get user details from Facebook
        try {
            $fbUserData = $this->facebookService->getUserData($accessToken);
            Log::info('Facebook user data retrieved', [
                'facebook_id' => $fbUserData['id'],
                'email' => $fbUserData['email'] ?? 'no_email',
            ]);
        } catch (\Exception $e) {
            $this->facebookService->logError($e, 'callback() - getUserData()');
            return redirect()->route('login')->with('error', 'Unable to retrieve user information from Facebook.');
        }

        // Fetch user's Facebook pages
        $facebookPages = [];
        try {
            $facebookPages = $this->facebookService->getUserPages($accessToken);
            Log::info('Facebook pages retrieved', [
                'page_count' => count($facebookPages),
                'page_ids' => array_column($facebookPages, 'id'),
            ]);
        } catch (\Exception $e) {
            // Non-critical error - continue without pages
            Log::warning('Failed to retrieve Facebook pages', [
                'error' => $e->getMessage(),
            ]);
        }

        // Find or create user
        $user = User::where('facebook_id', $fbUserData['id'])->first();

        if (!$user && $fbUserData['email']) {
            // Check if user with this email already exists
            $user = User::where('email', $fbUserData['email'])->first();
        }

        if (!$user) {
            // Create new user from Facebook data
            try {
                $nameParts = explode(' ', $fbUserData['name'] ?? 'User', 2);
                $user = User::create([
                    'first_name' => $nameParts[0] ?? 'User',
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $fbUserData['email'] ?? 'fb_' . $fbUserData['id'] . '@facebook.local',
                    'facebook_id' => $fbUserData['id'],
                    'facebook_access_token' => $accessToken,
                    'facebook_token_expires_at' => $tokenExpiresAt,
                    'facebook_profile_picture' => $fbUserData['picture_url'] ?? null,
                    'facebook_pages' => $facebookPages,
                    'password' => Hash::make(uniqid()),
                ]);
                Log::info('New user created via Facebook', [
                    'user_id' => $user->id,
                    'facebook_id' => $fbUserData['id'],
                    'has_pages' => !empty($facebookPages),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create user from Facebook data', [
                    'error' => $e->getMessage(),
                    'facebook_id' => $fbUserData['id'],
                ]);
                return redirect()->route('login')->with('error', 'Failed to create account. Please try again.');
            }
        } else {
            // Update existing user with Facebook data
            try {
                $updateData = [
                    'facebook_id' => $fbUserData['id'],
                    'facebook_access_token' => $accessToken,
                    'facebook_token_expires_at' => $tokenExpiresAt,
                    'facebook_profile_picture' => $fbUserData['picture_url'] ?? $user->facebook_profile_picture,
                    'facebook_pages' => $facebookPages,
                ];

                $user->update($updateData);

                Log::info('Existing user updated with Facebook data', [
                    'user_id' => $user->id,
                    'facebook_id' => $fbUserData['id'],
                    'pages_updated' => !empty($facebookPages),
                    'token_expires_at' => $tokenExpiresAt->toDateTimeString(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to update user with Facebook data', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue anyway as user exists
            }
        }

        // Update last login and authenticate
        $user->updateLastLogin();
        Auth::login($user, true);

        Log::info('User logged in via Facebook successfully', [
            'user_id' => $user->id,
            'facebook_pages_count' => count($facebookPages),
            'token_expires_at' => $tokenExpiresAt->toDateTimeString(),
        ]);

        // Prepare success message with additional info
        $successMessage = 'Welcome! You have been logged in successfully with Facebook.';
        if (!empty($facebookPages)) {
            $successMessage .= ' We found ' . count($facebookPages) . ' Facebook page(s) connected to your account.';
        }

        return redirect()->route('dashboard')->with('success', $successMessage);
    }

    /**
     * Get authenticated user's Facebook information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMe()
    {
        $user = Auth::user();

        if (!$user->isConnectedToFacebook()) {
            Log::warning('Attempted to get Facebook info for non-connected user', ['user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => 'User is not connected to Facebook',
            ], 400);
        }

        // Return stored user information
        // Note: To get real-time data, we would need to store and refresh the access token
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->facebook_id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Disconnect user's Facebook account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect()
    {
        $user = Auth::user();

        if (!$user->isConnectedToFacebook()) {
            return redirect()->back()->with('error', 'No Facebook account is currently connected.');
        }

        try {
            $user->update([
                'facebook_id' => null,
                'facebook_access_token' => null,
                'facebook_token_expires_at' => null,
                'facebook_refresh_token' => null,
                'facebook_profile_picture' => null,
                'facebook_pages' => null,
            ]);
            Log::info('User disconnected from Facebook', ['user_id' => $user->id]);
            return redirect()->back()->with('success', 'Your Facebook account has been disconnected successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to disconnect Facebook', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to disconnect Facebook account. Please try again.');
        }
    }

    /**
     * Refresh Facebook data (pages, profile, etc.).
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refreshFacebookData()
    {
        $user = Auth::user();

        if (!$user->isConnectedToFacebook()) {
            return redirect()->back()->with('error', 'No Facebook account is currently connected.');
        }

        if (!$user->hasFacebookToken()) {
            Log::warning('User has no Facebook token to refresh', ['user_id' => $user->id]);
            return redirect()->back()->with('error', 'Please reconnect your Facebook account.');
        }

        // Check if token is expired
        if ($user->isFacebookTokenExpired()) {
            Log::warning('Facebook token is expired', ['user_id' => $user->id]);
            return redirect()->back()->with('error', 'Your Facebook token has expired. Please reconnect your account.');
        }

        try {
            $accessToken = $user->facebook_access_token;

            // Refresh user data
            try {
                $fbUserData = $this->facebookService->getUserData($accessToken);
                Log::info('Facebook user data refreshed', [
                    'user_id' => $user->id,
                    'facebook_id' => $fbUserData['id'],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to refresh user data', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Refresh pages
            try {
                $facebookPages = $this->facebookService->getUserPages($accessToken);
                $user->update([
                    'facebook_pages' => $facebookPages,
                    'facebook_profile_picture' => $fbUserData['picture_url'] ?? $user->facebook_profile_picture,
                ]);

                Log::info('Facebook data refreshed', [
                    'user_id' => $user->id,
                    'pages_count' => count($facebookPages),
                ]);

                $message = 'Facebook data refreshed successfully.';
                if (!empty($facebookPages)) {
                    $message .= ' Found ' . count($facebookPages) . ' page(s).';
                }

                return redirect()->back()->with('success', $message);
            } catch (\Exception $e) {
                Log::error('Failed to refresh Facebook pages', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                return redirect()->back()->with('error', 'Failed to refresh Facebook data. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to refresh Facebook data', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Failed to refresh Facebook data. Please try again.');
        }
    }
}
