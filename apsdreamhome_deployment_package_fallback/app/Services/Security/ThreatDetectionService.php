<?php
namespace App\Services\Security;

class ThreatDetectionService
{
    private $db;
    
    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }
    
    /**
     * Analyze login attempts for suspicious patterns
     */
    public function analyzeLoginAttempts($ip, $email)
    {
        $threats = [];
        
        // Check for brute force attempts
        $bruteForce = $this->detectBruteForce($ip, $email);
        if ($bruteForce) {
            $threats[] = $bruteForce;
        }
        
        // Check for credential stuffing
        $credentialStuffing = $this->detectCredentialStuffing($ip);
        if ($credentialStuffing) {
            $threats[] = $credentialStuffing;
        }
        
        // Check for unusual login patterns
        $unusualPattern = $this->detectUnusualPattern($ip, $email);
        if ($unusualPattern) {
            $threats[] = $unusualPattern;
        }
        
        return $threats;
    }
    
    /**
     * Detect brute force attacks
     */
    private function detectBruteForce($ip, $email)
    {
        $sql = "SELECT COUNT(*) as attempts, MAX(created_at) as last_attempt 
                FROM login_attempts 
                WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        if ($result['attempts'] >= 10) {
            return [
                'type' => 'brute_force',
                'severity' => 'high',
                'ip' => $ip,
                'email' => $email,
                'attempts' => $result['attempts'],
                'last_attempt' => $result['last_attempt'],
                'recommendation' => 'Block IP address temporarily'
            ];
        }
        
        return null;
    }
    
    /**
     * Detect credential stuffing attacks
     */
    private function detectCredentialStuffing($ip)
    {
        $sql = "SELECT COUNT(DISTINCT email) as unique_emails, COUNT(*) as total_attempts 
                FROM login_attempts 
                WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        
        // If many different emails tried from same IP
        if ($result['unique_emails'] >= 5 && $result['total_attempts'] >= 20) {
            return [
                'type' => 'credential_stuffing',
                'severity' => 'critical',
                'ip' => $ip,
                'unique_emails' => $result['unique_emails'],
                'total_attempts' => $result['total_attempts'],
                'recommendation' => 'Block IP address immediately'
            ];
        }
        
        return null;
    }
    
    /**
     * Detect unusual login patterns
     */
    private function detectUnusualPattern($ip, $email)
    {
        // Check for login from unusual location
        $unusualLocation = $this->detectUnusualLocation($ip, $email);
        if ($unusualLocation) {
            return $unusualLocation;
        }
        
        // Check for login at unusual time
        $unusualTime = $this->detectUnusualTime($email);
        if ($unusualTime) {
            return $unusualTime;
        }
        
        return null;
    }
    
    /**
     * Detect unusual login location
     */
    private function detectUnusualLocation($ip, $email)
    {
        $sql = "SELECT country, city FROM user_login_history 
                WHERE email = ? AND successful = 1 
                ORDER BY created_at DESC LIMIT 10";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $history = $stmt->fetchAll();
        
        if (empty($history)) {
            return null; // First login
        }
        
        $currentLocation = $this->getLocationFromIP($ip);
        $usualCountries = array_unique(array_column($history, 'country'));
        
        if (!in_array($currentLocation['country'], $usualCountries)) {
            return [
                'type' => 'unusual_location',
                'severity' => 'medium',
                'ip' => $ip,
                'email' => $email,
                'current_location' => $currentLocation,
                'usual_countries' => $usualCountries,
                'recommendation' => 'Require additional verification'
            ];
        }
        
        return null;
    }
    
    /**
     * Detect unusual login time
     */
    private function detectUnusualTime($email)
    {
        $sql = "SELECT HOUR(created_at) as hour FROM user_login_history 
                WHERE email = ? AND successful = 1 
                ORDER BY created_at DESC LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $history = $stmt->fetchAll();
        
        if (count($history) < 10) {
            return null; // Not enough history
        }
        
        $currentHour = (int)date('H');
        $usualHours = array_column($history, 'hour');
        
        // Check if current hour is unusual (less than 10% of logins)
        $hourFrequency = array_count_values($usualHours);
        $totalLogins = count($usualHours);
        $currentHourFrequency = $hourFrequency[$currentHour] ?? 0;
        
        if ($currentHourFrequency / $totalLogins < 0.1) {
            return [
                'type' => 'unusual_time',
                'severity' => 'low',
                'email' => $email,
                'current_hour' => $currentHour,
                'frequency' => $currentHourFrequency / $totalLogins,
                'recommendation' => 'Monitor for suspicious activity'
            ];
        }
        
        return null;
    }
    
    /**
     * Analyze API usage patterns
     */
    public function analyzeApiUsage($userId, $apiKey)
    {
        $threats = [];
        
        // Check for API abuse
        $apiAbuse = $this->detectApiAbuse($apiKey);
        if ($apiAbuse) {
            $threats[] = $apiAbuse;
        }
        
        // Check for unusual API patterns
        $unusualPattern = $this->detectUnusualApiPattern($userId);
        if ($unusualPattern) {
            $threats[] = $unusualPattern;
        }
        
        return $threats;
    }
    
    /**
     * Detect API abuse
     */
    private function detectApiAbuse($apiKey)
    {
        $sql = "SELECT COUNT(*) as requests, MAX(created_at) as last_request 
                FROM api_usage_logs 
                WHERE api_key = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$apiKey]);
        $result = $stmt->fetch();
        
        if ($result['requests'] > 100) { // More than 100 requests per minute
            return [
                'type' => 'api_abuse',
                'severity' => 'high',
                'api_key' => $apiKey,
                'requests_per_minute' => $result['requests'],
                'recommendation' => 'Rate limit or temporarily block API key'
            ];
        }
        
        return null;
    }
    
    /**
     * Detect unusual API patterns
     */
    private function detectUnusualApiPattern($userId)
    {
        $sql = "SELECT endpoint, COUNT(*) as requests FROM api_usage_logs 
                WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY endpoint ORDER BY requests DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $currentUsage = $stmt->fetchAll();
        
        $sql = "SELECT endpoint, AVG(requests) as avg_requests FROM (
                    SELECT endpoint, COUNT(*) as requests 
                    FROM api_usage_logs 
                    WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                    GROUP BY DATE(created_at), endpoint
                ) as daily_usage GROUP BY endpoint";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $averageUsage = $stmt->fetchAll();
        
        $avgByEndpoint = [];
        foreach ($averageUsage as $avg) {
            $avgByEndpoint[$avg['endpoint']] = $avg['avg_requests'];
        }
        
        foreach ($currentUsage as $current) {
            $avg = $avgByEndpoint[$current['endpoint']] ?? 0;
            
            if ($avg > 0 && $current['requests'] > $avg * 5) { // 5x normal usage
                return [
                    'type' => 'unusual_api_pattern',
                    'severity' => 'medium',
                    'user_id' => $userId,
                    'endpoint' => $current['endpoint'],
                    'current_requests' => $current['requests'],
                    'average_requests' => $avg,
                    'recommendation' => 'Monitor API usage and contact user if needed'
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Get location from IP (placeholder implementation)
     */
    private function getLocationFromIP($ip)
    {
        // This would use a GeoIP service to get location
        // For now, return dummy data
        return [
            'country' => 'Unknown',
            'city' => 'Unknown'
        ];
    }
    
    /**
     * Log security threat
     */
    public function logThreat($threat)
    {
        $sql = "INSERT INTO security_threats (type, severity, ip_address, user_id, threat_data, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $threat['type'],
            $threat['severity'],
            $threat['ip'] ?? null,
            $threat['email'] ?? null,
            json_encode($threat)
        ]);
    }
    
    /**
     * Get active threats
     */
    public function getActiveThreats($limit = 50)
    {
        $sql = "SELECT * FROM security_threats 
                WHERE resolved = 0 
                ORDER BY severity DESC, created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Resolve threat
     */
    public function resolveThreat($threatId, $resolution)
    {
        $sql = "UPDATE security_threats 
                SET resolved = 1, resolution = ?, resolved_at = NOW() 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$resolution, $threatId]);
    }
}
