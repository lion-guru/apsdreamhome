<?php

/**
 * Admin Profile Controller
 * Handles user profile management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;

class AdminProfileController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show profile page
     */
    public function index()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get current user from session
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
        $user = [];

        if ($userId) {
            try {
                $user = $this->db->fetch(
                    "SELECT * FROM users WHERE id = ? AND status = 'active'",
                    [$userId]
                );

                if (!$user) {
                    $user = $this->db->fetch(
                        "SELECT * FROM admin_users WHERE id = ? AND status = 'active'",
                        [$userId]
                    );
                }
            } catch (\Exception $e) {
                error_log("Error getting user: " . $e->getMessage());
            }
        }

        $data = [
            'active_page' => 'profile',
            'page_title' => 'My Profile',
            'page_description' => 'Manage your profile information',
            'user' => $user ?: []
        ];

        return $this->render('admin/profile', $data);
    }

    /**
     * Update profile
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/profile');
            exit;
        }

        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error'] = 'Session expired. Please login again.';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (empty($name) || empty($email)) {
            $_SESSION['error'] = 'Name and email are required.';
            header('Location: ' . BASE_URL . '/admin/profile');
            exit;
        }

        try {
            $db = Database::getInstance();

            $updateData = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, updated_at = NOW() WHERE id = ?";
            $db->update($sql, [$name, $email, $phone, $address, $userId]);

            $_SESSION['success'] = 'Profile updated successfully.';
            header('Location: ' . BASE_URL . '/admin/profile');
            exit;
        } catch (\Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to update profile. Please try again.';
            header('Location: ' . BASE_URL . '/admin/profile');
            exit;
        }
    }

    /**
     * Show security settings
     */
    public function security()
    {
        $this->data['active_page'] = 'profile';
        $this->data['page_title'] = 'Security Settings';
        $this->data['page_description'] = 'Manage your account security';

        $this->render('admin/profile_security', $this->data);
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/profile/security');
            exit;
        }

        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error'] = 'Session expired. Please login again.';
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'All password fields are required.';
            header('Location: ' . BASE_URL . '/admin/profile/security');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match.';
            header('Location: ' . BASE_URL . '/admin/profile/security');
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters.';
            header('Location: ' . BASE_URL . '/admin/profile/security');
            exit;
        }

        try {
            $db = Database::getInstance();

            $user = $db->fetch("SELECT password FROM users WHERE id = ?", [$userId]);

            if (!$user) {
                $user = $db->fetch("SELECT password_hash FROM admin_users WHERE id = ?", [$userId]);
                $passwordField = 'password_hash';
            } else {
                $passwordField = 'password';
            }

            if ($user && !empty($user[$passwordField]) && !password_verify($currentPassword, $user[$passwordField])) {
                $_SESSION['error'] = 'Current password is incorrect.';
                header('Location: ' . BASE_URL . '/admin/profile/security');
                exit;
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            if ($passwordField === 'password_hash') {
                $db->update("UPDATE admin_users SET password_hash = ?, updated_at = NOW() WHERE id = ?", [$hashedPassword, $userId]);
            } else {
                $db->update("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?", [$hashedPassword, $userId]);
            }

            $_SESSION['success'] = 'Password changed successfully.';
            header('Location: ' . BASE_URL . '/admin/profile/security');
            exit;
        } catch (\Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $_SESSION['error'] = 'Failed to change password. Please try again.';
            header('Location: ' . BASE_URL . '/admin/profile/security');
            exit;
        }
    }
}
