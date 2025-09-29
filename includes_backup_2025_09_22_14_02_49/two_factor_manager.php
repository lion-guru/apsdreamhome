<?php
// Two-Factor Authentication Management System

class TwoFactorManager {
    // 2FA Methods
    const METHOD_EMAIL = 'email';
    const METHOD_SMS = 'sms';
    const METHOD_AUTHENTICATOR = 'authenticator_app';

    // Configuration
    private $config = [
        'token_length' => 6,
        'token_expiry' => 900, // 15 minutes
        'max_attempts' => 3
    ];

    // Dependencies
    private $db;
    private $logger;
    private $email_service;
    private $sms_service;

    public function __construct($db, $logger, $email_service = null, $sms_service = null) {
        $this->db = $db;
        $this->logger = $logger;
        $this->email_service = $email_service;
        $this->sms_service = $sms_service;
    }

    /**
     * Generate Two-Factor Authentication Token
     * @param int $user_id User ID
     * @param string $method Authentication method
     * @return string|false Generated token or false
     */
    public function generateToken($user_id, $method = self::METHOD_EMAIL) {
        try {
            // Validate method
            $this->validateMethod($method);

            // Generate secure token
            $token = $this->generateSecureToken();

            // Store token in database
            $stmt = $this->db->prepare("
                INSERT INTO two_factor_tokens 
                (user_id, token, method, expires_at) 
                VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))
            ");
            $stmt->bind_param(
                'issi', 
                $user_id, 
                $token, 
                $method, 
                $this->config['token_expiry']
            );
            $stmt->execute();

            // Send token via selected method
            $this->sendToken($user_id, $token, $method);

            return $token;
        } catch (Exception $e) {
            $this->logger->log(
                "2FA token generation error: " . $e->getMessage(), 
                'error', 
                'security'
            );
            return false;
        }
    }

    /**
     * Validate Two-Factor Authentication Token
     * @param int $user_id User ID
     * @param string $token Token to validate
     * @return bool
     */
    public function validateToken($user_id, $token) {
        try {
            // Check token validity
            $stmt = $this->db->prepare("
                SELECT id, expires_at, used 
                FROM two_factor_tokens 
                WHERE user_id = ? AND token = ? AND expires_at > NOW() AND used = 0
            ");
            $stmt->bind_param('is', $user_id, $token);
            $stmt->execute();
            $result = $stmt->get_result();

            // Token not found or expired
            if ($result->num_rows === 0) {
                $this->logger->log(
                    "Invalid 2FA token for user {$user_id}", 
                    'warning', 
                    'security'
                );
                return false;
            }

            $token_data = $result->fetch_assoc();

            // Mark token as used
            $update_stmt = $this->db->prepare("
                UPDATE two_factor_tokens 
                SET used = 1 
                WHERE id = ?
            ");
            $update_stmt->bind_param('i', $token_data['id']);
            $update_stmt->execute();

            return true;
        } catch (Exception $e) {
            $this->logger->log(
                "2FA token validation error: " . $e->getMessage(), 
                'error', 
                'security'
            );
            return false;
        }
    }

    /**
     * Send token via selected method
     * @param int $user_id User ID
     * @param string $token Token to send
     * @param string $method Send method
     */
    private function sendToken($user_id, $token, $method) {
        // Fetch user details
        $stmt = $this->db->prepare("SELECT email, phone FROM users WHERE id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        switch ($method) {
            case self::METHOD_EMAIL:
                $this->sendEmailToken($user['email'], $token);
                break;
            case self::METHOD_SMS:
                $this->sendSmsToken($user['phone'], $token);
                break;
            case self::METHOD_AUTHENTICATOR:
                // Placeholder for authenticator app integration
                break;
        }
    }

    /**
     * Send token via email
     * @param string $email Recipient email
     * @param string $token Token to send
     */
    private function sendEmailToken($email, $token) {
        if (!$this->email_service) {
            $this->logger->log(
                "Email service not configured", 
                'warning', 
                'security'
            );
            return;
        }

        $subject = "Your Two-Factor Authentication Token";
        $body = "Your 2FA token is: {$token}. It will expire in 15 minutes.";

        $this->email_service->send($email, $subject, $body);
    }

    /**
     * Send token via SMS
     * @param string $phone Recipient phone number
     * @param string $token Token to send
     */
    private function sendSmsToken($phone, $token) {
        if (!$this->sms_service) {
            $this->logger->log(
                "SMS service not configured", 
                'warning', 
                'security'
            );
            return;
        }

        $message = "Your 2FA token is: {$token}. It will expire in 15 minutes.";
        $this->sms_service->send($phone, $message);
    }

    /**
     * Generate secure token
     * @return string Generated token
     */
    private function generateSecureToken() {
        // Generate numeric token
        $token = str_pad(
            random_int(0, pow(10, $this->config['token_length']) - 1), 
            $this->config['token_length'], 
            '0', 
            STR_PAD_LEFT
        );

        return $token;
    }

    /**
     * Validate authentication method
     * @param string $method Method to validate
     * @throws Exception If method is invalid
     */
    private function validateMethod($method) {
        $valid_methods = [
            self::METHOD_EMAIL, 
            self::METHOD_SMS, 
            self::METHOD_AUTHENTICATOR
        ];

        if (!in_array($method, $valid_methods)) {
            throw new Exception("Invalid 2FA method");
        }
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens() {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM two_factor_tokens 
                WHERE expires_at < NOW() OR used = 1
            ");
            $stmt->execute();

            $this->logger->log(
                "Cleaned up expired 2FA tokens", 
                'info', 
                'security'
            );
        } catch (Exception $e) {
            $this->logger->log(
                "Token cleanup error: " . $e->getMessage(), 
                'error', 
                'security'
            );
        }
    }
}

// Helper function for dependency injection
function getTwoFactorManager() {
    $container = container(); // Assuming dependency container is loaded
    
    // Lazy load dependencies
    $db = $container->resolve('db_connection');
    $logger = $container->resolve('logger');
    $email_service = $container->resolve('email_service', null);
    $sms_service = $container->resolve('sms_service', null);
    
    return new TwoFactorManager($db, $logger, $email_service, $sms_service);
}

return getTwoFactorManager();
