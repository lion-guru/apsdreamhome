<?php
namespace App\Http\Controllers;

class AuthController extends BaseController
{
    public function login()
    {
        $this->json(['success' => false, 'error' => 'Use POST /api/v2/mobile/auth/login instead'], 400);
    }

    public function me()
    {
        $this->json(['success' => false, 'error' => 'Use GET /api/mobile/profile instead'], 400);
    }

    public function refresh()
    {
        $this->json(['success' => false, 'error' => 'Use POST /api/mobile/auth/refresh instead'], 400);
    }

    public function logout()
    {
        $this->json(['success' => false, 'error' => 'Use POST /api/v2/mobile/auth/logout instead'], 400);
    }

    public function forgotPassword()
    {
        include __DIR__ . "/../../views/auth/forgot_password.php";
    }

    public function verifyEmail()
    {
        include __DIR__ . "/../../views/auth/verify_email.php";
        }
}?>