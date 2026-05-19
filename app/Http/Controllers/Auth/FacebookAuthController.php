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
    
    public function redirect()
    {
        $facebookUrl = $this->socialService->getProviderRedirectUrl('facebook');
        if ($facebookUrl) {
            header('Location: ' . $facebookUrl);
        } else {
            $_SESSION['error'] = 'Facebook login is not configured. Please contact admin.';
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
        
        $user = $this->socialService->handleProviderCallback('facebook', $code);
        if ($user && isset($user['email'])) {
            $this->loginOrRegister($user);
        } else {
            $_SESSION['error'] = 'Facebook login failed. Please try again.';
            header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }
    
    private function loginOrRegister($socialUser)
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check if social account exists
            $stmt = $db->prepare("SELECT user_id FROM social_accounts WHERE provider = 'facebook' AND provider_id = ?");
            $stmt->execute([$socialUser['id']]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($existing) {
                $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND status = 'active'");
                $stmt->execute([$existing['user_id']]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = 'customer';
                    header('Location: ' . BASE_URL . '/user/dashboard');
                    exit;
                }
            }
            
            // Check if email already registered
            if (!empty($socialUser['email'])) {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$socialUser['email']]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($user) {
                    // Link social account
                    $stmt = $db->prepare("INSERT INTO social_accounts (user_id, provider, provider_id, email, name, created_at) VALUES (?, 'facebook', ?, ?, ?, NOW())");
                    $stmt->execute([$user['id'], $socialUser['id'], $socialUser['email'], $socialUser['name'] ?? '']);
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = 'customer';
                    header('Location: ' . BASE_URL . '/user/dashboard');
                    exit;
                }
            }
            
            // New user - register
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password, status, created_at) VALUES (?, ?, ?, 'active', NOW())");
            $stmt->execute([$socialUser['name'] ?? 'Facebook User', $socialUser['email'] ?? ('fb_' . $socialUser['id'] . '@facebook.com'), $password]);
            $userId = $db->lastInsertId();
            
            $stmt = $db->prepare("INSERT INTO social_accounts (user_id, provider, provider_id, email, name, created_at) VALUES (?, 'facebook', ?, ?, ?, NOW())");
            $stmt->execute([$userId, $socialUser['id'], $socialUser['email'] ?? '', $socialUser['name'] ?? '']);
            
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $socialUser['name'] ?? 'Facebook User';
            $_SESSION['user_email'] = $socialUser['email'] ?? '';
            $_SESSION['user_role'] = 'customer';
            header('Location: ' . BASE_URL . '/user/dashboard');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }
}
