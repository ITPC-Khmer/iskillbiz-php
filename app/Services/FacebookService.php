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
     *
     * @param string $callbackUrl
     * @param array $permissions
     * @return string
     */
    public function getLoginUrl(string $callbackUrl, array $permissions = ['email',
        'public_profile',
        'pages_show_list',
        'pages_read_engagement',
        'pages_manage_posts',
        'pages_read_user_content',
        'pages_messaging',
        'pages_messaging_subscriptions'], ?string $state = null): string
    {
        $graphVersion = config('services.facebook.graph_version', 'v18.0');
        $graphVersion = str_starts_with($graphVersion, 'v') ? $graphVersion : 'v' . $graphVersion;
        $scopes = implode(',', $permissions);
//        dd([
//            'client_id' => config('services.facebook.app_id'),
//            'redirect_uri' => $callbackUrl,
//            'scope' => $scopes,
//            'response_type' => 'code',
//            'state' => $state,
//        ]);
        $query = http_build_query([
            'client_id' => config('services.facebook.app_id'),
            'redirect_uri' => $callbackUrl,
            'scope' => $scopes,
            'response_type' => 'code',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return sprintf('https://www.facebook.com/%s/dialog/oauth?%s', $graphVersion, $query);
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
