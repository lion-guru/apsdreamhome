<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;

/**
 * AuthController for Associate users
 * Handles authentication for associates
 */
class AuthController extends BaseController
{
    /**
     * Show login form for associates
     * @return void
     */
    public function login()
    {
        $data = [
            'page_title' => 'Associate Login - APS Dream Home',
            'page_description' => 'Login to your associate dashboard to manage your properties and referrals'
        ];
        return $this->render('auth/login', $data, 'layouts/base');
    }

    /**
     * Show registration form for associates
     * @return void
     */
    public function register()
    {
        $data = [
            'page_title' => 'Associate Registration - APS Dream Home',
            'page_description' => 'Join APS Dream Home as an associate and start earning with real estate'
        ];
        return $this->render('auth/register', $data, 'layouts/base');
    }

    /**
     * Process associate login
     * @return void
     */
    public function processLogin()
    {
        // TODO: Implement login processing
        return $this->redirect('/associate/dashboard');
    }

    /**
     * Process associate registration
     * @return void
     */
    public function processRegister()
    {
        // TODO: Implement registration processing
        return $this->redirect('/associate/dashboard');
    }

    /**
     * Logout associate
     * @return void
     */
    public function logout()
    {
        // TODO: Implement logout
        return $this->redirect('/');
    }
}
