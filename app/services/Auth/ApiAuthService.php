<?php

namespace App\Services\Auth;

use App\Core\Database;
use App\Core\Security;
use PDO;
use Exception;

class ApiAuthService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getPdo();
    }

    /**
     * Authenticate a user and generate an API token
     */
    public function login($email, $password)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !Security::verifyPassword($password, $user['password'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }

            // Generate token
            $token = Security::generateRandomString(64);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

            $stmt = $this->db->prepare("
                INSERT INTO api_tokens (user_id, token, expires_at, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$user['id'], $token, $expiresAt]);

            try {
                // Fetch full profile info for initial app state
                $stmt = $this->db->prepare("
                    SELECT u.id as userId, u.name, u.email, u.phone, 
                           COALESCE(u.created_at, NOW()) as createdAt, 
                           COALESCE(u.updated_at, NOW()) as updatedAt,
                           COALESCE(mp.current_level, 'Customer') as rank
                    FROM users u
                    LEFT JOIN mlm_profiles mp ON u.id = mp.user_id
                    WHERE u.id = ?
                ");
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            $userData['avatar'] = null;
            $userData['target'] = 0.0;

            return [
                'success' => true,
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                ],
                'expires_at' => $expiresAt
            ];

        } catch (Exception $e) {
            error_log("API Login Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Internal server error'];
        }
    }

    /**
     * Logout and revoke API token
     */
    public function logout($token)
    {
        try {
            try {
                $stmt = $this->db->prepare("DELETE FROM api_tokens WHERE token = ?");
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt->execute([$token]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
