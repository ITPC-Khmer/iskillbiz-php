<?php

namespace App\Http\Controllers;

use App\Models\User;
use Facebook\Facebook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class FacebookController extends Controller
{
    protected $facebook;

    public function __construct()
    {
        $this->facebook = new Facebook([
            'app_id' => config('services.facebook.app_id'),
            'app_secret' => config('services.facebook.app_secret'),
            'default_graph_version' => 'v18.0',
        ]);
    }

    /**
     * Redirect to Facebook for authentication
     */
    public function login()
    {
        $helper = $this->facebook->getRedirectLoginHelper();
        $permissions = ['email', 'public_profile'];
        $loginUrl = $helper->getLoginUrl(route('facebook.callback'), $permissions);

        return redirect()->away($loginUrl);
    }

    /**
     * Handle Facebook callback
     */
    public function callback()
    {
        $helper = $this->facebook->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            return redirect()->route('login')->with('error', 'Facebook API Error: ' . $e->getMessage());
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return redirect()->route('login')->with('error', 'Facebook SDK Error: ' . $e->getMessage());
        }

        if (!isset($accessToken)) {
            return redirect()->route('login')->with('error', 'No access token retrieved');
        }

        // Get user details from Facebook
        try {
            $response = $this->facebook->get('/me?fields=id,name,email,picture', $accessToken);
            $fbUser = $response->getGraphUser();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            return redirect()->route('login')->with('error', 'Failed to retrieve user info: ' . $e->getMessage());
        }

        // Find or create user
        $user = User::where('facebook_id', $fbUser->getId())->first();

        if (!$user) {
            // Check if email already exists
            if ($fbUser->getEmail()) {
                $user = User::where('email', $fbUser->getEmail())->first();
            }

            if (!$user) {
                // Create new user
                $nameParts = explode(' ', $fbUser->getName(), 2);
                $user = User::create([
                    'name' => $fbUser->getName(),
                    'email' => $fbUser->getEmail() ?? 'fb_' . $fbUser->getId() . '@facebook.local',
                    'facebook_id' => $fbUser->getId(),
                    'password' => Hash::make(uniqid()),
                ]);
            } else {
                // Update existing user with Facebook ID
                $user->update(['facebook_id' => $fbUser->getId()]);
            }
        }

        // Login the user
        Auth::login($user, true);

        return redirect()->route('dashboard')->with('success', 'Logged in successfully with Facebook!');
    }

    /**
     * Get Facebook user info (requires authenticated user)
     */
    public function getMe()
    {
        $user = Auth::user();

        if (!$user->facebook_id) {
            return response()->json(['error' => 'User not connected to Facebook'], 400);
        }

        try {
            // This would require storing and refreshing the access token
            // For now, just return the stored user info
            $response = $this->facebook->get('/' . $user->facebook_id . '?fields=id,name,email,picture', config('services.facebook.app_secret'));
            $fbUser = $response->getGraphUser();

            return response()->json([
                'id' => $fbUser->getId(),
                'name' => $fbUser->getName(),
                'email' => $fbUser->getEmail(),
                'picture' => $fbUser->getPicture()->getUrl(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'id' => $user->facebook_id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }
    }

    /**
     * Disconnect Facebook account
     */
    public function disconnect()
    {
        $user = Auth::user();

        if ($user->facebook_id) {
            $user->update(['facebook_id' => null]);
            return redirect()->back()->with('success', 'Facebook account disconnected successfully!');
        }

        return redirect()->back()->with('error', 'No Facebook account connected!');
    }
}
