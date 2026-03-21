<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;

class AuthController extends BaseController
{
    public function universalLogin()
    {
        $data = [
            'page_title' => 'Universal Login - APS Dream Home',
            'page_description' => 'Login with email, mobile number, or Google',
            'active_page' => 'login'
        ];
        $this->render('auth/universal_login', $data);
    }

    public function login()
    {
        $data = [
            'page_title' => 'Login - APS Dream Home',
            'page_description' => 'Access your account'
        ];
        $this->render('auth/employee_login', $data);
    }

    public function authenticate()
    {
        $this->redirect('/dashboard');
    }

    public function logout()
    {
        $data = [
            'page_title' => 'Logged Out - APS Dream Home',
            'page_description' => 'You have been logged out'
        ];
        $this->render('auth/logout', $data);
    }

    public function register()
    {
        $data = [
            'page_title' => 'Register - APS Dream Home',
            'page_description' => 'Create a new account'
        ];
        $this->render('auth/register', $data);
    }

    public function createAccount()
    {
        $this->redirect('/auth/login');
    }

    public function forgotPassword()
    {
        $data = [
            'page_title' => 'Forgot Password - APS Dream Home',
            'page_description' => 'Recover your account'
        ];
        $this->render('auth/forgot_password', $data);
    }

    public function sendPasswordReset()
    {
        $this->redirect('/auth/reset-password');
    }

    public function resetPassword()
    {
        $data = [
            'page_title' => 'Reset Password - APS Dream Home',
            'page_description' => 'Set a new password'
        ];
        $this->render('auth/reset_password', $data);
    }

    public function updatePassword()
    {
        $this->redirect('/auth/login');
    }

    public function profile()
    {
        $data = [
            'page_title' => 'Profile - APS Dream Home',
            'page_description' => 'Manage your profile'
        ];
        $this->render('auth/profile', $data);
    }

    public function updateProfile()
    {
        $this->redirect('/auth/profile');
    }
}
