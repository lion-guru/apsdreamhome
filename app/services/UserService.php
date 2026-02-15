<?php

namespace App\Services;

use App\Models\User;
use App\Services\EmailService;

class UserService
{
    private $userModel;
    private $emailService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->emailService = new EmailService();
    }

    /**
     * Register a new user
     * 
     * @param array $userData
     * @return array
     */
    public function registerUser(array $userData)
    {
        // Validate required fields
        $requiredFields = ['username', 'email', 'password', 'mobile'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                throw new \InvalidArgumentException("Missing required field: $field");
            }
        }

        // Check if email already exists
        if ($this->userModel->findByEmail($userData['email'])) {
            throw new \RuntimeException('Email already registered');
        }

        // Create new user
        $user = new User();
        $user->username = $userData['username'];
        $user->email = $userData['email'];
        $user->mobile = $userData['mobile'];
        $user->setPassword($userData['password']);
        $user->role = $userData['role'] ?? 'user';
        $user->status = 'pending'; // Will be activated after email verification

        if ($user->save()) {
            // Send verification email
            $token = bin2hex(random_bytes(32));
            $this->emailService->sendVerificationEmail($user->email, $user->username, $token);

            return [
                'success' => true,
                'user_id' => $user->id,
                'message' => 'Registration successful. Please check your email to verify your account.'
            ];
        }

        throw new \RuntimeException('Failed to register user');
    }

    /**
     * Update user profile
     * 
     * @param int $userId
     * @param array $userData
     * @return bool
     */
    public function updateProfile(int $userId, array $userData)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Update allowed fields
        $updatableFields = ['username', 'mobile', 'address', 'city', 'state', 'country', 'pincode'];

        foreach ($updatableFields as $field) {
            if (isset($userData[$field])) {
                $user->$field = $userData[$field];
            }
        }

        // Handle profile picture upload
        if (!empty($_FILES['profile_picture']['name'])) {
            $uploadResult = $this->uploadProfilePicture($_FILES['profile_picture']);
            if ($uploadResult['success']) {
                $user->profile_picture = $uploadResult['file_path'];
            }
        }

        return $user->save();
    }

    /**
     * Change user password
     * 
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Verify current password
        if (!$user->verifyPassword($currentPassword)) {
            throw new \RuntimeException('Current password is incorrect');
        }

        // Update password
        $user->setPassword($newPassword);

        return $user->save();
    }

    /**
     * Reset password request
     * 
     * @param string $email
     * @return bool
     */
    public function requestPasswordReset(string $email)
    {
        $user = $this->userModel->findByEmail($email);

        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Save token to database (you'll need a password_resets table)
            $db = \App\Core\Database::getInstance();
            $db->query(
                "INSERT INTO password_resets (email, token, created_at) VALUES (:email, :token1, :expires1) 
                 ON DUPLICATE KEY UPDATE token = :token2, created_at = :expires2",
                [
                    'email' => $email,
                    'token1' => $token,
                    'expires1' => $expiresAt,
                    'token2' => $token,
                    'expires2' => $expiresAt
                ]
            );

            // Send reset email
            return $this->emailService->sendPasswordResetEmail($email, $user->username, $token);
        }

        // For security, don't reveal if the email exists or not
        return true;
    }

    /**
     * Reset password with token
     * 
     * @param string $token
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(string $token, string $newPassword)
    {
        $db = \App\Core\Database::getInstance();

        // Find valid token
        $stmt = $db->query(
            "SELECT * FROM password_resets WHERE token = :token AND created_at > NOW() LIMIT 1",
            ['token' => $token]
        );

        $reset = $stmt->fetch();

        if (!$reset) {
            throw new \RuntimeException('Invalid or expired token');
        }

        // Find user by email
        $user = $this->userModel->findByEmail($reset['email']);

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        // Update password
        $user->setPassword($newPassword);
        $result = $user->save();

        if ($result) {
            // Delete used token
            $db->query("DELETE FROM password_resets WHERE token = :token", ['token' => $token]);
        }

        return $result;
    }

    /**
     * Upload profile picture
     * 
     * @param array $file
     * @return array
     */
    private function uploadProfilePicture(array $file)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload error: ' . $file['error']);
        }

        if (!in_array($file['type'], $allowedTypes)) {
            throw new \RuntimeException('Invalid file type. Only JPG, PNG and GIF are allowed.');
        }

        if ($file['size'] > $maxSize) {
            throw new \RuntimeException('File is too large. Maximum size is 2MB.');
        }

        // Create uploads directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'file_path' => '/uploads/profiles/' . $filename
            ];
        }

        throw new \RuntimeException('Failed to upload file');
    }
}
