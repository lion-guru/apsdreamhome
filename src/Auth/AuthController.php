<?php
namespace Auth;

use Models\User;
use Database\Database;

class AuthController {
    private $user;
    private $db;

    public function __construct() {
        $this->user = new User();
        $this->db = Database::getInstance();
    }

    public function login($email, $password) {
        $email = sanitize_input($email);
        
        if ($this->user->validateLogin($email, $password)) {
            $userData = $this->user->findByEmail($email);
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['user_role'] = $userData['role'];
            $_SESSION['user_email'] = $userData['email'];
            $_SESSION['user_name'] = $userData['name'];
            
            return true;
        }
        return false;
    }

    public function register($data) {
        // Validate required fields
        $required = ['name', 'email', 'password', 'phone'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }

        // Sanitize inputs
        $data['name'] = sanitize_input($data['name']);
        $data['email'] = sanitize_input($data['email']);
        $data['phone'] = sanitize_input($data['phone']);

        // Check if email already exists
        if ($this->user->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default role if not provided
        $data['role'] = $data['role'] ?? 'user';

        // Create user
        try {
            $this->user->create($data);
            return ['success' => true, 'message' => 'Registration successful'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    public function resetPassword($email) {
        $user = $this->user->findByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Email not found'];
        }

        // Generate reset token
        $token = generate_random_string(32);
        $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

        // Store reset token in database
        $this->user->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expiry' => $expiry
        ]);

        // Send reset email
        $resetLink = APP_URL . '/reset-password.php?token=' . $token;
        $to = $user['email'];
        $subject = APP_NAME . ' - Password Reset';
        $message = "Click the following link to reset your password: {$resetLink}";

        if (mail($to, $subject, $message)) {
            return ['success' => true, 'message' => 'Reset instructions sent to your email'];
        }

        return ['success' => false, 'message' => 'Failed to send reset instructions'];
    }

    public function validateResetToken($token) {
        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()";
        return $this->user->db->fetch($sql, [$token]);
    }

    public function updatePassword($token, $newPassword) {
        $user = $this->validateResetToken($token);
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired token'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password and clear reset token
        $this->user->update($user['id'], [
            'password' => $hashedPassword,
            'reset_token' => null,
            'reset_token_expiry' => null
        ]);

        return ['success' => true, 'message' => 'Password updated successfully'];
    }

    public function logout() {
        session_destroy();
        return true;
    }
}