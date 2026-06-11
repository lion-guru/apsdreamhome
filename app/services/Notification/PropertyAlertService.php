<?php

namespace App\Services\Notification;

use App\Core\Database\Database;

/**
 * Property Alert & Notification Service
 * Automated alerts for new properties matching saved searches
 */
class PropertyAlertService
{
    private $database;
    
    public function __construct()
    {
        $this->database = Database::getInstance();
        $this->ensureTablesExist();
    }
    
    /**
     * Ensure tables exist
     */
    private function ensureTablesExist(): void
    {
        $pdo = $this->database->getConnection();
        
        // Property alerts table
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Alert matches log
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
        
        // Notification queue
        // $pdo->exec("ENGINE=InnoDB..."); // Managed by migrations
    }
    
    /**
     * Create property alert
     */
    public function createAlert(int $userId, array $criteria): array
    {
        try {
            $sql = "INSERT INTO property_alerts 
                (user_id, alert_name, property_type, location, city, 
                 min_price, max_price, min_area, max_area, bedrooms, 
                 furnishing, amenities, frequency) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->database->prepare($sql);
            $stmt->execute([
                $userId,
                $criteria['name'] ?? 'My Property Alert',
                $criteria['type'] ?? null,
                $criteria['location'] ?? null,
                $criteria['city'] ?? null,
                $criteria['min_price'] ?? null,
                $criteria['max_price'] ?? null,
                $criteria['min_area'] ?? null,
                $criteria['max_area'] ?? null,
                $criteria['bedrooms'] ?? null,
                $criteria['furnishing'] ?? null,
                !empty($criteria['amenities']) ? json_encode($criteria['amenities']) : null,
                $criteria['frequency'] ?? 'daily'
            ]);
            
            $alertId = $this->database->lastInsertId();
            
            // Run initial match
            $matches = $this->checkMatches($alertId);
            
            return [
                'success' => true,
                'alert_id' => $alertId,
                'initial_matches' => count($matches),
                'message' => 'Alert created successfully'
            ];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get user's alerts
     */
    public function getUserAlerts(int $userId): array
    {
        $sql = "SELECT pa.*, 
            COUNT(am.id) as total_matches,
            SUM(CASE WHEN am.notification_sent = 1 THEN 1 ELSE 0 END) as notified_matches
            FROM property_alerts pa
            LEFT JOIN alert_matches am ON pa.id = am.alert_id
            WHERE pa.user_id = ?
            GROUP BY pa.id
            ORDER BY pa.created_at DESC";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$userId]);
        
        $alerts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach ($alerts as &$alert) {
            $alert['amenities'] = json_decode($alert['amenities'], true);
        }
        
        return $alerts;
    }
    
    /**
     * Check for matching properties
     */
    public function checkMatches(int $alertId, bool $notify = false): array
    {
        // Get alert criteria
        $alertSql = "SELECT * FROM property_alerts WHERE id = ? AND is_active = 1";
        $alertStmt = $this->database->prepare($alertSql);
        $alertStmt->execute([$alertId]);
        $alert = $alertStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$alert) {
            return [];
        }
        
        // Build match query
        $where = ['p.status = ?', 'p.created_at >= ?'];
        $params = ['available', $alert['created_at']];
        
        if ($alert['property_type']) {
            $where[] = 'p.type = ?';
            $params[] = $alert['property_type'];
        }
        
        if ($alert['city']) {
            $where[] = 'p.city = ?';
            $params[] = $alert['city'];
        }
        
        if ($alert['location']) {
            $where[] = '(p.location LIKE ? OR p.locality LIKE ?)';
            $params[] = '%' . $alert['location'] . '%';
            $params[] = '%' . $alert['location'] . '%';
        }
        
        if ($alert['min_price']) {
            $where[] = 'p.price >= ?';
            $params[] = $alert['min_price'];
        }
        
        if ($alert['max_price']) {
            $where[] = 'p.price <= ?';
            $params[] = $alert['max_price'];
        }
        
        if ($alert['bedrooms']) {
            $where[] = 'p.bedrooms = ?';
            $params[] = $alert['bedrooms'];
        }
        
        if ($alert['furnishing']) {
            $where[] = 'p.furnishing_status = ?';
            $params[] = $alert['furnishing'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Find matching properties not already matched
        $sql = "SELECT p.*, pi.image_path as primary_image
            FROM properties p
            LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
            WHERE {$whereClause}
            AND p.id NOT IN (SELECT property_id FROM alert_matches WHERE alert_id = ?)
            ORDER BY p.created_at DESC";
        
        $params[] = $alertId;
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute($params);
        $matches = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Log matches
        $insertSql = "INSERT IGNORE INTO alert_matches (alert_id, property_id, match_score) VALUES (?, ?, ?)";
        $insertStmt = $this->database->prepare($insertSql);
        
        foreach ($matches as $property) {
            $score = $this->calculateMatchScore($alert, $property);
            $insertStmt->execute([$alertId, $property['id'], $score]);
        }
        
        // Update total matches count
        $this->updateAlertStats($alertId);
        
        // Send notifications if requested
        if ($notify && !empty($matches)) {
            $this->sendAlertNotification($alertId, $matches);
        }
        
        return $matches;
    }
    
    /**
     * Calculate match score
     */
    private function calculateMatchScore(array $alert, array $property): float
    {
        $score = 1.0;
        
        // Exact location match
        if ($alert['location'] && stripos($property['location'], $alert['location']) !== false) {
            $score += 0.2;
        }
        
        // Price within range (closer = higher score)
        if ($alert['min_price'] && $alert['max_price']) {
            $midPrice = ($alert['min_price'] + $alert['max_price']) / 2;
            $diff = abs($property['price'] - $midPrice) / $midPrice;
            $score += max(0, 0.2 - $diff);
        }
        
        // Newer properties get slight boost
        $daysOld = (strtotime('now') - strtotime($property['created_at'])) / 86400;
        if ($daysOld < 7) {
            $score += 0.1;
        }
        
        return min($score, 1.5);
    }
    
    /**
     * Process all pending alerts
     */
    public function processPendingAlerts(): array
    {
        $sql = "SELECT * FROM property_alerts 
            WHERE is_active = 1 
            AND (
                last_sent IS NULL 
                OR (frequency = 'daily' AND last_sent < DATE_SUB(NOW(), INTERVAL 1 DAY))
                OR (frequency = 'weekly' AND last_sent < DATE_SUB(NOW(), INTERVAL 7 DAY))
            )";
        
        $alerts = $this->database->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        
        $processed = 0;
        $notifications = 0;
        
        foreach ($alerts as $alert) {
            $matches = $this->checkMatches($alert['id'], true);
            
            if (!empty($matches)) {
                $notifications++;
            }
            
            // Update last_sent
            $updateSql = "UPDATE property_alerts SET last_sent = NOW() WHERE id = ?";
            $updateStmt = $this->database->prepare($updateSql);
            $updateStmt->execute([$alert['id']]);
            
            $processed++;
        }
        
        return [
            'processed' => $processed,
            'notifications_sent' => $notifications
        ];
    }
    
    /**
     * Send alert notification
     */
    private function sendAlertNotification(int $alertId, array $matches): void
    {
        // Get user details
        $userSql = "SELECT pa.*, u.email, u.phone, u.name 
            FROM property_alerts pa
            JOIN users u ON pa.user_id = u.id
            WHERE pa.id = ?";
        
        $userStmt = $this->database->prepare($userSql);
        $userStmt->execute([$alertId]);
        $user = $userStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$user) return;
        
        $matchCount = count($matches);
        
        // Queue notification
        $title = "{$matchCount} New Properties Matching Your Alert";
        $message = "Hi {$user['name']}, we found {$matchCount} new properties that match your alert '{$user['alert_name']}'. ";
        $message .= "Check them out before they're gone!";
        
        $this->queueNotification(
            $user['user_id'],
            'email',
            $title,
            $message,
            ['alert_id' => $alertId, 'matches' => array_column($matches, 'id')]
        );
        
        // Also send WhatsApp if phone available
        if ($user['phone']) {
            $this->queueNotification(
                $user['user_id'],
                'whatsapp',
                $title,
                $message,
                ['alert_id' => $alertId]
            );
        }
        
        // Mark matches as notified
        $matchIds = array_column($matches, 'id');
        $updateSql = "UPDATE alert_matches SET notification_sent = 1, sent_at = NOW() 
            WHERE alert_id = ? AND property_id IN (" . implode(',', $matchIds) . ")";
        $updateStmt = $this->database->prepare($updateSql);
        $updateStmt->execute([$alertId]);
    }
    
    /**
     * Queue notification
     */
    public function queueNotification(int $userId, string $type, string $title, string $message, ?array $data = null): int
    {
        $sql = "INSERT INTO notification_queue 
            (user_id, type, title, message, data, scheduled_at) 
            VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([
            $userId,
            $type,
            $title,
            $message,
            $data ? json_encode($data) : null,
            date('Y-m-d H:i:s')
        ]);
        
        return $this->database->lastInsertId();
    }
    
    /**
     * Update alert stats
     */
    private function updateAlertStats(int $alertId): void
    {
        $sql = "UPDATE property_alerts 
            SET total_matches = (SELECT COUNT(*) FROM alert_matches WHERE alert_id = ?)
            WHERE id = ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$alertId, $alertId]);
    }
    
    /**
     * Delete alert
     */
    public function deleteAlert(int $alertId, int $userId): array
    {
        $sql = "DELETE FROM property_alerts WHERE id = ? AND user_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$alertId, $userId]);
        
        return [
            'success' => $stmt->rowCount() > 0,
            'message' => $stmt->rowCount() > 0 ? 'Alert deleted' : 'Alert not found'
        ];
    }
    
    /**
     * Get popular alert criteria
     */
    public function getPopularCriteria(int $limit = 10): array
    {
        $sql = "SELECT 
            property_type, city, location,
            COUNT(*) as alert_count,
            AVG(max_price) as avg_max_price
            FROM property_alerts 
            WHERE is_active = 1
            GROUP BY property_type, city
            ORDER BY alert_count DESC
            LIMIT ?";
        
        $stmt = $this->database->prepare($sql);
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
