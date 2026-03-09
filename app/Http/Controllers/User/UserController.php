<?php

namespace App\Http\Controllers\User;

use App\Services\User\UserService;
use App\Http\Controllers\BaseController;

/**
 * User Controller - APS Dream Home
 * User management and authentication
 * Custom MVC implementation without Laravel dependencies
 */
class UserController extends BaseController
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Display user dashboard
     */
    public function dashboard()
    {
        try {
            $users = $this->userService->getAllUsers();
            $activeUsers = $this->userService->getActiveUsers();
            $statistics = $this->userService->getUserStatistics();
            
            $data = [
                'page_title' => 'User Dashboard - APS Dream Home',
                'users' => $users,
                'active_users' => $activeUsers,
                'statistics' => $statistics,
                'total_users' => count($users),
                'active_count' => count($activeUsers)
            ];
            
            $this->render('user/dashboard', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading user dashboard', $e->getMessage());
        }
    }

    /**
     * Display user list
     */
    public function index()
    {
        try {
            $users = $this->userService->getAllUsers();
            
            $data = [
                'page_title' => 'Users - APS Dream Home',
                'users' => $users,
                'total_count' => count($users)
            ];
            
            $this->render('user/index', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading users', $e->getMessage());
        }
    }

    /**
     * Display create user form
     */
    public function create()
    {
        $data = [
            'page_title' => 'Create User - APS Dream Home',
            'action' => '/users/store',
            'roles' => ['admin', 'user', 'agent', 'associate']
        ];
        
        $this->render('user/create', $data);
    }

    /**
     * Store new user
     */
    public function store()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'password' => $_POST['password'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'role' => $_POST['role'] ?? 'user',
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Validate required fields
                if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                    throw new Exception('Name, email, and password are required');
                }
                
                // Check if email already exists
                if ($this->userService->emailExists($data['email'])) {
                    throw new Exception('Email already exists');
                }
                
                $userId = $this->userService->createUser($data);
                
                if ($userId) {
                    header('Location: /users');
                    exit;
                } else {
                    throw new Exception('Failed to create user');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error creating user', $e->getMessage());
        }
    }

    /**
     * Display edit user form
     */
    public function edit($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                $this->renderError('User not found', 'User with ID ' . $id . ' not found');
                return;
            }
            
            $data = [
                'page_title' => 'Edit User - APS Dream Home',
                'user' => $user,
                'action' => '/users/update/' . $id,
                'roles' => ['admin', 'user', 'agent', 'associate']
            ];
            
            $this->render('user/edit', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading user', $e->getMessage());
        }
    }

    /**
     * Update user
     */
    public function update($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'role' => $_POST['role'] ?? 'user',
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Validate required fields
                if (empty($data['name']) || empty($data['email'])) {
                    throw new Exception('Name and email are required');
                }
                
                $result = $this->userService->updateUser($id, $data);
                
                if ($result) {
                    header('Location: /users');
                    exit;
                } else {
                    throw new Exception('Failed to update user');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error updating user', $e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        try {
            $result = $this->userService->deleteUser($id);
            
            if ($result) {
                header('Location: /users');
                exit;
            } else {
                throw new Exception('Failed to delete user');
            }
        } catch (Exception $e) {
            $this->renderError('Error deleting user', $e->getMessage());
        }
    }

    /**
     * Display user details
     */
    public function show($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            $preferences = $this->userService->getUserPreferences($id);
            
            if (!$user) {
                $this->renderError('User not found', 'User with ID ' . $id . ' not found');
                return;
            }
            
            $data = [
                'page_title' => 'User Details - APS Dream Home',
                'user' => $user,
                'preferences' => $preferences
            ];
            
            $this->render('user/show', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading user details', $e->getMessage());
        }
    }

    /**
     * Display user profile
     */
    public function profile($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            $preferences = $this->userService->getUserPreferences($id);
            
            if (!$user) {
                $this->renderError('User not found', 'User with ID ' . $id . ' not found');
                return;
            }
            
            $data = [
                'page_title' => 'User Profile - APS Dream Home',
                'user' => $user,
                'preferences' => $preferences
            ];
            
            $this->render('user/profile', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading user profile', $e->getMessage());
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'phone' => $_POST['phone'] ?? ''
                ];
                
                // Validate required fields
                if (empty($data['name'])) {
                    throw new Exception('Name is required');
                }
                
                $result = $this->userService->updateUserProfile($id, $data);
                
                if ($result) {
                    header('Location: /users/profile/' . $id);
                    exit;
                } else {
                    throw new Exception('Failed to update user profile');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error updating user profile', $e->getMessage());
        }
    }

    /**
     * Display change password form
     */
    public function changePassword($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                $this->renderError('User not found', 'User with ID ' . $id . ' not found');
                return;
            }
            
            $data = [
                'page_title' => 'Change Password - APS Dream Home',
                'user' => $user,
                'action' => '/users/update-password/' . $id
            ];
            
            $this->render('user/change-password', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading password change form', $e->getMessage());
        }
    }

    /**
     * Update user password
     */
    public function updatePassword($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                // Validate required fields
                if (empty($password)) {
                    throw new Exception('Password is required');
                }
                
                if ($password !== $confirmPassword) {
                    throw new Exception('Password confirmation does not match');
                }
                
                $result = $this->userService->updateUserPassword($id, $password);
                
                if ($result) {
                    header('Location: /users/profile/' . $id);
                    exit;
                } else {
                    throw new Exception('Failed to update password');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error updating password', $e->getMessage());
        }
    }

    /**
     * Update user status
     */
    public function updateStatus($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $status = $_POST['status'] ?? 'active';
                
                $result = $this->userService->updateUserStatus($id, $status);
                
                if ($result) {
                    header('Location: /users');
                    exit;
                } else {
                    throw new Exception('Failed to update user status');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error updating user status', $e->getMessage());
        }
    }

    /**
     * Display users by role
     */
    public function byRole($role)
    {
        try {
            $users = $this->userService->getUsersByRole($role);
            
            $data = [
                'page_title' => ucfirst($role) . ' Users - APS Dream Home',
                'users' => $users,
                'role' => $role,
                'total_count' => count($users)
            ];
            
            $this->render('user/by-role', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading users by role', $e->getMessage());
        }
    }
}
