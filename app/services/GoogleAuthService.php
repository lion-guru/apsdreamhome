<?php

namespace App\Services;

use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;

class GoogleAuthService {
    private $client;
    private $userModel;

    public function __construct() {
        $this->client = new Google_Client();
        $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $this->client->setRedirectUri($_ENV['APP_URL'] . '/auth/google/callback');
        $this->client->addScope('email');
        $this->client->addScope('profile');
        
        $this->userModel = new User();
    }

    public function getAuthUrl() {
        return $this->client->createAuthUrl();
    }

    public function handleCallback($code) {
        try {
            // Check if Google API classes are available
            if (!class_exists('Google_Service_Oauth2')) {
                error_log('Google OAuth2 service not available');
                return false;
            }

            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                throw new \Exception('Invalid token');
            }

            $this->client->setAccessToken($token);

            $googleService = new Google_Service_Oauth2($this->client);
            $googleUser = $googleService->userinfo->get();
            
            // Find or create user
            $user = $this->userModel->findByEmail($googleUser->getEmail());
            
            if (!$user) {
                // Create new user
                $user = new User();
                $user->username = $googleUser->getName();
                $user->email = $googleUser->getEmail();
                $user->google_id = $googleUser->getId();
                $user->status = 'active';
                $user->email_verified_at = date('Y-m-d H:i:s');
                $user->save();
            } elseif (empty($user->google_id)) {
                // Update existing user with Google ID
                $user->google_id = $googleUser->getId();
                $user->save();
            }
            
            return $user;
            
        } catch (\Exception $e) {
            error_log('Google Auth Error: ' . $e->getMessage());
            return false;
        }
    }
}
