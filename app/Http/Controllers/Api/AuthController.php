<?php

namespace App\Http\Controllers\Api;

use \Exception;

class AuthController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth', ['except' => ['login']]);
    }

    /**
     * Authenticate user and generate JWT token
     */
    public function login()
    {
        if ($this->request()->getMethod() !== 'POST') {
            return $this->jsonError('Method not allowed. Use POST.', 405);
        }

        try {
            $email = $this->request()->input('email');
            $password = $this->request()->input('password');

            if (empty($email) || empty($password)) {
                return $this->jsonError('Email and password are required', 400);
            }

            $token = $this->auth->login($email, $password);

            if (!$token) {
                return $this->jsonError('Invalid email or password', 401);
            }

            $userPayload = $this->auth->validateToken($token);
            $user = $this->model('User')->find($userPayload['sub']);

            if (!$user) {
                return $this->jsonError('User not found after authentication', 404);
            }

            return $this->jsonSuccess([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ],
                'token' => $token,
                'expires_in' => 86400,
                'token_type' => 'Bearer'
            ], 'Login successful');

        } catch (Exception $e) {
            return $this->jsonError('Authentication failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get current user info
     */
    public function me()
    {
        try {
            $user = $this->auth->user();

            if (!$user) {
                return $this->jsonError('User not found', 404);
            }

            $userModel = $this->model('User');
            // Get roles and permissions
            $roles = $userModel->getRoles($user->id);
            $permissions = $userModel->getPermissions($user->id);

            return $this->jsonSuccess([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status
                ],
                'roles' => $roles,
                'permissions' => $permissions
            ]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Refresh authentication token
     */
    public function refresh()
    {
        try {
            $newToken = $this->auth->refreshToken();

            if (!$newToken) {
                return $this->jsonError('Token refresh failed', 401);
            }

            return $this->jsonSuccess([
                'token' => $newToken,
                'expires_in' => 86400,
                'token_type' => 'Bearer'
            ], 'Token refreshed');

        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Log out current user
     */
    public function logout()
    {
        try {
            $this->auth->logout();
            return $this->jsonSuccess(null, 'Logged out successfully');
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}
