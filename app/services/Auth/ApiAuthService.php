<?php

namespace App\Services\Auth;

use App\Core\Database;
use Security;
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

            // Fetch full profile info for initial app state
            $stmt = $this->db->prepare("
                SELECT u.id as user_id, u.name, u.email, u.phone, u.created_at, u.updated_at,
                       mp.current_level as rank,
                       (SELECT target_amount FROM mlm_rank_rates WHERE rank = mp.current_level LIMIT 1) as target
                FROM users u
                LEFT JOIN mlm_profiles mp ON u.id = mp.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$user['id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            // Ensure numeric types
            $userData['target'] = (double)($userData['target'] ?? 0);
            $userData['avatar'] = null; // Placeholder

            return [
                'success' => true,
                'token' => $token,
                'data' => $userData,
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
            $stmt = $this->db->prepare("DELETE FROM api_tokens WHERE token = ?");
            $stmt->execute([$token]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
