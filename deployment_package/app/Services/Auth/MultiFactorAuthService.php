<?php
namespace App\Services\Auth;

use App\Services\AuthService;
use PragmaRX\Google2FA\Google2FA;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class MultiFactorAuthService
{
    private $google2fa;
    private $authService;
    
    public function __construct()
    {
        $this->google2fa = new Google2FA();
        $this->authService = new AuthService();
    }
    
    /**
     * Generate secret key for 2FA
     */
    public function generateSecretKey($userId)
    {
        $user = $this->authService->getUserById($userId);
        if (!$user) {
            return false;
        }
        
        $secret = $this->google2fa->generateSecretKey();
        
        // Store secret in database
        $this->storeUserSecret($userId, $secret);
        
        return $secret;
    }
    
    /**
     * Generate QR code for 2FA setup
     */
    public function generateQrCode($userId, $secret)
    {
        $user = $this->authService->getUserById($userId);
        if (!$user) {
            return false;
        }
        
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
        
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrCodeUrl)
            ->size(200)
            ->margin(0)
            ->build();
        
        return $result->getDataUri();
    }
    
    /**
     * Verify 2FA token
     */
    public function verifyToken($userId, $token)
    {
        $secret = $this->getUserSecret($userId);
        if (!$secret) {
            return false;
        }
        
        return $this->google2fa->verifyKey($secret, $token);
    }
    
    /**
     * Enable 2FA for user
     */
    public function enableTwoFactor($userId, $token)
    {
        if (!$this->verifyToken($userId, $token)) {
            return false;
        }
        
        $this->updateUserTwoFactorStatus($userId, true);
        return true;
    }
    
    /**
     * Disable 2FA for user
     */
    public function disableTwoFactor($userId, $password)
    {
        $user = $this->authService->getUserById($userId);
        if (!$user || !password_verify($password, $user->password)) {
            return false;
        }
        
        $this->updateUserTwoFactorStatus($userId, false);
        $this->removeUserSecret($userId);
        return true;
    }
    
    /**
     * Generate backup codes
     */
    public function generateBackupCodes($userId)
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        
        $this->storeBackupCodes($userId, $codes);
        return $codes;
    }
    
    /**
     * Verify backup code
     */
    public function verifyBackupCode($userId, $code)
    {
        $storedCodes = $this->getBackupCodes($userId);
        
        foreach ($storedCodes as $index => $storedCode) {
            if (hash_equals($storedCode, $code)) {
                // Remove used code
                $this->removeBackupCode($userId, $index);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Store user secret in database
     */
    private function storeUserSecret($userId, $secret)
    {
        $hashedSecret = hash('sha256', $secret);
        
        $sql = "INSERT INTO user_2fa_secrets (user_id, secret, created_at) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE secret = ?, updated_at = NOW()";
        
        $stmt = $this->authService->db->prepare($sql);
        return $stmt->execute([$userId, $hashedSecret, $hashedSecret]);
    }
    
    /**
     * Get user secret from database
     */
    private function getUserSecret($userId)
    {
        $sql = "SELECT secret FROM user_2fa_secrets WHERE user_id = ? LIMIT 1";
        $stmt = $this->authService->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $result = $stmt->fetch();
        return $result ? $result['secret'] : null;
    }
    
    /**
     * Remove user secret from database
     */
    private function removeUserSecret($userId)
    {
        $sql = "DELETE FROM user_2fa_secrets WHERE user_id = ?";
        $stmt = $this->authService->db->prepare($sql);
        return $stmt->execute([$userId]);
    }
    
    /**
     * Update user 2FA status
     */
    private function updateUserTwoFactorStatus($userId, $enabled)
    {
        $sql = "UPDATE users SET two_factor_enabled = ? WHERE id = ?";
        $stmt = $this->authService->db->prepare($sql);
        return $stmt->execute([$enabled, $userId]);
    }
    
    /**
     * Store backup codes
     */
    private function storeBackupCodes($userId, $codes)
    {
        $hashedCodes = array_map(function($code) {
            return hash('sha256', $code);
        }, $codes);
        
        $sql = "DELETE FROM user_backup_codes WHERE user_id = ?";
        $stmt = $this->authService->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $sql = "INSERT INTO user_backup_codes (user_id, code, created_at) VALUES (?, ?, NOW())";
        $stmt = $this->authService->db->prepare($sql);
        
        foreach ($hashedCodes as $code) {
            $stmt->execute([$userId, $code]);
        }
        
        return true;
    }
    
    /**
     * Get backup codes
     */
    private function getBackupCodes($userId)
    {
        $sql = "SELECT code FROM user_backup_codes WHERE user_id = ? AND used = 0";
        $stmt = $this->authService->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $codes = [];
        while ($row = $stmt->fetch()) {
            $codes[] = $row['code'];
        }
        
        return $codes;
    }
    
    /**
     * Remove backup code
     */
    private function removeBackupCode($userId, $index)
    {
        $sql = "UPDATE user_backup_codes SET used = 1, used_at = NOW() 
                WHERE user_id = ? AND id = (SELECT id FROM (SELECT id FROM user_backup_codes WHERE user_id = ? AND used = 0 LIMIT 1 OFFSET ?) AS temp)";
        $stmt = $this->authService->db->prepare($sql);
        return $stmt->execute([$userId, $userId, $index]);
    }
}
