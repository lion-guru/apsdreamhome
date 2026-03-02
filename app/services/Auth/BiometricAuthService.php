<?php
namespace App\Services\Auth;

use App\Services\AuthService;
use Firebase\JWT\JWT;

class BiometricAuthService
{
    private $authService;
    private $jwtKey;
    
    public function __construct()
    {
        $this->authService = new AuthService();
        $this->jwtKey = config('app.jwt_secret');
    }
    
    /**
     * Generate biometric challenge
     */
    public function generateChallenge($userId)
    {
        $user = $this->authService->getUserById($userId);
        if (!$user) {
            return false;
        }
        
        $challenge = [
            'user_id' => $userId,
            'challenge' => bin2hex(random_bytes(32)),
            'timestamp' => time(),
            'expires_at' => time() + 300 // 5 minutes
        ];
        
        // Store challenge
        $this->storeChallenge($challenge);
        
        return $challenge['challenge'];
    }
    
    /**
     * Verify biometric response
     */
    public function verifyBiometricResponse($userId, $response, $signature)
    {
        $challenge = $this->getChallenge($response);
        if (!$challenge || $challenge['user_id'] != $userId) {
            return false;
        }
        
        if (time() > $challenge['expires_at']) {
            return false;
        }
        
        // Verify signature
        $publicKey = $this->getUserPublicKey($userId);
        if (!$publicKey) {
            return false;
        }
        
        $verified = openssl_verify($response, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256);
        
        if ($verified) {
            $this->removeChallenge($challenge['id']);
            return true;
        }
        
        return false;
    }
    
    /**
     * Register biometric device
     */
    public function registerDevice($userId, $deviceName, $publicKey)
    {
        $deviceId = $this->generateDeviceId();
        
        $sql = "INSERT INTO user_biometric_devices (user_id, device_id, device_name, public_key, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->authService->db->prepare($sql);
        $result = $stmt->execute([$userId, $deviceId, $deviceName, $publicKey]);
        
        if ($result) {
            return $deviceId;
        }
        
        return false;
    }
    
    /**
     * Get user devices
     */
    public function getUserDevices($userId)
    {
        $sql = "SELECT device_id, device_name, created_at, last_used_at 
                FROM user_biometric_devices 
                WHERE user_id = ? AND active = 1 
                ORDER BY created_at DESC";
        
        $stmt = $this->authService->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Remove device
     */
    public function removeDevice($userId, $deviceId)
    {
        $sql = "UPDATE user_biometric_devices 
                SET active = 0, removed_at = NOW() 
                WHERE user_id = ? AND device_id = ?";
        
        $stmt = $this->authService->db->prepare($sql);
        return $stmt->execute([$userId, $deviceId]);
    }
    
    /**
     * Generate JWT token for biometric auth
     */
    public function generateBiometricToken($userId, $deviceId)
    {
        $payload = [
            'user_id' => $userId,
            'device_id' => $deviceId,
            'auth_type' => 'biometric',
            'iat' => time(),
            'exp' => time() + 3600 // 1 hour
        ];
        
        return JWT::encode($payload, $this->jwtKey);
    }
    
    /**
     * Verify biometric token
     */
    public function verifyBiometricToken($token)
    {
        try {
            $decoded = JWT::decode($token, $this->jwtKey, ['HS256']);
            
            if ($decoded->auth_type !== 'biometric') {
                return false;
            }
            
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Store challenge
     */
    private function storeChallenge($challenge)
    {
        $sql = "INSERT INTO biometric_challenges (user_id, challenge, expires_at, created_at) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->authService->db->prepare($sql);
        return $stmt->execute([
            $challenge['user_id'],
            $challenge['challenge'],
            date('Y-m-d H:i:s', $challenge['expires_at'])
        ]);
    }
    
    /**
     * Get challenge
     */
    private function getChallenge($challenge)
    {
        $sql = "SELECT id, user_id, expires_at 
                FROM biometric_challenges 
                WHERE challenge = ? AND used = 0 
                LIMIT 1";
        
        $stmt = $this->authService->db->prepare($sql);
        $stmt->execute([$challenge]);
        
        return $stmt->fetch();
    }
    
    /**
     * Remove challenge
     */
    private function removeChallenge($challengeId)
    {
        $sql = "UPDATE biometric_challenges SET used = 1 WHERE id = ?";
        $stmt = $this->authService->db->prepare($sql);
        return $stmt->execute([$challengeId]);
    }
    
    /**
     * Get user public key
     */
    private function getUserPublicKey($userId)
    {
        $sql = "SELECT public_key FROM user_biometric_devices 
                WHERE user_id = ? AND active = 1 
                LIMIT 1";
        
        $stmt = $this->authService->db->prepare($sql);
        $stmt->execute([$userId]);
        
        $result = $stmt->fetch();
        return $result ? $result['public_key'] : null;
    }
    
    /**
     * Generate device ID
     */
    private function generateDeviceId()
    {
        return uniqid('bio_', true);
    }
}
