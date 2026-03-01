<?php

namespace App\Services;

use App\Models\User;
use App\Core\Database;

/**
 * User Service
 * Handles user-related operations
 */
class UserService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Register a new user
     * 
     * @param array $userData
     * @return array
     */
    public function registerUser(array $userData): array
    {
        try {
            // Check if email already exists
            $existingUser = $this->userModel->findByEmail($userData['email']);
            if ($existingUser) {
                return [
                    'success' => false,
                    'message' => 'Email already registered'
                ];
            }

            // Create new user
            $userId = $this->userModel->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
                'role' => 'user',
                'status' => 'active'
            ]);

            return [
                'success' => true,
                'message' => 'User registered successfully',
                'user_id' => $userId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update user profile
     * 
     * @param int $userId
     * @param array $profileData
     * @return array
     */
    public function updateProfile(int $userId, array $profileData): array
    {
        try {
            $allowedFields = ['name', 'phone', 'address', 'bio'];
            $updateData = [];
            
            foreach ($allowedFields as $field) {
                if (isset($profileData[$field])) {
                    $updateData[$field] = $profileData[$field];
                }
            }

            $result = $this->userModel->update($userId, $updateData);
            
            return [
                'success' => $result,
                'message' => $result ? 'Profile updated successfully' : 'Profile update failed'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Profile update failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Request password reset
     * 
     * @param string $email
     * @return array
     */
    public function requestPasswordReset(string $email): array
    {
        try {
            $user = $this->userModel->findByEmail($email);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email not found'
                ];
            }

            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expiry]);

            return [
                'success' => true,
                'message' => 'Password reset link sent to your email',
                'token' => $token
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Password reset request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Reset user password
     * 
     * @param string $token
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(string $token, string $newPassword)
    {
        $db = Database::getInstance();

        // Find valid token
        $stmt = $db->prepare(
            "SELECT * FROM password_reset_tokens WHERE token = ? AND expires_at > NOW()"
        );
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

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
            $stmt = $db->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
            $stmt->execute([$token]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Upload user profile picture
     */
    public function uploadProfilePicture(int $userId, array $file): array
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
