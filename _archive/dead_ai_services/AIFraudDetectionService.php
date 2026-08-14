<?php

namespace App\Services\AI;

use App\Core\Database\Database;

/**
 * AI Fraud Detection Service
 * Detects suspicious activities and potential fraud using ML algorithms
 */
class AIFraudDetectionService
{
    private $database;
    private $riskThresholds;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->riskThresholds = [
            'low' => 30,
            'medium' => 60,
            'high' => 80
        ];
        $this->ensureTablesExist();
    }
    
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Analyze transaction for fraud
     */
    public function analyzeTransaction(array $transaction): array
    {
        $riskFactors = [];
        $riskScore = 0;
        
        // Check unusual amount
        $amountRisk = $this->checkUnusualAmount($transaction);
        if ($amountRisk > 0) {
            $riskScore += $amountRisk;
            $riskFactors[] = ['type' => 'unusual_amount', 'score' => $amountRisk];
        }
        
        // Check velocity
        $velocityRisk = $this->checkTransactionVelocity($transaction['user_id'] ?? 0);
        if ($velocityRisk > 0) {
            $riskScore += $velocityRisk;
            $riskFactors[] = ['type' => 'velocity', 'score' => $velocityRisk];
        }
        
        // Check location
        $locationRisk = $this->checkLocationRisk($transaction);
        if ($locationRisk > 0) {
            $riskScore += $locationRisk;
            $riskFactors[] = ['type' => 'location', 'score' => $locationRisk];
        }
        
        // Check device/IP
        $deviceRisk = $this->checkDeviceRisk($transaction);
        if ($deviceRisk > 0) {
            $riskScore += $deviceRisk;
            $riskFactors[] = ['type' => 'device', 'score' => $deviceRisk];
        }
        
        // Check timing
        $timeRisk = $this->checkTimingRisk($transaction);
        if ($timeRisk > 0) {
            $riskScore += $timeRisk;
            $riskFactors[] = ['type' => 'timing', 'score' => $timeRisk];
        }
        
        $riskLevel = $this->getRiskLevel($riskScore);
        
        // Create alert if high risk
        if ($riskLevel !== 'low') {
            $this->createFraudAlert('transaction', $transaction['id'] ?? 0, $riskScore, $riskLevel, $riskFactors);
        }
        
        return [
            'success' => true,
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactors,
            'action' => $this->getRecommendedAction($riskLevel),
            'requires_review' => $riskLevel !== 'low'
        ];
    }
    
    /**
     * Analyze user behavior
     */
    public function analyzeUserBehavior(int $userId, string $userType = 'customer'): array
    {
        try {
            $db = $this->database->getConnection();
            
            $factors = [];
            $totalScore = 0;
            
            // Check multiple accounts
            $accountRisk = $this->checkMultipleAccounts($userId, $userType);
            if ($accountRisk > 0) {
                $totalScore += $accountRisk;
                $factors[] = ['type' => 'multiple_accounts', 'score' => $accountRisk];
            }
            
            // Check suspicious activity patterns
            $activityRisk = $this->checkActivityPatterns($userId);
            if ($activityRisk > 0) {
                $totalScore += $activityRisk;
                $factors[] = ['type' => 'suspicious_activity', 'score' => $activityRisk];
            }
            
            // Check document authenticity
            $docRisk = $this->checkDocumentRisk($userId);
            if ($docRisk > 0) {
                $totalScore += $docRisk;
                $factors[] = ['type' => 'document_issues', 'score' => $docRisk];
            }
            
            // Update user risk score
            $this->updateUserRiskScore($userId, $userType, $totalScore, $factors);
            
            $riskLevel = $this->getRiskLevel($totalScore);
            
            // Create alert if high risk
            if ($riskLevel === 'high' || $riskLevel === 'critical') {
                $this->createFraudAlert('user', $userId, $totalScore, $riskLevel, $factors);
            }
            
            return [
                'success' => true,
                'user_id' => $userId,
                'risk_score' => $totalScore,
                'risk_level' => $riskLevel,
                'factors' => $factors,
                'recommendation' => $this->getUserRecommendation($riskLevel)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Check for unusual transaction amount
     */
    private function checkUnusualAmount(array $transaction): int
    {
        $amount = $transaction['amount'] ?? 0;
        $userId = $transaction['user_id'] ?? 0;
        
        // Get user's average transaction amount
        $db = $this->database->getConnection();
        $sql = "SELECT AVG(amount) as avg_amount, MAX(amount) as max_amount
            FROM payment_transactions 
            WHERE user_id = ? AND status = 'success'
            AND created_at > DATE_SUB(NOW(), INTERVAL 90 DAY)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$stats['avg_amount']) {
            // First transaction - check against global average
            $globalSql = "SELECT AVG(amount) as global_avg FROM payment_transactions WHERE status = 'success'";
            $globalAvg = $db->query($globalSql)->fetchColumn();
            
            if ($amount > $globalAvg * 5) {
                return 40; // Very high for first transaction
            }
            return 0;
        }
        
        $avg = $stats['avg_amount'];
        $max = $stats['max_amount'];
        
        // Risk scoring based on deviation
        if ($amount > $max * 2) {
            return 50; // Double previous max
        } elseif ($amount > $avg * 5) {
            return 40;
        } elseif ($amount > $avg * 3) {
            return 25;
        } elseif ($amount > $avg * 2) {
            return 15;
        }
        
        return 0;
    }
    
    /**
     * Check transaction velocity
     */
    private function checkTransactionVelocity(int $userId): int
    {
        $db = $this->database->getConnection();
        
        // Transactions in last hour
        $sql = "SELECT COUNT(*) FROM payment_transactions 
            WHERE user_id = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $count1h = $stmt->fetchColumn();
        
        if ($count1h >= 5) {
            return 35;
        } elseif ($count1h >= 3) {
            return 20;
        }
        
        // Transactions in last 24 hours
        $sql = "SELECT COUNT(*) FROM payment_transactions 
            WHERE user_id = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $count24h = $stmt->fetchColumn();
        
        if ($count24h >= 10) {
            return 25;
        } elseif ($count24h >= 5) {
            return 15;
        }
        
        return 0;
    }
    
    /**
     * Check location risk
     */
    private function checkLocationRisk(array $transaction): int
    {
        $risk = 0;
        $ip = $transaction['ip_address'] ?? '';
        $userId = $transaction['user_id'] ?? 0;
        
        // Check if IP is from different country than usual
        $db = $this->database->getConnection();
        $sql = "SELECT DISTINCT ip_address FROM payment_transactions 
            WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY created_at DESC LIMIT 5";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $previousIPs = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        if (!empty($previousIPs) && !in_array($ip, $previousIPs)) {
            $risk += 15;
        }
        
        return $risk;
    }
    
    /**
     * Check device risk
     */
    private function checkDeviceRisk(array $transaction): int
    {
        $device = $transaction['device_fingerprint'] ?? '';
        $userId = $transaction['user_id'] ?? 0;
        
        if (empty($device)) {
            return 0;
        }
        
        // Check if device is new for this user
        $db = $this->database->getConnection();
        $sql = "SELECT COUNT(*) FROM payment_transactions 
            WHERE user_id = ? AND device_fingerprint = ?
            AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId, $device]);
        $previousUses = $stmt->fetchColumn();
        
        if ($previousUses == 0) {
            return 20; // New device
        }
        
        return 0;
    }
    
    /**
     * Check timing risk
     */
    private function checkTimingRisk(array $transaction): int
    {
        $hour = (int) date('H', strtotime($transaction['created_at'] ?? 'now'));
        
        // Transactions during odd hours (2 AM - 5 AM)
        if ($hour >= 2 && $hour <= 5) {
            return 10;
        }
        
        return 0;
    }
    
    /**
     * Check multiple accounts
     */
    private function checkMultipleAccounts(int $userId, string $userType): int
    {
        $db = $this->database->getConnection();
        
        // Check for accounts with same phone/email
        $sql = "SELECT phone, email FROM users WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) {
            return 0;
        }
        
        // Count accounts with same phone or email
        $checkSql = "SELECT COUNT(*) FROM users 
            WHERE id != ? AND (phone = ? OR email = ?)";
        
        $checkStmt = $db->prepare($checkSql);
        $checkStmt->execute([$userId, $user['phone'], $user['email']]);
        $duplicateCount = $checkStmt->fetchColumn();
        
        if ($duplicateCount >= 3) {
            return 40;
        } elseif ($duplicateCount >= 2) {
            return 25;
        } elseif ($duplicateCount >= 1) {
            return 15;
        }
        
        return 0;
    }
    
    /**
     * Check activity patterns
     */
    private function checkActivityPatterns(int $userId): int
    {
        $db = $this->database->getConnection();
        $risk = 0;
        
        // Check for bot-like behavior (very fast actions)
        $sql = "SELECT COUNT(*) FROM ai_user_behavior 
            WHERE user_id = ? 
            AND time_spent_seconds < 5
            AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $fastActions = $stmt->fetchColumn();
        
        if ($fastActions >= 10) {
            $risk += 30;
        } elseif ($fastActions >= 5) {
            $risk += 15;
        }
        
        return $risk;
    }
    
    /**
     * Check document risk
     */
    private function checkDocumentRisk(int $userId): int
    {
        // Check if documents are pending verification for too long
        $db = $this->database->getConnection();
        
        $sql = "SELECT COUNT(*) FROM documents 
            WHERE entity_type = 'user' AND entity_id = ? AND verification_status = 'pending'
            AND uploaded_on < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$userId]);
        $oldPending = $stmt->fetchColumn();
        
        if ($oldPending > 0) {
            return 10;
        }
        
        return 0;
    }
    
    /**
     * Get risk level
     */
    private function getRiskLevel(int $score): string
    {
        if ($score >= $this->riskThresholds['high']) {
            return 'high';
        } elseif ($score >= $this->riskThresholds['medium']) {
            return 'medium';
        } elseif ($score >= $this->riskThresholds['low']) {
            return 'low';
        }
        return 'low';
    }
    
    /**
     * Create fraud alert
     */
    private function createFraudAlert(string $entityType, int $entityId, int $score, string $level, array $factors): void
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "INSERT INTO ai_fraud_alerts 
                (entity_type, entity_id, alert_type, risk_score, risk_level, indicators)
                VALUES (?, ?, 'automatic_detection', ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $entityType,
                $entityId,
                $score,
                $level,
                json_encode($factors)
            ]);
            
        } catch (\Exception $e) {
            // Silently fail
                    error_log("AIFraudDetectionService.php: " . $e->getMessage());
        }
    }
    
    /**
     * Update user risk score
     */
    private function updateUserRiskScore(int $userId, string $userType, int $score, array $factors): void
    {
        try {
            $db = $this->database->getConnection();
            $level = $this->getRiskLevel($score);
            
            $sql = "INSERT INTO ai_user_risk_scores 
                (user_id, user_type, overall_risk_score, risk_level, factors, last_activity_at)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                overall_risk_score = VALUES(overall_risk_score),
                risk_level = VALUES(risk_level),
                factors = VALUES(factors),
                last_activity_at = VALUES(last_activity_at)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $userId,
                $userType,
                $score,
                $level,
                json_encode($factors)
            ]);
            
        } catch (\Exception $e) {
            // Silently fail
                    error_log("AIFraudDetectionService.php: " . $e->getMessage());
        }
    }
    
    /**
     * Get recommended action
     */
    private function getRecommendedAction(string $riskLevel): string
    {
        $actions = [
            'low' => 'approve',
            'medium' => 'review',
            'high' => 'manual_review',
            'critical' => 'block'
        ];
        
        return $actions[$riskLevel] ?? 'review';
    }
    
    /**
     * Get user recommendation
     */
    private function getUserRecommendation(string $riskLevel): string
    {
        $recommendations = [
            'low' => 'No action needed',
            'medium' => 'Monitor user activity',
            'high' => 'Review account and documents',
            'critical' => 'Suspend account pending investigation'
        ];
        
        return $recommendations[$riskLevel] ?? 'Review required';
    }
    
    /**
     * Get pending fraud alerts
     */
    public function getPendingAlerts(int $limit = 50): array
    {
        try {
            $db = $this->database->getConnection();
            
            $sql = "SELECT * FROM ai_fraud_alerts 
                WHERE status IN ('new', 'investigating')
                ORDER BY risk_score DESC, created_at DESC
                LIMIT ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$limit]);
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get fraud statistics
     */
    public function getFraudStats(): array
    {
        try {
            $db = $this->database->getConnection();
            
            $stats = [];
            
            // By status
            $statusSql = "SELECT status, COUNT(*) as count FROM ai_fraud_alerts GROUP BY status";
            $stats['by_status'] = $db->query($statusSql)->fetchAll(\PDO::FETCH_ASSOC);
            
            // By risk level
            $riskSql = "SELECT risk_level, COUNT(*) as count FROM ai_fraud_alerts GROUP BY risk_level";
            $stats['by_risk'] = $db->query($riskSql)->fetchAll(\PDO::FETCH_ASSOC);
            
            // Last 30 days
            $recentSql = "SELECT COUNT(*) FROM ai_fraud_alerts 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stats['last_30_days'] = $db->query($recentSql)->fetchColumn();
            
            return $stats;
            
        } catch (\Exception $e) {
            return [];
        }
    }
}?>