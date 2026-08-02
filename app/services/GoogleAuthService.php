<?php

namespace App\Services;

use App\Models\User;
use \App\Traits\ServiceTenantTrait;

class GoogleAuthService {
    
    use \App\Traits\ServiceTenantTrait;

    public function getAuthUrl($redirectUri) {
        $clientId = getenv('GOOGLE_CLIENT_ID') ?: ($_ENV['GOOGLE_CLIENT_ID'] ?? '');
        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => bin2hex(random_bytes(16))
        ]);
        return 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
    }

    public function handleCallback($code, $redirectUri) {
        try {
            $clientId = getenv('GOOGLE_CLIENT_ID') ?: ($_ENV['GOOGLE_CLIENT_ID'] ?? '');
            $clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: ($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');

            // Exchange code for token
            $ch = curl_init('https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code'
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                error_log("Google token exchange failed: HTTP $httpCode");
                return false;
            }

            $tokenData = json_decode($response, true);
            if (!isset($tokenData['access_token'])) {
                error_log("Google token exchange: no access_token in response");
                return false;
            }

            // Get user info
            $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tokenData['access_token']]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $userResponse = curl_exec($ch);
            curl_close($ch);

            $userData = json_decode($userResponse, true);
            if (!isset($userData['email'])) {
                error_log("Google user info: no email in response");
                return false;
            }

            // Find existing user
            $userModel = new User();
            $user = $userModel->findByEmail($userData['email']);

            if (!$user) {
                return [
                    'is_new' => true,
                    'name' => $userData['name'] ?? 'User',
                    'email' => $userData['email'],
                    'picture' => $userData['picture'] ?? ''
                ];
            }

            return $user;

        } catch (\Exception $e) {
            error_log('Google Auth Service Error: ' . $e->getMessage());
            return false;
        }
    }
}
