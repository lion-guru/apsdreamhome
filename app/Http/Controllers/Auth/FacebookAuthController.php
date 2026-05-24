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
            $facebookUrl = $this->socialService->getAuthUrl('facebook');
            if ($facebookUrl) {
                header('Location: ' . $facebookUrl);
            } else {
                $_SESSION['error'] = 'Facebook login is not configured. Please contact admin.';
                header('Location: ' . BASE_URL . '/login');
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Facebook login configuration error: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }
    
    public function callback()
    {
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
                    $_SESSION['user_role'] = $user['role'] ?? 'customer';
                    header('Location: ' . BASE_URL . '/user/dashboard');
                    exit;
                }
            }
            $_SESSION['error'] = 'Facebook login failed. Could not retrieve user info.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Facebook login failed: ' . $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}
