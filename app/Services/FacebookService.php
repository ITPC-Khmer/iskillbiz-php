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
    public function getLoginUrl(string $callbackUrl, array $permissions = ['email', 'public_profile']): string
    {
        $helper = $this->getRedirectLoginHelper();
        return $helper->getLoginUrl($callbackUrl, $permissions);
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
}
