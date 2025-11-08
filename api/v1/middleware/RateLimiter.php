<?php

class RateLimiter {
    private $conn;
    private $rateLimit;
    private $windowInSeconds;

    public function __construct($conn, $rateLimit = 100, $windowInSeconds = 60) {
        $this->conn = $conn;
        $this->rateLimit = $rateLimit;
        $this->windowInSeconds = $windowInSeconds;
    }

    public function check($apiKey) {
        // Get the current timestamp
        $currentTime = time();
        $windowStart = $currentTime - $this->windowInSeconds;

        // Get the user's rate limit from the database
        $stmt = $this->conn->prepare(
            "SELECT api_rate_limit FROM users u 
             JOIN api_keys ak ON u.id = ak.user_id 
             WHERE ak.api_key = ? AND ak.status = 'active'"
        );
        $stmt->bind_param('s', $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return [
                'allowed' => false,
                'error' => 'Invalid or inactive API key',
                'status' => 403
            ];
        }

        $user = $result->fetch_assoc();
        $userRateLimit = $user['api_rate_limit'] ?? $this->rateLimit;

        // Clean up old rate limit records (older than our window)
        $this->cleanupOldRecords($apiKey, $windowStart);

        // Count requests in current window
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as request_count 
             FROM api_rate_limits 
             WHERE api_key = ? AND timestamp > ?"
        );
        $stmt->bind_param('si', $apiKey, $windowStart);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['request_count'];

        // Check if rate limit is exceeded
        if ($count >= $userRateLimit) {
            return [
                'allowed' => false,
                'error' => 'Rate limit exceeded',
                'status' => 429,
                'limit' => $userRateLimit,
                'remaining' => 0,
                'reset' => $windowStart + $this->windowInSeconds
            ];
        }

        // Log this request
        $this->logRequest($apiKey, $currentTime);

        return [
            'allowed' => true,
            'limit' => $userRateLimit,
            'remaining' => $userRateLimit - $count - 1,
            'reset' => $windowStart + $this->windowInSeconds
        ];
    }

    private function logRequest($apiKey, $timestamp) {
        $stmt = $this->conn->prepare(
            "INSERT INTO api_rate_limits (api_key, timestamp) VALUES (?, ?)"
        );
        $stmt->bind_param('si', $apiKey, $timestamp);
        $stmt->execute();
    }

    private function cleanupOldRecords($apiKey, $windowStart) {
        // Clean up old records (run this only occasionally for performance)
        if (rand(1, 100) === 1) { // 1% chance to clean up
            $stmt = $this->conn->prepare(
                "DELETE FROM api_rate_limits WHERE timestamp < ?"
            );
            $stmt->bind_param('i', $windowStart);
            $stmt->execute();
        }
    }
}
?>
