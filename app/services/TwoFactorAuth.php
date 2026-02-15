<?php

namespace App\Services;

use App\Core\App;
use \Exception;

class TwoFactorAuth
{
    protected $db;
    protected static $instance = null;

    private function __construct()
    {
        $this->db = App::database();
        $this->ensureTwoFactorTableExists();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generate and store a 2FA code for a user
     */
    public function generateCode(int $userId): string
    {
        $code = \sprintf("%06d", \App\Helpers\SecurityHelper::secureRandomInt(100000, 999999));
        $expiresAt = \date('Y-m-d H:i:s', \strtotime('+10 minutes'));

        // Delete existing codes for this user
        $this->db->execute("DELETE FROM user_two_factor_codes WHERE user_id = ?", [$userId]);

        // Insert new code
        $this->db->execute("INSERT INTO user_two_factor_codes (user_id, code, expires_at) VALUES (?, ?, ?)", [$userId, $code, $expiresAt]);

        return $code;
    }

    /**
     * Verify a 2FA code
     */
    public function verifyCode(int $userId, string $code): bool
    {
        $row = $this->db->fetch("SELECT id FROM user_two_factor_codes WHERE user_id = ? AND code = ? AND expires_at > NOW() AND is_used = 0", [$userId, $code]);

        if ($row) {
            $id = $row['id'];

            // Mark as used
            $this->db->execute("UPDATE user_two_factor_codes SET is_used = 1 WHERE id = ?", [$id]);
            return true;
        }

        return false;
    }

    /**
     * Ensure the 2FA table exists
     */
    protected function ensureTwoFactorTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS user_two_factor_codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            code VARCHAR(10) NOT NULL,
            expires_at DATETIME NOT NULL,
            is_used TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (user_id),
            INDEX (code)
        )";
        $this->db->execute($sql);
    }
}
