<?php
namespace App\Http\Controllers;

class AuthController extends BaseController
{
    public function forgotPassword()
    {
        include __DIR__ . "/../../views/auth/forgot_password.php";
    }

    public function verifyEmail()
    {
        include __DIR__ . "/../../views/auth/verify_email.php";
    }
}
?>