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
                'session_id' => session()->getId(),
            ]);
        }

        // Use SDK's built-in method which handles state/CSRF automatically
        $loginUrl = $helper->getLoginUrl($callbackUrl, $permissions);

        // Force session save to ensure SDK's state parameter is persisted
        session()->save();

        Log::info('Facebook login URL generated', [
            'callback_url' => $callbackUrl,
            'permissions' => $permissions,
            'has_custom_state' => $customState !== null,
            'session_id' => session()->getId(),
        ]);

        return $loginUrl;
    }

    /**
     * Get access token from callback.
     *
     * Attempts multiple strategies:
     * 1. SDK helper (preferred) - handles state validation automatically
     * 2. OAuth2 client direct exchange - fallback if state validation fails
     * 3. Direct HTTP call - last resort fallback with explicit error handling
     *
     * @return \Facebook\Authentication\AccessToken|null
     * @throws \Facebook\Exceptions\FacebookResponseException
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getAccessTokenFromCallback()
    {
        $helper = $this->getRedirectLoginHelper();

        // Strategy 1: Try using SDK's built-in helper (preferred method)
        try {
            $accessToken = $helper->getAccessToken();
            Log::info('Access token obtained via SDK helper', [
                'token_length' => strlen((string) $accessToken),
                'method' => 'sdk_helper',
            ]);
            return $accessToken;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Check if this is a state validation error
            $isStateError = strpos($e->getMessage(), 'state') !== false;
            $hasCode = request()->has('code');

            Log::warning('SDK helper failed', [
                'error_message' => $e->getMessage(),
                'is_state_error' => $isStateError,
                'has_code' => $hasCode,
                'has_url_state' => request()->has('state'),
                'method_attempted' => 'sdk_helper',
            ]);

            // If it's a state error and we have a code, try fallback methods
            if ($isStateError && $hasCode) {
                // Strategy 2: Try OAuth2 client direct exchange
                try {
                    Log::info('Attempting Strategy 2: OAuth2 client direct exchange');
                    $code = request()->input('code');
                    $redirectUri = route('facebook.facebook_login_back');

                    $oauth2Client = $this->facebook->getOAuth2Client();
                    $accessToken = $oauth2Client->getAccessTokenFromCode($code, $redirectUri);

                    Log::info('Access token obtained via OAuth2 client fallback', [
                        'token_length' => strlen((string) $accessToken),
                        'method' => 'oauth2_client',
                        'redirect_uri' => $redirectUri,
                    ]);
                    return $accessToken;
                } catch (\Exception $ex) {
                    Log::warning('OAuth2 client fallback failed', [
                        'error_message' => $ex->getMessage(),
                        'error_code' => $ex->getCode(),
                        'method_attempted' => 'oauth2_client',
                    ]);

                    // Strategy 3: Direct HTTP call as last resort
                    try {
                        Log::info('Attempting Strategy 3: Direct HTTP token exchange');
                        return $this->exchangeCodeForTokenViaHttp(request()->input('code'));
                    } catch (\Exception $httpEx) {
                        Log::error('Direct HTTP token exchange failed - all strategies exhausted', [
                            'error_message' => $httpEx->getMessage(),
                            'error_code' => $httpEx->getCode(),
                            'method_attempted' => 'direct_http',
                            'original_state_error' => $e->getMessage(),
                        ]);
                        throw $e; // Throw original exception
                    }
                }
            }

            // If not a state error or no code available, throw immediately
            throw $e;
        }
    }

    /**
     * Exchange authorization code for access token via direct HTTP call.
     * This is a last-resort fallback that mimics the JavaScript approach suggested.
     *
     * @param string $code Authorization code from Facebook callback
     * @return \Facebook\Authentication\AccessToken
     * @throws \Exception
     */
    private function exchangeCodeForTokenViaHttp(string $code): \Facebook\Authentication\AccessToken
    {
        $appId = config('services.facebook.app_id');
        $appSecret = config('services.facebook.app_secret');
        $redirectUri = route('facebook.facebook_login_back');

        if (!$appId || !$appSecret) {
            throw new \Exception('Facebook App ID or App Secret is not configured');
        }

        Log::debug('Preparing direct HTTP token exchange', [
            'code_length' => strlen($code),
            'redirect_uri' => $redirectUri,
            'app_id_length' => strlen($appId),
        ]);

        try {
            // Build the token exchange URL with query parameters
            $tokenUrl = 'https://graph.facebook.com/v18.0/oauth/access_token';
            $params = [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ];

            // Use Guzzle HTTP client for the request
            $client = new \GuzzleHttp\Client();
            $response = $client->get($tokenUrl, [
                'query' => $params,
                'timeout' => 10,
                'connect_timeout' => 5,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'iskillbiz-facebook-oauth/1.0',
                ],
                'http_errors' => true, // Let exceptions be thrown on 4xx/5xx
            ]);

            $responseBody = json_decode((string) $response->getBody(), true);

            Log::debug('HTTP token exchange response received', [
                'has_access_token' => isset($responseBody['access_token']),
                'has_expires_in' => isset($responseBody['expires_in']),
                'response_keys' => array_keys($responseBody),
            ]);

            if (!isset($responseBody['access_token'])) {
                $errorMsg = $responseBody['error']['message'] ?? 'Unknown error';
                throw new \Exception("Facebook token exchange failed: {$errorMsg}");
            }

            // Convert the response to Facebook SDK's AccessToken object
            $accessToken = new \Facebook\Authentication\AccessToken(
                $responseBody['access_token'],
                $responseBody['expires_in'] ?? null
            );

            Log::info('HTTP direct token exchange successful', [
                'token_length' => strlen((string) $accessToken),
                'expires_in' => $responseBody['expires_in'] ?? null,
            ]);

            return $accessToken;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();
            $responseBody = (string) $e->getResponse()?->getBody();

            Log::error('HTTP token exchange request failed', [
                'status_code' => $statusCode,
                'error_message' => $e->getMessage(),
                'response_body' => $responseBody,
                'code_provided' => !empty($code),
            ]);

            throw new \Exception(
                "Failed to exchange code for token (HTTP {$statusCode}): {$e->getMessage()}"
            );
        } catch (\Exception $e) {
            Log::error('HTTP token exchange error', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);
            throw $e;
        }
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
