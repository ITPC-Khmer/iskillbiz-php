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
     * Attempts multiple strategies (Direct HTTP is now primary):
     * 1. Direct HTTP call (preferred) - direct Graph API token exchange
     * 2. OAuth2 client direct exchange - fallback using SDK OAuth2 client
     * 3. SDK helper - last resort for backward compatibility
     *
     * @return \Facebook\Authentication\AccessToken|null
     * @throws \Facebook\Exceptions\FacebookResponseException
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function getAccessTokenFromCallback()
    {
        $code = request()->input('code');

        if (!$code) {
            Log::warning('No authorization code in callback', [
                'has_error' => request()->has('error'),
                'error' => request()->input('error'),
                'error_description' => request()->input('error_description'),
            ]);
            throw new \Facebook\Exceptions\FacebookSDKException('No authorization code provided in callback');
        }

        // Strategy 1: Direct HTTP call to Graph API (preferred method - most reliable)
        try {
            Log::info('Attempting Strategy 1: Direct HTTP token exchange via Graph API');
            $accessToken = $this->exchangeCodeForTokenViaHttp($code);

            Log::info('Access token obtained via direct HTTP', [
                'token_length' => strlen((string) $accessToken),
                'method' => 'direct_http',
                'code_length' => strlen($code),
            ]);

            return $accessToken;
        } catch (\Exception $e) {
            Log::warning('Direct HTTP token exchange failed, trying fallback methods', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'method_attempted' => 'direct_http',
            ]);

            // Strategy 2: Try OAuth2 client direct exchange
            try {
                Log::info('Attempting Strategy 2: OAuth2 client direct exchange');
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
                Log::warning('OAuth2 client fallback failed, trying SDK helper', [
                    'error_message' => $ex->getMessage(),
                    'error_code' => $ex->getCode(),
                    'method_attempted' => 'oauth2_client',
                ]);

                // Strategy 3: Try SDK helper as last resort
                try {
                    Log::info('Attempting Strategy 3: SDK helper (last resort)');
                    $helper = $this->getRedirectLoginHelper();
                    $accessToken = $helper->getAccessToken();

                    Log::info('Access token obtained via SDK helper', [
                        'token_length' => strlen((string) $accessToken),
                        'method' => 'sdk_helper',
                    ]);
                    return $accessToken;
                } catch (\Facebook\Exceptions\FacebookSDKException $sdkEx) {
                    Log::error('All token exchange strategies failed', [
                        'direct_http_error' => $e->getMessage(),
                        'oauth2_client_error' => $ex->getMessage(),
                        'sdk_helper_error' => $sdkEx->getMessage(),
                        'method_attempted' => 'sdk_helper',
                    ]);

                    // Throw the most relevant error (from direct HTTP attempt)
                    throw new \Facebook\Exceptions\FacebookSDKException(
                        'Failed to obtain access token from callback. Direct HTTP: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            }
        }
    }

    /**
     * Exchange authorization code for access token via direct HTTP call.
     * This method directly calls Facebook Graph API similar to the JavaScript approach:
     * GET https://graph.facebook.com/v18.0/oauth/access_token
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

        // Validate configuration
        if (!$appId || !$appSecret) {
            throw new \Exception('Facebook App ID or App Secret is not configured');
        }

        if (empty($code)) {
            throw new \Exception('Authorization code is empty');
        }

        Log::info('Starting direct HTTP token exchange via Graph API', [
            'code_length' => strlen($code),
            'redirect_uri' => $redirectUri,
            'app_id' => substr($appId, 0, 5) . '...',
            'graph_version' => 'v18.0',
        ]);

        try {
            // Exchange code for access token using Facebook Graph API
            // This matches the JavaScript example provided by the user
            $tokenUrl = 'https://graph.facebook.com/v18.0/oauth/access_token';
            $params = [
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ];

            // Use Guzzle HTTP client for the request
            $client = new \GuzzleHttp\Client([
                'timeout' => 15,
                'connect_timeout' => 10,
            ]);

            Log::debug('Sending token exchange request to Facebook Graph API', [
                'url' => $tokenUrl,
                'params_keys' => array_keys($params),
            ]);

            $response = $client->get($tokenUrl, [
                'query' => $params,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'iskillbiz-php-oauth/2.0',
                ],
                'http_errors' => true, // Throw exceptions on 4xx/5xx errors
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode((string) $response->getBody(), true);

            if ($statusCode !== 200) {
                throw new \Exception("Unexpected HTTP status code: {$statusCode}");
            }

            Log::debug('Graph API response received', [
                'status_code' => $statusCode,
                'has_access_token' => isset($responseBody['access_token']),
                'has_expires_in' => isset($responseBody['expires_in']),
                'has_token_type' => isset($responseBody['token_type']),
                'response_keys' => array_keys($responseBody ?? []),
            ]);

            // Validate response structure
            if (!isset($responseBody['access_token'])) {
                $errorMsg = $responseBody['error']['message'] ?? 'No access token in response';
                $errorCode = $responseBody['error']['code'] ?? 'unknown';
                throw new \Exception("Facebook Graph API error [{$errorCode}]: {$errorMsg}");
            }

            // Extract token data (matching JavaScript example structure)
            $accessTokenString = $responseBody['access_token'];
            $expiresIn = $responseBody['expires_in'] ?? null;

            // Convert the response to Facebook SDK's AccessToken object
            $accessToken = new \Facebook\Authentication\AccessToken(
                $accessTokenString,
                $expiresIn
            );

            Log::info('Direct HTTP token exchange successful', [
                'token_length' => strlen($accessTokenString),
                'expires_in' => $expiresIn,
                'expires_in_days' => $expiresIn ? round($expiresIn / 86400, 1) : null,
                'token_type' => $responseBody['token_type'] ?? 'bearer',
            ]);

            return $accessToken;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $statusCode = $e->getResponse()?->getStatusCode();
            $responseBody = (string) $e->getResponse()?->getBody();
            $responseData = json_decode($responseBody, true);

            $errorMessage = $responseData['error']['message'] ?? $e->getMessage();
            $errorCode = $responseData['error']['code'] ?? 'unknown';
            $errorType = $responseData['error']['type'] ?? 'unknown';

            Log::error('HTTP token exchange request failed', [
                'status_code' => $statusCode,
                'error_code' => $errorCode,
                'error_type' => $errorType,
                'error_message' => $errorMessage,
                'response_body' => $responseBody,
                'code_provided' => !empty($code),
            ]);

            throw new \Exception(
                "Facebook Graph API token exchange failed [{$errorCode}]: {$errorMessage} (HTTP {$statusCode})"
            );
        } catch (\Exception $e) {
            // Don't re-wrap exceptions that are already wrapped
            if (strpos($e->getMessage(), 'Facebook Graph API') !== false) {
                throw $e;
            }

            Log::error('HTTP token exchange unexpected error', [
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
            ]);

            throw new \Exception("Token exchange failed: {$e->getMessage()}", 0, $e);
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
     * @throws \Exception
     */
    public function getUserData($accessToken, string $fields = 'id,name,email,picture'): array
    {
        try {
            // Direct HTTP call
            $url = "https://graph.facebook.com/v18.0/me";
            $params = [
                'fields' => $fields,
                'access_token' => (string)$accessToken,
                'appsecret_proof' => hash_hmac('sha256', (string)$accessToken, config('services.facebook.app_secret')),
            ];

            $client = new \GuzzleHttp\Client([
                'timeout' => 15,
                'connect_timeout' => 10,
            ]);

            $response = $client->get($url, ['query' => $params]);
            $user = json_decode((string)$response->getBody(), true);

             if (isset($user['error'])) {
                 throw new \Exception($user['error']['message'] ?? 'Unknown Facebook error');
            }

            return [
                'id' => $user['id'],
                'name' => $user['name'] ?? null,
                'email' => $user['email'] ?? null,
                'picture_url' => $user['picture']['data']['url'] ?? null,
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
            // Direct HTTP call to avoid SDK issues with PHP 8.2+
            $url = 'https://graph.facebook.com/v18.0/oauth/access_token';
            $params = [
                'grant_type' => 'fb_exchange_token',
                'client_id' => config('services.facebook.app_id'),
                'client_secret' => config('services.facebook.app_secret'),
                'fb_exchange_token' => $accessToken,
            ];

            $client = new \GuzzleHttp\Client([
                'timeout' => 15,
                'connect_timeout' => 10,
            ]);

            $response = $client->get($url, ['query' => $params]);
            $data = json_decode((string)$response->getBody(), true);

            if (isset($data['error'])) {
                 throw new \Exception($data['error']['message'] ?? 'Unknown Facebook error');
            }

            return [
                'access_token' => $data['access_token'],
                'expires_in' => $data['expires_in'] ?? 5184000,
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
     * @throws \Exception
     */
    public function getUserPages($accessToken): array
    {
        try {
            $url = "https://graph.facebook.com/v18.0/me/accounts";
            $params = [
                'fields' => 'id,name,access_token,category,tasks,picture{url}',
                'access_token' => (string)$accessToken,
                'limit' => 100,
                'appsecret_proof' => hash_hmac('sha256', (string)$accessToken, config('services.facebook.app_secret')),
            ];

            $client = new \GuzzleHttp\Client([
                'timeout' => 15,
                'connect_timeout' => 10,
            ]);

            $response = $client->get($url, ['query' => $params]);
            $data = json_decode((string)$response->getBody(), true);

            if (isset($data['error'])) {
                 throw new \Exception($data['error']['message'] ?? 'Unknown Facebook error');
            }

            $pages = $data['data'] ?? [];
            $pagesArray = [];

            foreach ($pages as $page) {
                $pagesArray[] = [
                    'id' => $page['id'] ?? null,
                    'name' => $page['name'] ?? null,
                    'access_token' => $page['access_token'] ?? null,
                    'category' => $page['category'] ?? null,
                    'tasks' => $page['tasks'] ?? [],
                    'picture_url' => $page['picture']['data']['url'] ?? null,
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
            $url = 'https://graph.facebook.com/v18.0/debug_token';
            $params = [
                'input_token' => $accessToken,
                'access_token' => config('services.facebook.app_id') . '|' . config('services.facebook.app_secret'),
            ];

            $client = new \GuzzleHttp\Client([
                'timeout' => 15,
                'connect_timeout' => 10,
            ]);

            $response = $client->get($url, ['query' => $params]);
            $data = json_decode((string)$response->getBody(), true);

            if (isset($data['error'])) {
                 throw new \Exception($data['error']['message'] ?? 'Unknown Facebook error');
            }

            return $data['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to inspect token', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
