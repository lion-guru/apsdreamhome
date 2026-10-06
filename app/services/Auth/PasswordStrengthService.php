<?php
namespace App\Services\Auth;

/**
 * Password Strength Service
 * Comprehensive password validation with configurable rules
 */
class PasswordStrengthService
{
    private array $config = [
        'min_length' => 8,
        'max_length' => 128,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'max_consecutive_chars' => 3,
        'block_common_passwords' => true,
        'block_user_info' => true,
    ];

    private array $commonPasswords = [
        'password', 'password123', '123456', '123456789', 'qwerty',
        'admin', 'welcome', 'letmein', 'monkey', 'dragon',
        'abc123', '111111', 'iloveyou', 'sunshine', 'princess',
        'football', 'baseball', 'superman', 'batman', 'trustno1',
        'master', 'hello', 'freedom', 'whatever', 'qazwsx',
    ];

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Validate password and return detailed result
     */
    public function validate(string $password, array $userInfo = []): array
    {
        $errors = [];
        $warnings = [];
        $score = 0;
        $maxScore = 0;

        // Length checks
        $maxScore += 25;
        if (strlen($password) < $this->config['min_length']) {
            $errors[] = "Password must be at least {$this->config['min_length']} characters";
        } elseif (strlen($password) >= $this->config['min_length']) {
            $score += 25;
        }

        $maxScore += 15;
        if (strlen($password) > $this->config['max_length']) {
            $errors[] = "Password must not exceed {$this->config['max_length']} characters";
        } else {
            $score += 15;
        }

        // Character variety
        $maxScore += 15;
        if ($this->config['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        } else {
            $score += 5;
        }

        $maxScore += 15;
        if ($this->config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } else {
            $score += 5;
        }

        $maxScore += 15;
        if ($this->config['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        } else {
            $score += 5;
        }

        $maxScore += 15;
        if ($this->config['require_symbols'] && !preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        } else {
            $score += 5;
        }

        // Consecutive characters
        $maxScore += 10;
        if ($this->config['max_consecutive_chars'] > 0) {
            $consecutive = $this->checkConsecutiveChars($password, $this->config['max_consecutive_chars']);
            if ($consecutive) {
                $errors[] = "Password cannot contain more than {$this->config['max_consecutive_chars']} consecutive identical characters";
            } else {
                $score += 10;
            }
        }

        // Common passwords
        $maxScore += 20;
        if ($this->config['block_common_passwords'] && $this->isCommonPassword($password)) {
            $errors[] = 'This password is too common. Please choose a more unique password';
        } else {
            $score += 20;
        }

        // User info in password
        $maxScore += 10;
        if ($this->config['block_user_info'] && !empty($userInfo)) {
            $containsInfo = $this->containsUserInfo($password, $userInfo);
            if ($containsInfo) {
                $warnings[] = 'Avoid using personal information in your password';
                $score = max(0, $score - 10);
            } else {
                $score += 10;
            }
        }

        // Entropy calculation
        $entropy = $this->calculateEntropy($password);
        $maxScore += 15;
        $score += min(15, ($entropy / 60) * 15);

        $percentage = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
        $strength = $this->getStrengthLabel($percentage);

        return [
            'valid' => empty($errors),
            'score' => $percentage,
            'strength' => $strength,
            'entropy' => $entropy,
            'errors' => $errors,
            'warnings' => $warnings,
            'checks' => [
                'length' => strlen($password) >= $this->config['min_length'],
                'lowercase' => (bool)preg_match('/[a-z]/', $password),
                'uppercase' => (bool)preg_match('/[A-Z]/', $password),
                'numbers' => (bool)preg_match('/[0-9]/', $password),
                'symbols' => (bool)preg_match('/[^a-zA-Z0-9]/', $password),
                'no_consecutive' => !$this->checkConsecutiveChars($password, $this->config['max_consecutive_chars']),
                'not_common' => !$this->isCommonPassword($password),
                'no_user_info' => empty($userInfo) || !$this->containsUserInfo($password, $userInfo),
            ],
        ];
    }

    /**
     * Get strength label from percentage
     */
    private function getStrengthLabel(int $percentage): string
    {
        if ($percentage < 30) return 'Very Weak';
        if ($percentage < 50) return 'Weak';
        if ($percentage < 70) return 'Fair';
        if ($percentage < 85) return 'Good';
        if ($percentage < 95) return 'Strong';
        return 'Very Strong';
    }

    /**
     * Check for consecutive identical characters
     */
    private function checkConsecutiveChars(string $password, int $max): bool
    {
        $count = 1;
        for ($i = 1; $i < strlen($password); $i++) {
            if ($password[$i] === $password[$i - 1]) {
                $count++;
                if ($count > $max) return true;
            } else {
                $count = 1;
            }
        }
        return false;
    }

    /**
     * Check if password is in common list
     */
    private function isCommonPassword(string $password): bool
    {
        return in_array(strtolower($password), $this->commonPasswords);
    }

    /**
     * Check if password contains user info
     */
    private function containsUserInfo(string $password, array $userInfo): bool
    {
        $lowerPassword = strtolower($password);
        foreach ($userInfo as $key => $value) {
            if (empty($value)) continue;
            if (strlen($value) >= 3 && stripos($lowerPassword, strtolower($value)) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate password entropy
     */
    private function calculateEntropy(string $password): float
    {
        $pool = 0;
        if (preg_match('/[a-z]/', $password)) $pool += 26;
        if (preg_match('/[A-Z]/', $password)) $pool += 26;
        if (preg_match('/[0-9]/', $password)) $pool += 10;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $pool += 32;

        return strlen($password) * log($pool, 2);
    }

    /**
     * Generate random secure password
     */
    public function generate(int $length = 16): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';
        $bytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[$bytes[$i] % strlen($chars)];
        }
        return $password;
    }

    /**
     * Update configuration
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Get current configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}

