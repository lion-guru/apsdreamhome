<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Agent;
use App\Models\Property;
use Exception;

class AgentController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/agent';
    }

    /**
     * Show agent login page
     */
    public function login()
    {
        // If already logged in as agent, redirect to dashboard
        if ($this->get('agent_id')) {
            $this->redirect('/agent/dashboard');
            return;
        }

        $this->render('auth/agent_login', [
            'page_title' => 'Agent Login - APS Dream Home',
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    /**
     * Handle agent authentication
     */
    public function authenticate()
    {
        try {
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $password = $this->sanitizeInput($_POST['password'] ?? '');

            // Validate input
            if (empty($email) || empty($password)) {
                $this->setFlash('error', 'Please fill in all fields');
                $this->redirect('/agent/login');
                return;
            }

            // Validate email format
            if (!$this->validateEmail($email)) {
                $this->setFlash('error', 'Please enter a valid email address');
                $this->redirect('/agent/login');
                return;
            }

            // Authenticate agent
            $agentModel = $this->model('Agent');
            $agent = $agentModel->authenticate($email, $password);

            if ($agent) {
                // Set session
                $this->set('agent_id', $agent['id']);
                $this->set('agent_name', $agent['name']);
                $this->set('agent_email', $agent['email']);
                $this->set('logged_in', true);

                $this->setFlash('success', 'Login successful! Welcome back.');
                $this->redirect('/agent/dashboard');
            } else {
                $this->setFlash('error', 'Invalid email or password');
                $this->redirect('/agent/login');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Login failed. Please try again.');
            $this->redirect('/agent/login');
        }
    }

    /**
     * Show agent dashboard
     */
    public function dashboard()
    {
        // Check if agent is logged in
        if (!$this->get('agent_id')) {
            $this->redirect('/agent/login');
            return;
        }

        // Get agent data
        $agentModel = $this->model('Agent');
        $agent = $agentModel->getById($this->get('agent_id'));

        // Get agent's properties
        $propertyModel = $this->model('Property');
        $properties = $propertyModel->getByAgentId($this->get('agent_id'));

        $this->render('agent/dashboard', [
            'agent' => $agent,
            'properties' => $properties,
            'page_title' => 'Agent Dashboard - APS Dream Home'
        ]);
    }

    /**
     * Show registration page
     */
    public function register()
    {
        $this->render('auth/agent_register', [
            'page_title' => 'Agent Registration - APS Dream Home',
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    /**
     * Handle agent registration
     */
    public function handleRegister()
    {
        // Use the modern ValidatorService for cleaner code
        $validator = new \App\Services\ValidatorService($_POST);
        $validator->validateRequired(['name', 'email', 'phone', 'password', 'confirm_password']);
        $validator->validateEmail('email');
        $validator->validatePhone('phone');
        $validator->validateMinLength('password', 6);
        $validator->validateMatch('password', 'confirm_password');

        if ($validator->hasErrors()) {
            $_SESSION['form_errors'] = $validator->getErrors();
            $this->redirect('/agent/register');
            return;
        }

        try {
            $name = $this->sanitizeInput($_POST['name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $password = $this->sanitizeInput($_POST['password'] ?? '');

            // Check if email already exists
            $agentModel = $this->model('Agent');
            $existingAgent = $agentModel->findByEmail($email);
            if ($existingAgent) {
                $this->setFlash('error', 'This email address is already registered.');
                $this->redirect('/agent/register');
                return;
            }

            // Register agent
            $agentId = $agentModel->register([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->setFlash('success', 'Registration successful! Please wait for admin approval.');
            $this->redirect('/agent/login');
        } catch (Exception $e) {
            $this->setFlash('error', 'Registration failed. Please try again.');
            $this->redirect('/agent/register');
        }
    }

    /**
     * Logout agent
     */
    public function logout()
    {
        session_destroy();
        $this->setFlash('success', 'You have been logged out successfully.');
        $this->redirect('/agent/login');
    }

    /**
     * Show forgot password page
     */
    public function forgotPassword()
    {
        $this->render('auth/agent_forgot_password', [
            'page_title' => 'Forgot Password - Agent',
            'csrf_token' => $this->getCsrfToken()
        ]);
    }

    /**
     * Handle forgot password request
     */
    public function handleForgotPassword()
    {
        try {
            $email = $this->sanitizeInput($_POST['email'] ?? '', 'email');
            if (!$this->validateEmail($email)) {
                $this->setFlash('error', 'Please enter a valid email address.');
                $this->redirect('/agent/forgot-password');
                return;
            }

            $agentModel = $this->model('Agent');
            $agent = $agentModel->findByEmail($email);

            if ($agent) {
                // Generate a secure token
                $token = bin2hex(random_bytes(32));
                $agentModel->storePasswordResetToken($email, $token);

                // Send reset email
                $resetLink = BASE_URL . '/agent/reset-password/' . $token;
                $emailService = new \App\Services\UniversalServiceWrapper();
                $emailService->sendEmail(
                    $email,
                    'Password Reset Request - APS Dream Home',
                    "Hello, <br><br>Please click the following link to reset your password: <a href='{$resetLink}'>{$resetLink}</a><br><br>This link is valid for 1 hour."
                );
            }

            // Always show the same message to prevent email enumeration
            $this->setFlash('success', 'If an account with that email exists, a password reset link has been sent.');
            $this->redirect('/agent/login');
        } catch (Exception $e) {
            $this->setFlash('error', 'An unexpected error occurred. Please try again.');
            $this->redirect('/agent/forgot-password');
        }
    }

    /**
     * Show password reset form
     */
    public function resetPassword($token = null)
    {
        if (empty($token)) {
            $this->setFlash('error', 'Invalid reset token.');
            $this->redirect('/agent/login');
            return;
        }

        $agentModel = $this->model('Agent');
        $record = $agentModel->findResetToken($token);

        if (!$record) {
            $this->setFlash('error', 'Invalid or expired password reset token.');
            $this->redirect('/agent/login');
            return;
        }

        $this->render('auth/agent_reset_password', [
            'page_title' => 'Reset Password - Agent',
            'csrf_token' => $this->getCsrfToken(),
            'token' => $token
        ]);
    }

    /**
     * Handle password reset submission
     */
    public function handleResetPassword()
    {
        $token = $this->sanitizeInput($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $this->setFlash('error', 'All fields are required.');
            $this->redirect('/agent/reset-password/' . $token);
            return;
        }

        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->redirect('/agent/reset-password/' . $token);
            return;
        }

        if (strlen($password) < 6) {
            $this->setFlash('error', 'Password must be at least 6 characters long.');
            $this->redirect('/agent/reset-password/' . $token);
            return;
        }

        $agentModel = $this->model('Agent');
        $record = $agentModel->findResetToken($token);

        if (!$record) {
            $this->setFlash('error', 'Invalid or expired password reset token.');
            $this->redirect('/agent/login');
            return;
        }

        // Update password and delete token
        $agentModel->updatePassword($record['email'], $password);
        $agentModel->deleteResetToken($record['email']);

        $this->setFlash('success', 'Your password has been reset successfully. Please login.');
        $this->redirect('/agent/login');
    }
}
