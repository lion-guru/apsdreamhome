<?php

namespace App\Services\AI;

use App\Core\Database\Database;
use Exception;

/**
 * AI Fraud Detection Service
 * Analyzes transactions and user behavior for fraud patterns
 */
class AIFraudDetectionService
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    private function ensureTablesExist(): void
    {
        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS fraud_alerts (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    entity_type VARCHAR(50) NOT NULL,
                    entity_id INT UNSIGNED NOT NULL,
                    risk_score INT UNSIGNED NOT NULL DEFAULT 0,
                    risk_level ENUM('low','medium','high','critical') DEFAULT 'low',
                    factors JSON,
                    recommended_action VARCHAR(255),
                    status ENUM('pending','reviewed','resolved','false_positive') DEFAULT 'pending',
                    reviewed_by INT UNSIGNED,
                    reviewed_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_entity (entity_type, entity_id),
                    INDEX idx_status (status),
                    INDEX idx_risk (risk_level)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            $this->db->query("
                CREATE TABLE IF NOT EXISTS user_risk_scores (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id INT UNSIGNED NOT NULL,
                    user_type VARCHAR(20) NOT NULL,
                    risk_score INT UNSIGNED NOT NULL DEFAULT 0,
                    risk_level ENUM('low','medium','high','critical') DEFAULT 'low',
                    factors JSON,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    UNIQUE KEY uniq_user (user_id, user_type),
                    INDEX idx_risk (risk_level)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (Exception $e) {
            error_log('Fraud detection tables creation error: ' . $e->getMessage());
        }
    }
    
    /**
     * Analyze a transaction for fraud indicators
     */
    public function analyzeTransaction(array $transaction): array
    {
        $userId = $transaction['user_id'] ?? 0;
        $userType = $transaction['user_type'] ?? 'customer';
        
        $factors = [];
        $totalScore = 0;
        
        // Check unusual amount
        $amountScore = $this->checkUnusualAmount($transaction);
        if ($amountScore > 0) {
            $factors[] = ['factor' => 'unusual_amount', 'score' => $amountScore, 'description' => 'Transaction amount deviates from user pattern'];
            $totalScore += $amountScore;
        }
        
        // Check transaction velocity
        $velocityScore = $this->checkTransactionVelocity($userId);
        if ($velocityScore > 0) {
            $factors[] = ['factor' => 'high_velocity', 'score' => $velocityScore, 'description' => 'Too many transactions in short time period'];
            $totalScore += $velocityScore;
        }
        
        // Check location risk
        $locationScore = $this->checkLocationRisk($transaction);
        if ($locationScore > 0) {
            $factors[] = ['factor' => 'location_risk', 'score' => $locationScore, 'description' => 'Transaction from high-risk or unusual location'];
            $totalScore += $locationScore;
        }
        
        // Check device risk
        $deviceScore = $this->checkDeviceRisk($transaction);
        if ($deviceScore > 0) {
            $factors[] = ['factor' => 'device_risk', 'score' => $deviceScore, 'description' => 'New or suspicious device detected'];
            $totalScore += $deviceScore;
        }
        
        // Check timing risk
        $timingScore = $this->checkTimingRisk($transaction);
        if ($timingScore > 0) {
            $factors[] = ['factor' => 'timing_risk', 'score' => $timingScore, 'description' => 'Transaction at unusual hours'];
            $totalScore += $timingScore;
        }
        
        $riskLevel = $this->getRiskLevel($totalScore);
        $recommendedAction = $this->getRecommendedAction($riskLevel);
        
        // Create fraud alert if score is significant
        if ($totalScore >= 30) {
            $this->createFraudAlert('transaction', $transaction['id'] ?? 0, $totalScore, $riskLevel, $factors);
        }
        
        // Update user risk score
        $this->updateUserRiskScore($userId, $userType, $totalScore, $factors);
        
        return [
            'risk_score' => $totalScore,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommended_action' => $recommendedAction,
            'user_recommendation' => $this->getUserRecommendation($riskLevel)
        ];
    }
    
    /**
     * Analyze user behavior patterns
     */
    public function analyzeUserBehavior(int $userId, string $userType = 'customer'): array
    {
        $factors = [];
        $totalScore = 0;
        
        // Check for multiple accounts
        $multiAccountScore = $this->checkMultipleAccounts($userId, $userType);
        if ($multiAccountScore > 0) {
            $factors[] = ['factor' => 'multiple_accounts', 'score' => $multiAccountScore, 'description' => 'Multiple accounts linked to same identity'];
            $totalScore += $multiAccountScore;
        }
        
        // Check activity patterns
        $activityScore = $this->checkActivityPatterns($userId);
        if ($activityScore > 0) {
            $factors[] = ['factor' => 'suspicious_activity', 'score' => $activityScore, 'description' => 'Unusual activity patterns detected'];
            $totalScore += $activityScore;
        }
        
        // Check document risk
        $docScore = $this->checkDocumentRisk($userId);
        if ($docScore > 0) {
            $factors[] = ['factor' => 'document_risk', 'score' => $docScore, 'description' => 'Document verification issues'];
            $totalScore += $docScore;
        }
        
        $riskLevel = $this->getRiskLevel($totalScore);
        
        if ($totalScore >= 30) {
            $this->createFraudAlert('user', $userId, $totalScore, $riskLevel, $factors);
        }
        
        return [
            'risk_score' => $totalScore,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommended_action' => $this->getRecommendedAction($riskLevel)
        ];
    }
    
    private function checkUnusualAmount(array $transaction): int
    {
        try {
            $userId = $transaction['user_id'] ?? 0;
            $amount = $transaction['amount'] ?? 0;
            
            // Get user's average transaction amount
            $avg = $this->db->fetch("
                SELECT COALESCE(AVG(amount), 0) as avg_amount 
                FROM payment_transactions 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
            ", [$userId]);
            
            $avgAmount = $avg['avg_amount'] ?? 0;
            
            if ($avgAmount > 0 && $amount > $avgAmount * 5) {
                return 25; // 5x average
            } elseif ($avgAmount > 0 && $amount > $avgAmount * 3) {
                return 15; // 3x average
            } elseif ($avgAmount > 0 && $amount > $avgAmount * 2) {
                return 10; // 2x average
            }
        } catch (Exception $e) {
            error_log('Unusual amount check error: ' . $e->getMessage());
        }
        return 0;
    }
    
    private function checkTransactionVelocity(int $userId): int
    {
        try {
            // Count transactions in last hour
            $lastHour = $this->db->fetch("
                SELECT COUNT(*) as cnt 
                FROM payment_transactions 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ", [$userId]);
            
            if (($lastHour['cnt'] ?? 0) > 10) return 25;
            if (($lastHour['cnt'] ?? 0) > 5) return 15;
            
            // Count in last 24 hours
            $lastDay = $this->db->fetch("
                SELECT COUNT(*) as cnt 
                FROM payment_transactions 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ", [$userId]);
            
            if (($lastDay['cnt'] ?? 0) > 50) return 20;
            if (($lastDay['cnt'] ?? 0) > 20) return 10;
        } catch (Exception $e) {
            error_log('Velocity check error: ' . $e->getMessage());
        }
        return 0;
    }
    
    private function checkLocationRisk(array $transaction): int
    {
        try {
            $userId = $transaction['user_id'] ?? 0;
            $ip = $transaction['ip_address'] ?? '';
            
            // Check if IP is from high-risk country
            // This is a simplified check - in production use GeoIP
            if ($ip) {
                // Check if user has used this IP before
                $knownIp = $this->db->fetch("
                    SELECT COUNT(*) as cnt 
                    FROM payment_transactions 
                    WHERE user_id = ? AND ip_address = ? 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ", [$userId, $ip]);
                
                if (($knownIp['cnt'] ?? 0) === 0) {
                    // New IP - check if it's from different city/country
                    // Simplified: just flag new IPs
                    return 10;
                }
            }
        } catch (Exception $e) {
            error_log('Location risk check error: ' . $e->getMessage());
        }
        return 0;
    }
    
    private function checkDeviceRisk(array $transaction): int
    {
        try {
            $userId = $transaction['user_id'] ?? 0;
            $deviceFingerprint = $transaction['device_fingerprint'] ?? '';
            
            if ($deviceFingerprint) {
                $knownDevice = $this->db->fetch("
                    SELECT COUNT(*) as cnt 
                    FROM payment_transactions 
                    WHERE user_id = ? AND device_fingerprint = ? 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
                ", [$userId, $deviceFingerprint]);
                
                if (($knownDevice['cnt'] ?? 0) === 0) {
                    return 15; // New device
                }
            }
        } catch (Exception $e) {
            error_log('Device risk check error: ' . $e->getMessage());
        }
        return 0;
    }
    
    private function checkTimingRisk(array $transaction): int
    {
        try {
            $createdAt = $transaction['created_at'] ?? date('Y-m-d H:i:s');
            $hour = (int)date('H', strtotime($createdAt));
            
            // Flag transactions between 2 AM and 5 AM
            if ($hour >= 2 && $hour <= 5) {
                return 10;
            }
        } catch (Exception $e) {
            error_log('Timing risk check error: ' . $e->getMessage());
        }
        return 0;
    }
    
    private function checkMultipleAccounts(int $userId, string $userType): int
    {
        try {
            // Check for same email/phone across multiple users
            $user = $this->db->fetch("SELECT email, phone FROM users WHERE id = ?", [$userId]);
            
            if ($user) {
                $sameEmail = $this->db->fetch("
                    SELECT COUNT(*) as cnt FROM users 
                    WHERE email = ? AND id != ? AND role = ?
                ", [$user['email'], $userId, $userType]);
                
                if (($sameEmail['cnt'] ?? 0) > 0) return 30;
                
                if ($user['phone']) {
                    $samePhone = $this->db->fetch("
                        SELECT COUNT(*) as cnt FROM users 
                        WHERE phone = ? AND id != ? AND role = ?
                    ", [$user['phone'], $userId, $userType]);
                    
                    if (($samePhone['cnt'] ?? 0) > 0) return 30;
                }
            }
        } catch (Exception $e) {
            error_log('Multiple accounts check error: ' . $e->getMessage());
        }
        return 0;
    }
    
    private function checkActivityPatterns(int $userId): int
    {
        try {
            // Check for bot-like behavior (too regular intervals)
            $transactions = $this->db->fetchAll("
                SELECT UNIX_TIMESTAMP(created_at) as ts 
                FROM payment_transactions 
                WHERE user_id = ? 
                ORDER BY created_at DESC LIMIT 20
            ", [$userId]);
            
            if (count($transactions) >= 5) {
                $intervals = [];
                for ($i = 1; $i < count($transactions); $i++) {
                    $intervals[] = $transactions[$i-1]['ts'] - $transactions[$i]['ts'];
                }
                
                // Check if intervals are suspiciously regular
                $avgInterval = array_sum($intervals) / count($intervals);
                $variance = 0;
                foreach ($intervals as $interval) {
                    $variance += pow($interval - $avgInterval, 2);
                }
                $variance /= count($intervals);
                
                // Very low variance = bot-like
                if ($variance < 10 && $avgInterval < 300) { // less than 5 min apart, very regular
                    return 20;
                }
            }
        } catch (Exception $e) {
            error_log('Activity patterns check error: ' . $e->getMessage());
        }
        return 0;
    }
    
    private function checkDocumentRisk(int $userId): int
    {
        try {
            // Check KYC verification status
            $kyc = $this->db->fetch("
                SELECT status FROM kyc_verifications 
                WHERE user_id = ? ORDER BY created_at DESC LIMIT 1
            ", [$userId]);
            
            if ($kyc) {
                if ($kyc['status'] === 'rejected') return 25;
                if ($kyc['status'] === 'pending') return 10;
            } else {
                // No KYC at all for high-value transactions
                return 15;
            }
        } catch (Exception $e) {
            error_log('Document risk check error: ' . $e->getMessage());
        }
        return 0;
    }
    
    private function getRiskLevel(int $score): string
    {
        if ($score >= 70) return 'critical';
        if ($score >= 50) return 'high';
        if ($score >= 30) return 'medium';
        return 'low';
    }
    
    private function createFraudAlert(string $entityType, int $entityId, int $score, string $level, array $factors): void
    {
        try {
            $this->db->query("
                INSERT INTO fraud_alerts (entity_type, entity_id, risk_score, risk_level, factors, recommended_action)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [
                $entityType,
                $entityId,
                $score,
                $level,
                json_encode($factors),
                $this->getRecommendedAction($level)
            ]);
        } catch (Exception $e) {
            error_log('Create fraud alert error: ' . $e->getMessage());
        }
    }
    
    private function updateUserRiskScore(int $userId, string $userType, int $score, array $factors): void
    {
        try {
            $existing = $this->db->fetch("
                SELECT risk_score, factors FROM user_risk_scores 
                WHERE user_id = ? AND user_type = ?
            ", [$userId, $userType]);
            
            $newScore = max($score, $existing['risk_score'] ?? 0);
            $newLevel = $this->getRiskLevel($newScore);
            
            $allFactors = $factors;
            if ($existing && $existing['factors']) {
                $oldFactors = json_decode($existing['factors'], true) ?? [];
                $allFactors = array_merge($oldFactors, $factors);
            }
            
            $this->db->query("
                INSERT INTO user_risk_scores (user_id, user_type, risk_score, risk_level, factors)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    risk_score = VALUES(risk_score),
                    risk_level = VALUES(risk_level),
                    factors = VALUES(factors),
                    updated_at = NOW()
            ", [$userId, $userType, $newScore, $newLevel, json_encode($allFactors)]);
        } catch (Exception $e) {
            error_log('Update user risk score error: ' . $e->getMessage());
        }
    }
    
    private function getRecommendedAction(string $riskLevel): string
    {
        return match($riskLevel) {
            'critical' => 'Block transaction and require manual review',
            'high' => 'Require additional verification (OTP, document)',
            'medium' => 'Flag for monitoring, allow with warning',
            default => 'Allow transaction, log for analytics'
        };
    }
    
    private function getUserRecommendation(string $riskLevel): string
    {
        return match($riskLevel) {
            'critical' => 'Your transaction has been blocked for security. Contact support.',
            'high' => 'Additional verification required to proceed.',
            'medium' => 'This transaction is being monitored.',
            default => 'Transaction approved.'
        };
    }
    
    public function getPendingAlerts(int $limit = 50): array
    {
        try {
            return $this->db->fetchAll("
                SELECT fa.*, u.name as reviewer_name 
                FROM fraud_alerts fa
                LEFT JOIN users u ON fa.reviewed_by = u.id
                WHERE fa.status = 'pending'
                ORDER BY fa.risk_score DESC, fa.created_at DESC
                LIMIT ?
            ", [$limit]) ?? [];
        } catch (Exception $e) {
            error_log('Get pending alerts error: ' . $e->getMessage());
            return [];
        }
    }
    
    public function getFraudStats(): array
    {
        try {
            $stats = $this->db->fetch("
                SELECT 
                    COUNT(*) as total_alerts,
                    SUM(CASE WHEN risk_level = 'critical' THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN risk_level = 'high' THEN 1 ELSE 0 END) as high,
                    SUM(CASE WHEN risk_level = 'medium' THEN 1 ELSE 0 END) as medium,
                    SUM(CASE WHEN risk_level = 'low' THEN 1 ELSE 0 END) as low,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
                FROM fraud_alerts
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            
            $userStats = $this->db->fetch("
                SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN risk_level = 'critical' THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN risk_level = 'high' THEN 1 ELSE 0 END) as high
                FROM user_risk_scores
            ");
            
            return array_merge($stats ?? [], $userStats ?? []);
        } catch (Exception $e) {
            error_log('Get fraud stats error: ' . $e->getMessage());
            return [];
        }
    }
}