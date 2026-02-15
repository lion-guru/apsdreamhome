<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Core\Auth;

class AuthController extends BaseController {
    protected $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show login form
     */
    public function showLogin() {
        if ($this->auth->check()) {
            return $this->redirect('/admin/dashboard');
        }
        return $this->render('auth/login', [
            'page_title' => 'Login'
        ]);
    }

    /**
     * Handle login request
     */
    public function login() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->setFlash('error', 'Username and password are required.');
            return $this->redirect('/login');
        }

        $user = $this->userModel->getByUsername($username);

        if ($user && \password_verify($password, $user['password'])) {
            if ($user['status'] !== 'active') {
                $this->setFlash('error', 'Your account is inactive.');
                return $this->redirect('/login');
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_role'] = $user['role_name'] ?? 'user';

            return $this->redirect('/admin/dashboard');
        }

        $this->setFlash('error', 'Invalid username or password.');
        return $this->redirect('/login');
    }

    /**
     * Handle logout
     */
    public function logout() {
        $this->auth->logout();
        $this->setFlash('success', 'You have been logged out.');
        return $this->redirect('/login');
    }

    /**
     * Show registration form
     */
    public function showRegister() {
        if ($this->auth->check()) {
            return $this->redirect('/admin/dashboard');
        }
        return $this->render('auth/register', [
            'page_title' => 'Register'
        ]);
    }

    /**
     * Handle registration request
     */
    public function register() {
        $request = $this->request();
        $data = $request->post();

        if (empty($data['username']) || empty($data['password']) || empty($data['email'])) {
            $this->setFlash('error', 'All fields are required.');
            return $this->redirect('/register');
        }

        if ($this->userModel->getByUsername($data['username'])) {
            $this->setFlash('error', 'Username already exists.');
            return $this->redirect('/register');
        }

        $data['password'] = \password_hash($data['password'], \PASSWORD_DEFAULT);
        $data['status'] = 'active';
        $data['created_at'] = \date('Y-m-d H:i:s');

        $userId = $this->userModel->create($data);

        if ($userId) {
            $this->setFlash('success', 'Registration successful. Please login.');
            return $this->redirect('/login');
        }

        $this->setFlash('error', 'Registration failed. Please try again.');
        return $this->redirect('/register');
    }
}
