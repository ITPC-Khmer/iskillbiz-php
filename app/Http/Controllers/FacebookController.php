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
        // Extract state parameter (contains user ID)
        $state = request()->input('state');
        $userId = $state ? (int) $state : null;

        Log::info('Facebook callback initiated', [
            'state' => $state,
            'user_id_from_state' => $userId,
            'has_code' => request()->has('code'),
            'query_params' => request()->query(),
        ]);

        try {
            // Get access token from callback
            $accessToken = $this->facebookService->getAccessTokenFromCallback();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            Log::warning('Facebook Response Exception in callback', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            $redirectRoute = $userId ? 'dashboard' : 'login';
            return redirect()->route($redirectRoute)->with('error', 'Facebook authentication failed. Please try again.');
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $this->facebookService->logError($e, 'callback() - SDK Exception');

            $redirectRoute = $userId ? 'dashboard' : 'login';
            return redirect()->route($redirectRoute)->with('error', 'Unable to process Facebook authentication.');
        }

        if (!isset($accessToken)) {
            Log::warning('No access token retrieved from Facebook callback', [
                'user_id' => $userId,
            ]);

            $redirectRoute = $userId ? 'dashboard' : 'login';
            return redirect()->route($redirectRoute)->with('error', 'Authentication was cancelled. Please try again.');
        }

        Log::info('Facebook access token retrieved', [
            'token_length' => strlen((string) $accessToken),
            'user_id' => $userId,
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
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            // Continue with short-lived token if long-lived exchange fails
            Log::warning('Failed to get long-lived token, using short-lived', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            $tokenExpiresAt = now()->addHours(2); // Short-lived tokens last ~2 hours
        }

        // Get user details from Facebook
        try {
            $fbUserData = $this->facebookService->getUserData($accessToken);
            Log::info('Facebook user data retrieved', [
                'facebook_id' => $fbUserData['id'],
                'email' => $fbUserData['email'] ?? 'no_email',
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            $this->facebookService->logError($e, 'callback() - getUserData()');

            $redirectRoute = $userId ? 'dashboard' : 'login';
            return redirect()->route($redirectRoute)->with('error', 'Unable to retrieve user information from Facebook.');
        }

        // Fetch user's Facebook pages
        $facebookPages = [];
        try {
            $facebookPages = $this->facebookService->getUserPages($accessToken);
            Log::info('Facebook pages retrieved', [
                'page_count' => count($facebookPages),
                'page_ids' => array_column($facebookPages, 'id'),
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            // Non-critical error - continue without pages
            Log::warning('Failed to retrieve Facebook pages', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
        }

        // Determine which user to update/create
        $user = null;

        // Priority 1: If state contains user ID (user is already logged in and connecting Facebook)
        if ($userId) {
            $user = User::find($userId);

            if ($user) {
                Log::info('User found from state parameter - connecting Facebook to existing account', [
                    'user_id' => $user->id,
                    'facebook_id' => $fbUserData['id'],
                ]);
            } else {
                Log::warning('User ID from state not found', [
                    'state_user_id' => $userId,
                ]);
            }
        }

        // Priority 2: Find by Facebook ID
        if (!$user) {
            $user = User::where('facebook_id', $fbUserData['id'])->first();

            if ($user) {
                Log::info('User found by Facebook ID', [
                    'user_id' => $user->id,
                    'facebook_id' => $fbUserData['id'],
                ]);
            }
        }

        // Priority 3: Find by email (if Facebook provides email)
        if (!$user && isset($fbUserData['email'])) {
            $user = User::where('email', $fbUserData['email'])->first();

            if ($user) {
                Log::info('User found by email - linking Facebook account', [
                    'user_id' => $user->id,
                    'email' => $fbUserData['email'],
                    'facebook_id' => $fbUserData['id'],
                ]);
            }
        }

        // Create new user if none found and no state (new registration via Facebook)
        if (!$user && !$userId) {
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
        } elseif ($user) {
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
                    'was_from_state' => $userId === $user->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to update user with Facebook data', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue anyway as user exists
            }
        } else {
            // Edge case: state provided but no user found and can't create
            Log::error('Cannot process Facebook callback - user not found', [
                'state_user_id' => $userId,
                'facebook_id' => $fbUserData['id'],
            ]);
            return redirect()->route('login')->with('error', 'Unable to complete Facebook connection. Please try again.');
        }

        // Update last login and authenticate (only if not already logged in)
        if (!Auth::check() || Auth::id() !== $user->id) {
            $user->updateLastLogin();
            Auth::login($user, true);

            Log::info('User logged in via Facebook successfully', [
                'user_id' => $user->id,
                'facebook_pages_count' => count($facebookPages),
                'token_expires_at' => $tokenExpiresAt->toDateTimeString(),
            ]);
        } else {
            Log::info('Facebook connected to already logged-in user', [
                'user_id' => $user->id,
                'facebook_pages_count' => count($facebookPages),
                'token_expires_at' => $tokenExpiresAt->toDateTimeString(),
            ]);
        }

        // Prepare success message with additional info
        $isConnection = $userId !== null;
        $successMessage = $isConnection
            ? 'Facebook account connected successfully!'
            : 'Welcome! You have been logged in successfully with Facebook.';

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

    /**
     * Alternative Facebook callback handler for /home/facebook_login_back route.
     * This provides backward compatibility or handles different app configurations.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function facebookLoginBack()
    {
        Log::info('Facebook login back route accessed', [
            'route' => '/home/facebook_login_back',
            'query_params' => request()->all(),
        ]);

        // Use the same callback logic
        return $this->callback();
    }

    /**
     * Display all Facebook data stored for the authenticated user.
     * Useful for debugging and verifying data storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showStoredData()
    {
        $user = Auth::user();

        if (!$user->isConnectedToFacebook()) {
            return response()->json([
                'status' => 'not_connected',
                'message' => 'No Facebook account connected',
            ], 404);
        }

        // Prepare comprehensive Facebook data
        $facebookData = [
            'connection_status' => [
                'connected' => $user->isConnectedToFacebook(),
                'has_token' => $user->hasFacebookToken(),
                'token_expired' => $user->isFacebookTokenExpired(),
                'has_pages' => $user->hasFacebookPages(),
            ],
            'user_data' => [
                'facebook_id' => $user->facebook_id,
                'profile_picture' => $user->facebook_profile_picture,
            ],
            'token_data' => [
                'has_access_token' => !empty($user->facebook_access_token),
                'token_expires_at' => $user->facebook_token_expires_at?->toDateTimeString(),
                'expires_in_days' => $user->facebook_token_expires_at ?
                    now()->diffInDays($user->facebook_token_expires_at, false) : null,
                'has_refresh_token' => !empty($user->facebook_refresh_token),
            ],
            'pages_data' => [
                'total_pages' => count($user->getFacebookPages()),
                'pages' => $user->getFacebookPages(),
            ],
            'storage_timestamps' => [
                'user_created_at' => $user->created_at?->toDateTimeString(),
                'user_updated_at' => $user->updated_at?->toDateTimeString(),
                'last_login_at' => $user->last_login_at?->toDateTimeString(),
            ],
        ];

        Log::info('Facebook stored data retrieved', [
            'user_id' => $user->id,
            'pages_count' => $facebookData['pages_data']['total_pages'],
            'token_status' => $facebookData['connection_status']['token_expired'] ? 'expired' : 'valid',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $facebookData,
        ], 200);
    }
}
