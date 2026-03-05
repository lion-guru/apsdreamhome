<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Exception;

class UserController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // AdminController handles auth check
    }

    /**
     * List all users
     */
    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();

        return $this->render('admin/users/index', [
            'users' => $users,
            'page_title' => $this->mlSupport->translate('User Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        return $this->render('admin/users/create', [
            'page_title' => $this->mlSupport->translate('Add New User') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Store new user
     */
    public function store()
    {
        try {
            if ($this->request->method() !== 'POST') {
                $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
                $this->redirect('/admin/users/create');
                return;
            }

            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
                $this->redirect('/admin/users/create');
                return;
            }

            $data = $this->request->post();

            // Basic validation
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                $this->setFlash('error', $this->mlSupport->translate("Username, Email and Password are required."));
                $this->redirect('/admin/users/create');
                return;
            }

            // Check if email or username already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
            $stmt->execute([
                ':email' => $data['email'],
                ':username' => $data['username']
            ]);
            if ($stmt->fetch()) {
                $this->setFlash('error', $this->mlSupport->translate("Email or Username already exists."));
                $this->redirect('/admin/users/create');
                return;
            }

            // Default role and status
            $role = $data['role'] ?? 'customer';
            $status = $data['status'] ?? 'active';
            $mobile = $data['mobile'] ?? '';

            // Insert user
            $sql = "INSERT INTO users (username, email, password, mobile, role, status, created_at, updated_at) 
                    VALUES (:username, :email, :password, :mobile, :role, :status, NOW(), NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':mobile' => $mobile,
                ':role' => $role,
                ':status' => $status
            ]);

            $this->setFlash('success', $this->mlSupport->translate("User created successfully!"));
            $this->redirect('/admin/users');
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate("Error creating user: ") . $e->getMessage());
            $this->redirect('/admin/users/create');
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $id = intval($id);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->setFlash('error', $this->mlSupport->translate("User not found."));
            $this->redirect('/admin/users');
            return;
        }

        return $this->render('admin/users/edit', [
            'user' => $user,
            'page_title' => $this->mlSupport->translate('Edit User') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        try {
            $id = intval($id);
            if ($this->request->method() !== 'POST') {
                $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
                $this->redirect("/admin/users/edit/$id");
                return;
            }

            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
                $this->redirect("/admin/users/edit/$id");
                return;
            }

            $data = $this->request->post();

            // Basic validation
            if (empty($data['username']) || empty($data['email'])) {
                $this->setFlash('error', $this->mlSupport->translate("Username and Email are required."));
                $this->redirect("/admin/users/edit/$id");
                return;
            }

            // Check if email or username already exists (excluding current user)
            $stmt = $this->db->prepare("SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :id");
            $stmt->execute([
                ':email' => $data['email'],
                ':username' => $data['username'],
                ':id' => $id
            ]);
            if ($stmt->fetch()) {
                $this->setFlash('error', $this->mlSupport->translate("Email or Username already exists."));
                $this->redirect("/admin/users/edit/$id");
                return;
            }

            // Update user
            $sql = "UPDATE users SET username = :username, email = :email, mobile = :mobile, role = :role, status = :status, updated_at = NOW() WHERE id = :id";
            $params = [
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':mobile' => $data['mobile'] ?? '',
                ':role' => $data['role'] ?? 'customer',
                ':status' => $data['status'] ?? 'active',
                ':id' => $id
            ];

            // Update password if provided
            if (!empty($data['password'])) {
                $sql = "UPDATE users SET username = :username, email = :email, password = :password, mobile = :mobile, role = :role, status = :status, updated_at = NOW() WHERE id = :id";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->setFlash('success', $this->mlSupport->translate("User updated successfully!"));
            $this->redirect('/admin/users');
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate("Error updating user: ") . $e->getMessage());
            $this->redirect("/admin/users/edit/$id");
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            $id = intval($id);

            if ($this->request->method() !== 'POST') {
                $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
                $this->redirect('/admin/users');
                return;
            }

            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
                $this->redirect('/admin/users');
                return;
            }

            // Prevent deleting self
            if ($id == $this->session->get('user_id')) {
                $this->setFlash('error', $this->mlSupport->translate("You cannot delete yourself."));
                $this->redirect('/admin/users');
                return;
            }

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $this->setFlash('success', $this->mlSupport->translate("User deleted successfully!"));
            $this->redirect('/admin/users');
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate("Error deleting user: ") . $e->getMessage());
            $this->redirect('/admin/users');
        }
    }

    public function show()
    {
        // TODO: Implement show functionality
        return $this->view('show');
    }

    public function dashboard()
    {
        $this->requireLogin();

        $user = $this->auth->user();

        // Get user statistics
        $stats = $this->userService->getUserStats($_SESSION['user_id']);

        // Get recent activities
        $recentActivities = $this->userService->getRecentActivities($_SESSION['user_id']);

        $this->view('users/dashboard', [
            'title' => 'My Dashboard',
            'user' => $user,
            'stats' => $stats,
            'recent_activities' => $recentActivities
        ]);
    }

    public function profile()
    {
        $this->requireLogin();

        try {
            $data = [
                'username' => Security::sanitize($_POST['username']) ?? '',
                'mobile' => Security::sanitize($_POST['mobile']) ?? '',
                'address' => Security::sanitize($_POST['address']) ?? '',
                'city' => Security::sanitize($_POST['city']) ?? '',
                'state' => Security::sanitize($_POST['state']) ?? '',
                'country' => Security::sanitize($_POST['country']) ?? '',
                'pincode' => Security::sanitize($_POST['pincode']) ?? ''
            ];

            $result = $this->userService->updateProfile($_SESSION['user_id'], $data);

            if ($result) {
                $this->setFlash('success', 'Profile updated successfully!');
            } else {
                $this->setFlash('error', 'Failed to update profile');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $_SESSION['form_data'] = $_POST;
        }

        $this->redirect('/profile');
    }

    public function changePassword()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $currentPassword = Security::sanitize($_POST['current_password']) ?? '';
                $newPassword = Security::sanitize($_POST['new_password']) ?? '';
                $confirmPassword = Security::sanitize($_POST['confirm_password']) ?? '';

                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    throw new \Exception('All fields are required');
                }

                if ($newPassword !== $confirmPassword) {
                    throw new \Exception('New password and confirm password do not match');
                }

                $result = $this->userService->changePassword(
                    $_SESSION['user_id'],
                    $currentPassword,
                    $newPassword
                );

                if ($result) {
                    $this->setFlash('success', 'Password changed successfully!');
                    $this->redirect('/profile');
                    return;
                }

                throw new \Exception('Failed to change password');
            } catch (\Exception $e) {
                $this->setFlash('error', $e->getMessage());
            }
        }

        $this->view('users/change_password', [
            'title' => 'Change Password'
        ]);
    }

    public function forgotPassword()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/');
            return;
        }

        $this->view('auth/forgot_password', [
            'title' => 'Forgot Password'
        ]);
    }

    public function sendPasswordReset()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = Security::sanitize($_POST['email']) ?? '';

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->setFlash('error', 'Please provide a valid email address');
                $this->redirect('/forgot-password');
                return;
            }

            try {
                $result = $this->userService->requestPasswordReset($email);

                if ($result) {
                    $this->setFlash('success', 'If an account exists with this email, a password reset link has been sent.');
                    $this->redirect('/login');
                    return;
                }

                throw new \Exception('Failed to send password reset email');
            } catch (\Exception $e) {
                $this->setFlash('error', $e->getMessage());
                $this->redirect('/forgot-password');
            }
        }

        $this->redirect('/forgot-password');
    }

    public function resetPasswordForm($token)
    {
        if (empty($token)) {
            $this->setFlash('error', 'Invalid reset token');
            $this->redirect('/login');
            return;
        }

        $this->view('auth/reset_password', [
            'title' => 'Reset Password',
            'token' => $token
        ]);
    }

    public function googleLogin()
    {
        $authUrl = $this->googleAuthService->getAuthUrl();
        header('Location: ' . $authUrl);
        exit;
    }

    public function googleCallback()
    {
        if (isset($_GET['code'])) {
            try {
                $user = $this->googleAuthService->handleCallback($_GET['code']);

                if ($user) {
                    // Log the user in
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['user_email'] = $user->email;
                    $_SESSION['user_role'] = $user->role;

                    $this->setFlash('success', 'Logged in successfully with Google!');
                    $this->redirect('/dashboard');
                    return;
                }

                throw new \Exception('Failed to authenticate with Google');
            } catch (\Exception $e) {
                $this->setFlash('error', $e->getMessage());
            }
        } else {
            $this->setFlash('error', 'Invalid request');
        }

        $this->redirect('/login');
    }
}
