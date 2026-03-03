<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;

/**
 * AuthController for Employee users
 * Handles authentication for employees
 */
class AuthController extends BaseController
{
    /**
     * Show login form for employees
     * @return void
     */
    public function login()
    {
        $data = [
            'page_title' => 'Employee Login - APS Dream Home',
            'page_description' => 'Login to your employee dashboard to manage operations'
        ];
        return $this->render('auth/login', $data, 'layouts/base');
    }

    /**
     * Process employee login
     * @return void
     */
    public function processLogin()
    {
        // TODO: Implement login processing
        return $this->redirect('/employee/dashboard');
    }

    /**
     * Logout employee
     * @return void
     */
    public function logout()
    {
        // TODO: Implement logout
        return $this->redirect('/');
    }
}
