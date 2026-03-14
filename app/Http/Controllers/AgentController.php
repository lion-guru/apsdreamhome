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
        try {
            $name = $this->sanitizeInput($_POST['name'] ?? '');
            $email = $this->sanitizeInput($_POST['email'] ?? '');
            $phone = $this->sanitizeInput($_POST['phone'] ?? '');
            $password = $this->sanitizeInput($_POST['password'] ?? '');
            $confirmPassword = $this->sanitizeInput($_POST['confirm_password'] ?? '');

            // Validate input
            $errors = [];

            if (empty($name)) {
                $errors[] = 'Name is required';
            }

            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!$this->validateEmail($email)) {
                $errors[] = 'Please enter a valid email address';
            }

            if (empty($phone)) {
                $errors[] = 'Phone number is required';
            } elseif (!$this->validatePhone($phone)) {
                $errors[] = 'Please enter a valid phone number';
            }

            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }

            if ($password !== $confirmPassword) {
                $errors[] = 'Passwords do not match';
            }

            if (!empty($errors)) {
                $this->setFlash('error', implode(', ', $errors));
                $this->redirect('/agent/register');
                return;
            }

            // Register agent
            $agentModel = $this->model('Agent');
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
}
