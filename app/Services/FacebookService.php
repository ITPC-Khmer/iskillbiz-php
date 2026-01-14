<?php

namespace App\Services;

use Facebook\Facebook;
use Illuminate\Support\Facades\Log;

class FacebookService
{
    /**
     * @var Facebook
     */
    protected $facebook;

    /**
     * Initialize the Facebook SDK with configuration.
     */
    public function __construct()
    {
        $this->facebook = new Facebook([
            'app_id' => config('services.facebook.app_id'),
            'app_secret' => config('services.facebook.app_secret'),
            'default_graph_version' => 'v18.0',
        ]);
    }

    /**
     * Get the Facebook SDK instance.
     *
     * @return Facebook
     */
    public function getInstance(): Facebook
    {
        return $this->facebook;
    }

    /**
     * Get the redirect login helper.
     *
     * @return \Facebook\Helpers\FacebookRedirectLoginHelper
     */
    public function getRedirectLoginHelper()
    {
        return $this->facebook->getRedirectLoginHelper();
    }

    /**
     * Get Facebook login URL with permissions.
     * Uses Facebook SDK's helper to properly manage state/CSRF tokens.
     *
     * @param string $callbackUrl
     * @param array $permissions
     * @param string|null $customState Custom state to embed (e.g., user ID)
     * @return string
     */
    public function getLoginUrl(string $callbackUrl, array $permissions = ['email',
        'public_profile',
        'pages_show_list',
        'pages_read_engagement',
        'pages_manage_posts',
        'pages_read_user_content',
        'pages_messaging',
        'pages_messaging_subscriptions'], ?string $customState = null): string
    {
        $helper = $this->getRedirectLoginHelper();

        // Store custom state in session for retrieval after callback
        if ($customState !== null) {
            session(['facebook_custom_state' => $customState]);
            Log::info('Storing custom state in session', [
                'custom_state' => $customState,
            ]);
        }

        // Use SDK's built-in method which handles state/CSRF automatically
        $loginUrl = $helper->getLoginUrl($callbackUrl, $permissions);

        Log::info('Facebook login URL generated', [
            'callback_url' => $callbackUrl,
            'permissions' => $permissions,
            'has_custom_state' => $customState !== null,
        ]);

        return $loginUrl;
    }

    /**
     * Get access token from callback.
     *
     * @return \Facebook\Authentication\AccessToken|null
     * @throws \Facebook\Exceptions\FacebookResponseException
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getAccessTokenFromCallback()
    {
        $helper = $this->getRedirectLoginHelper();
        return $helper->getAccessToken();
    }

    /**
     * Get custom state stored before redirect (e.g., user ID).
     *
     * @return string|null
     */
    public function getCustomState(): ?string
    {
        $customState = session('facebook_custom_state');

        // Clear it from session after retrieval
        session()->forget('facebook_custom_state');

        Log::info('Retrieved custom state from session', [
            'custom_state' => $customState,
        ]);

        return $customState;
    }

    /**
     * Get user data from Facebook.
     *
     * @param string $accessToken
     * @param string $fields
     * @return array
     * @throws \Facebook\Exceptions\FacebookResponseException
     */
    public function getUserData($accessToken, string $fields = 'id,name,email,picture'): array
    {
        try {
            $response = $this->facebook->get("/me?fields={$fields}", $accessToken);
            $user = $response->getGraphUser();

            return [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'picture_url' => $user->getPicture()?->getUrl(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to retrieve Facebook user data', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        }
    }

    /**
     * Log Facebook errors.
     *
     * @param \Exception $e
     * @param string $context
     * @return void
     */
    public function logError(\Exception $e, string $context = ''): void
    {
        Log::error("Facebook SDK Error: {$context}", [
            'exception' => class_basename($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
        ]);
    }

    /**
     * Exchange short-lived token for long-lived token.
     *
     * @param string $accessToken
     * @return array Contains 'access_token' and 'expires_in'
     * @throws \Exception
     */
    public function getLongLivedToken(string $accessToken): array
    {
        try {
            $oauth2Client = $this->facebook->getOAuth2Client();
            $longLivedToken = $oauth2Client->getLongLivedAccessToken($accessToken);

            return [
                'access_token' => (string) $longLivedToken,
                'expires_in' => $longLivedToken->getExpiresAt() ?
                    $longLivedToken->getExpiresAt()->getTimestamp() - time() :
                    5184000, // 60 days default
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get long-lived token', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get user's Facebook pages.
     *
     * @param string $accessToken
     * @return array
     * @throws \Facebook\Exceptions\FacebookResponseException
     */
    public function getUserPages($accessToken): array
    {
        try {
            $response = $this->facebook->get(
                '/me/accounts?fields=id,name,access_token,category,tasks,picture{url}',
                $accessToken
            );

            $pages = $response->getGraphEdge();
            $pagesArray = [];

            foreach ($pages as $page) {
                $pagesArray[] = [
                    'id' => $page->getField('id'),
                    'name' => $page->getField('name'),
                    'access_token' => $page->getField('access_token'),
                    'category' => $page->getField('category'),
                    'tasks' => $page->getField('tasks'),
                    'picture_url' => $page->getField('picture')['url'] ?? null,
                ];
            }

            return $pagesArray;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve Facebook pages', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Debug and inspect access token.
     *
     * @param string $accessToken
     * @return array
     * @throws \Exception
     */
    public function inspectToken(string $accessToken): array
    {
        try {
            $response = $this->facebook->get(
                '/debug_token?input_token=' . $accessToken,
                config('services.facebook.app_id') . '|' . config('services.facebook.app_secret')
            );

            return $response->getDecodedBody()['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to inspect token', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
