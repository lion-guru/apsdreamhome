<?php
namespace App\Http\Controllers\Auth;
use App\Core\Database\Database;
use App\Services\SocialLoginService;

class FacebookAuthController extends \App\Http\Controllers\BaseController
{
    private $socialService;
    
    public function __construct()
    {
        parent::__construct();
        $this->socialService = new SocialLoginService();
    }
    
    public function redirectToProvider()
    {
        try {
            $state = bin2hex(random_bytes(16));
            $_SESSION['facebook_oauth_state'] = $state;
            $facebookUrl = $this->socialService->getAuthUrl('facebook', $state);
            if ($facebookUrl) {
                header('Location: ' . $facebookUrl);
            } else {
                $_SESSION['error'] = 'Facebook login is not configured. Please contact admin.';
                header('Location: ' . BASE_URL . '/login');
            }
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Facebook login configuration error: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }
    
    public function callback()
    {
        // OAuth Login-CSRF guard: state must match the one stored at redirect time
        $expectedState = $_SESSION['facebook_oauth_state'] ?? null;
        unset($_SESSION['facebook_oauth_state']);
        $gotState = $_GET['state'] ?? null;
        if (!$expectedState || !$gotState || !hash_equals((string)$expectedState, (string)$gotState)) {
            $_SESSION['error'] = 'Facebook sign-in could not be verified (state mismatch). Please try again.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $code = $_GET['code'] ?? '';
        if (empty($code)) {
            $_SESSION['error'] = 'Facebook login cancelled or failed.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        try {
            $tokenData = $this->socialService->exchangeCodeForToken('facebook', $code);
            $accessToken = $tokenData['access_token'] ?? '';
            $userData = $this->socialService->getUserInfo('facebook', $accessToken);
            if ($userData && isset($userData['email'])) {
                $user = $this->socialService->authenticateSocialUser('facebook', $userData, $accessToken);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_phone'] = $user['phone'] ?? '';
                    $_SESSION['role'] = $user['role'] ?? 'customer';
                    $_SESSION['logged_in'] = true;
                    header('Location: ' . BASE_URL . '/user/dashboard');
                    exit;
                }
            }
            $_SESSION['error'] = 'Facebook login failed. Could not retrieve user info.';
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Facebook login failed: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
