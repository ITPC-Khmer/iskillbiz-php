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
            $loginUrl = $this->facebookService->getLoginUrl(route('facebook.callback'));
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

        // Get user details from Facebook
        try {
            $fbUserData = $this->facebookService->getUserData($accessToken);
        } catch (\Exception $e) {
            $this->facebookService->logError($e, 'callback() - getUserData()');
            return redirect()->route('login')->with('error', 'Unable to retrieve user information from Facebook.');
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
                    'password' => Hash::make(uniqid()),
                ]);
                Log::info('New user created via Facebook', ['user_id' => $user->id, 'facebook_id' => $fbUserData['id']]);
            } catch (\Exception $e) {
                Log::error('Failed to create user from Facebook data', [
                    'error' => $e->getMessage(),
                    'facebook_id' => $fbUserData['id'],
                ]);
                return redirect()->route('login')->with('error', 'Failed to create account. Please try again.');
            }
        } else {
            // Update existing user with Facebook ID if not already set
            if (!$user->facebook_id) {
                $user->update(['facebook_id' => $fbUserData['id']]);
                Log::info('Existing user connected to Facebook', ['user_id' => $user->id]);
            }
        }

        // Update last login and authenticate
        $user->updateLastLogin();
        Auth::login($user, true);

        Log::info('User logged in via Facebook', ['user_id' => $user->id]);
        return redirect()->route('dashboard')->with('success', 'Welcome! You have been logged in successfully with Facebook.');
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
            $user->update(['facebook_id' => null]);
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
}
