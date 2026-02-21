<?php

namespace App\Models;

use App\Core\Database;
use DateTime;

/**
 * Property Comparison Tool Model
 * Handles property comparison sessions, criteria, and analytics
 */
class PropertyComparison extends Model
{
    protected $table = 'property_comparison_sessions';

    /**
     * Create a new comparison session
     */
    public function createComparisonSession(array $sessionData): array
    {
        $sessionKey = $this->generateSessionKey();

        $sessionRecord = [
            'session_key' => $sessionKey,
            'user_id' => $sessionData['user_id'] ?? null,
            'user_type' => $sessionData['user_type'] ?? 'guest',
            'session_name' => $sessionData['session_name'] ?? 'Property Comparison',
            'max_properties' => $sessionData['max_properties'] ?? 4,
            'comparison_criteria' => json_encode($sessionData['comparison_criteria'] ?? $this->getDefaultCriteria()),
            'is_active' => 1,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'device_info' => json_encode($this->getDeviceInfo()),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $sessionId = $this->insert($sessionRecord);

        // Log session creation
        $this->logComparisonEvent($sessionId, null, 'session_created', [
            'session_name' => $sessionRecord['session_name'],
            'criteria_count' => count($sessionData['comparison_criteria'] ?? [])
        ]);

        return [
            'success' => true,
            'session_id' => $sessionId,
            'session_key' => $sessionKey,
            'message' => 'Comparison session created successfully'
        ];
    }

    /**
     * Add property to comparison session
     */
    public function addPropertyToSession(int $sessionId, int $propertyId): array
    {
        $session = $this->find($sessionId);
        if (!$session) {
            return ['success' => false, 'message' => 'Comparison session not found'];
        }

        if (!$session['is_active']) {
            return ['success' => false, 'message' => 'Comparison session is not active'];
        }

        // Check if property already in session
        $existing = $this->query(
            "SELECT id FROM comparison_session_properties WHERE session_id = ? AND property_id = ?",
            [$sessionId, $propertyId]
        )->fetch();

        if ($existing) {
            return ['success' => false, 'message' => 'Property already in comparison session'];
        }

        // Check session limit
        $currentCount = $this->query(
            "SELECT COUNT(*) as count FROM comparison_session_properties WHERE session_id = ?",
            [$sessionId]
        )->fetch()['count'];

        if ($currentCount >= $session['max_properties']) {
            return ['success' => false, 'message' => 'Maximum properties limit reached for this session'];
        }

        $propertyRecord = [
            'session_id' => $sessionId,
            'property_id' => $propertyId,
            'added_at' => date('Y-m-d H:i:s'),
            'sort_order' => $currentCount + 1
        ];

        $this->insertInto('comparison_session_properties', $propertyRecord);

        // Log property addition
        $this->logComparisonEvent($sessionId, $propertyId, 'property_added', [
            'sort_order' => $propertyRecord['sort_order']
        ]);

        return [
            'success' => true,
            'message' => 'Property added to comparison successfully'
        ];
    }

    /**
     * Remove property from comparison session
     */
    public function removePropertyFromSession(int $sessionId, int $propertyId): array
    {
        $result = $this->query(
            "DELETE FROM comparison_session_properties WHERE session_id = ? AND property_id = ?",
            [$sessionId, $propertyId]
        );

        if ($result) {
            // Log property removal
            $this->logComparisonEvent($sessionId, $propertyId, 'property_removed');

            return [
                'success' => true,
                'message' => 'Property removed from comparison successfully'
            ];
        }

        return [
            'success' => false,
            'message' => 'Property not found in comparison session'
        ];
    }

    /**
     * Get comparison session with properties
     */
    public function getComparisonSession(string $sessionKey): ?array
    {
        $session = $this->query(
            "SELECT * FROM property_comparison_sessions WHERE session_key = ? AND is_active = 1",
            [$sessionKey]
        )->fetch();

        if (!$session) {
            return null;
        }

        // Check if session expired
        if ($session['expires_at'] && strtotime($session['expires_at']) < time()) {
            $this->update($session['id'], ['is_active' => 0]);
            return null;
        }

        // Get properties in session
        $properties = $this->getSessionProperties($session['id']);

        // Get comparison data
        $comparisonData = $this->getComparisonData($properties, json_decode($session['comparison_criteria'], true));

        $session['properties'] = $properties;
        $session['comparison_data'] = $comparisonData;
        $session['comparison_criteria'] = json_decode($session['comparison_criteria'], true);

        return $session;
    }

    /**
     * Get properties in a session
     */
    private function getSessionProperties(int $sessionId): array
    {
        $properties = $this->query(
            "SELECT p.*, csp.sort_order
             FROM comparison_session_properties csp
             LEFT JOIN properties p ON csp.property_id = p.id
             WHERE csp.session_id = ? AND p.status = 'available'
             ORDER BY csp.sort_order ASC",
            [$sessionId]
        )->fetchAll();

        return $properties;
    }

    /**
     * Get comparison data for properties
     */
    private function getComparisonData(array $properties, array $criteria): array
    {
        $comparisonData = [];
        $propertyIds = array_column($properties, 'id');

        foreach ($criteria as $criteriaKey) {
            $criteriaInfo = $this->getCriteriaInfo($criteriaKey);

            $comparisonData[$criteriaKey] = [
                'criteria_name' => $criteriaInfo['criteria_name'] ?? $criteriaKey,
                'data_type' => $criteriaInfo['data_type'] ?? 'text',
                'display_format' => $criteriaInfo['display_format'] ?? null,
                'values' => []
            ];

            foreach ($propertyIds as $propertyId) {
                $value = $this->getPropertyCriteriaValue($propertyId, $criteriaKey);
                $comparisonData[$criteriaKey]['values'][$propertyId] = $value;
            }
        }

        return $comparisonData;
    }

    /**
     * Get criteria information
     */
    private function getCriteriaInfo(string $criteriaKey): ?array
    {
        return $this->query(
            "SELECT * FROM comparison_criteria WHERE criteria_key = ? AND is_active = 1",
            [$criteriaKey]
        )->fetch();
    }

    /**
     * Get property criteria value
     */
    private function getPropertyCriteriaValue(int $propertyId, string $criteriaKey): mixed
    {
        // First check if we have stored comparison data
        $storedData = $this->query(
            "SELECT pcd.criteria_value, cc.data_type
             FROM property_comparison_data pcd
             LEFT JOIN comparison_criteria cc ON pcd.criteria_id = cc.id
             WHERE pcd.property_id = ? AND cc.criteria_key = ?",
            [$propertyId, $criteriaKey]
        )->fetch();

        if ($storedData) {
            return $this->formatCriteriaValue($storedData['criteria_value'], $storedData['data_type']);
        }

        // Fallback to property data
        return $this->getPropertyFieldValue($propertyId, $criteriaKey);
    }

    /**
     * Get property field value
     */
    private function getPropertyFieldValue(int $propertyId, string $criteriaKey): mixed
    {
        $property = $this->query("SELECT * FROM properties WHERE id = ?", [$propertyId])->fetch();

        if (!$property) return null;

        // Map criteria keys to property fields
        $fieldMapping = [
            'property_title' => 'title',
            'property_type' => 'property_type_id',
            'price' => 'price',
            'area_sqft' => 'area',
            'bedrooms' => 'bedrooms',
            'bathrooms' => 'bathrooms',
            'location' => 'location',
            'city' => 'city',
            'furnishing_status' => 'furnishing',
            'parking_available' => 'parking',
            'age_years' => 'age',
            'floor_number' => 'floor',
            'total_floors' => 'total_floors',
            'possession_status' => 'possession',
            'maintenance_cost' => 'maintenance'
        ];

        $fieldName = $fieldMapping[$criteriaKey] ?? $criteriaKey;

        if (isset($property[$fieldName])) {
            // Special handling for some fields
            switch ($criteriaKey) {
                case 'property_type':
                    // Get property type name
                    $type = $this->query("SELECT type FROM property_types WHERE id = ?", [$property[$fieldName]])->fetch();
                    return $type ? $type['type'] : $property[$fieldName];
                case 'furnishing_status':
                    return ucfirst($property[$fieldName] ?? 'unfurnished');
                case 'parking_available':
                    return $property[$fieldName] ? 'Yes' : 'No';
                default:
                    return $property[$fieldName];
            }
        }

        return null;
    }

    /**
     * Format criteria value based on data type
     */
    private function formatCriteriaValue(mixed $value, string $dataType): mixed
    {
        if ($value === null) return null;

        switch ($dataType) {
            case 'currency':
                return '₹' . number_format((float)$value, 0, '.', ',');
            case 'number':
                return (float)$value;
            case 'boolean':
                return (bool)$value ? 'Yes' : 'No';
            case 'date':
                return date('d/m/Y', strtotime($value));
            case 'list':
                $list = json_decode($value, true);
                return is_array($list) ? implode(', ', $list) : $value;
            default:
                return $value;
        }
    }

    /**
     * Update comparison criteria for session
     */
    public function updateSessionCriteria(int $sessionId, array $criteria): array
    {
        $this->update($sessionId, [
            'comparison_criteria' => json_encode($criteria),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log criteria change
        $this->logComparisonEvent($sessionId, null, 'criteria_changed', [
            'criteria_count' => count($criteria)
        ]);

        return [
            'success' => true,
            'message' => 'Comparison criteria updated successfully'
        ];
    }

    /**
     * Save comparison as template
     */
    public function saveComparison(int $sessionId, array $saveData): array
    {
        $session = $this->find($sessionId);
        if (!$session) {
            return ['success' => false, 'message' => 'Comparison session not found'];
        }

        // Get properties in session
        $properties = $this->query(
            "SELECT property_id FROM comparison_session_properties WHERE session_id = ? ORDER BY sort_order ASC",
            [$sessionId]
        )->fetchAll();

        $propertyIds = array_column($properties, 'property_id');

        $savedComparison = [
            'user_id' => $saveData['user_id'] ?? $session['user_id'],
            'user_type' => $saveData['user_type'] ?? $session['user_type'],
            'comparison_name' => $saveData['comparison_name'] ?? $session['session_name'],
            'comparison_description' => $saveData['comparison_description'] ?? null,
            'property_ids' => json_encode($propertyIds),
            'comparison_criteria' => $session['comparison_criteria'],
            'is_public' => $saveData['is_public'] ?? 0,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $comparisonId = $this->insertInto('saved_comparisons', $savedComparison);

        return [
            'success' => true,
            'comparison_id' => $comparisonId,
            'message' => 'Comparison saved successfully'
        ];
    }

    /**
     * Get saved comparisons for user
     */
    public function getSavedComparisons(int $userId, string $userType, int $limit = 20): array
    {
        return $this->query(
            "SELECT * FROM saved_comparisons
             WHERE user_id = ? AND user_type = ?
             ORDER BY created_at DESC LIMIT ?",
            [$userId, $userType, $limit]
        )->fetchAll();
    }

    /**
     * Load saved comparison
     */
    public function loadSavedComparison(int $comparisonId, int $userId, string $userType): ?array
    {
        $comparison = $this->query(
            "SELECT * FROM saved_comparisons WHERE id = ? AND (user_id = ? OR is_public = 1)",
            [$comparisonId, $userId]
        )->fetch();

        if (!$comparison) {
            return null;
        }

        $propertyIds = json_decode($comparison['property_ids'], true);
        $criteria = json_decode($comparison['comparison_criteria'], true);

        // Create new session with saved data
        $sessionResult = $this->createComparisonSession([
            'user_id' => $userId,
            'user_type' => $userType,
            'session_name' => $comparison['comparison_name'],
            'comparison_criteria' => $criteria,
            'max_properties' => count($propertyIds)
        ]);

        if ($sessionResult['success']) {
            // Add properties to session
            foreach ($propertyIds as $propertyId) {
                $this->addPropertyToSession($sessionResult['session_id'], $propertyId);
            }

            return $this->getComparisonSession($sessionResult['session_key']);
        }

        return null;
    }

    /**
     * Get comparison criteria list
     */
    public function getComparisonCriteria(): array
    {
        $criteria = $this->query(
            "SELECT * FROM comparison_criteria WHERE is_active = 1 ORDER BY criteria_group ASC, sort_order ASC"
        )->fetchAll();

        // Group by category
        $groupedCriteria = [];
        foreach ($criteria as $item) {
            $group = $item['criteria_group'];
            if (!isset($groupedCriteria[$group])) {
                $groupedCriteria[$group] = [];
            }
            $groupedCriteria[$group][] = $item;
        }

        return $groupedCriteria;
    }

    /**
     * Get default comparison criteria
     */
    private function getDefaultCriteria(): array
    {
        $defaults = $this->query(
            "SELECT criteria_key FROM comparison_criteria WHERE is_default = 1 AND is_active = 1 ORDER BY sort_order ASC"
        )->fetchAll();

        return array_column($defaults, 'criteria_key');
    }

    /**
     * Generate session key
     */
    private function generateSessionKey(): string
    {
        do {
            $key = 'cmp_' . bin2hex(random_bytes(8));
            $exists = $this->query("SELECT id FROM property_comparison_sessions WHERE session_key = ?", [$key])->fetch();
        } while ($exists);

        return $key;
    }

    /**
     * Get device information
     */
    private function getDeviceInfo(): array
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return [
            'user_agent' => $userAgent,
            'device_type' => $this->detectDeviceType($userAgent),
            'browser' => $this->detectBrowser($userAgent),
            'platform' => $this->detectPlatform($userAgent),
            'screen_resolution' => $_COOKIE['screen_resolution'] ?? null
        ];
    }

    /**
     * Log comparison event
     */
    private function logComparisonEvent(int $sessionId, ?int $propertyId, string $eventType, array $eventData = []): void
    {
        $session = $this->find($sessionId);
        $properties = $this->query(
            "SELECT property_id FROM comparison_session_properties WHERE session_id = ?",
            [$sessionId]
        )->fetchAll();

        $logData = [
            'session_id' => $sessionId,
            'user_id' => $session['user_id'] ?? null,
            'user_type' => $session['user_type'] ?? 'guest',
            'event_type' => $eventType,
            'event_data' => json_encode($eventData),
            'properties_compared' => json_encode(array_column($properties, 'property_id')),
            'criteria_used' => $session['comparison_criteria'] ?? null,
            'device_type' => $this->detectDeviceType($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->insertInto('comparison_analytics', $logData);
    }

    /**
     * Detect device type
     */
    private function detectDeviceType(string $userAgent): string
    {
        if (preg_match('/mobile/i', $userAgent)) {
            return 'mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    /**
     * Detect browser
     */
    private function detectBrowser(string $userAgent): string
    {
        if (preg_match('/chrome/i', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/safari/i', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/edge/i', $userAgent)) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Detect platform
     */
    private function detectPlatform(string $userAgent): string
    {
        if (preg_match('/windows/i', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/mac/i', $userAgent)) {
            return 'Mac';
        } elseif (preg_match('/linux/i', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/android/i', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/ios/i', $userAgent)) {
            return 'iOS';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get comparison analytics
     */
    public function getComparisonAnalytics(string $period = '30 days'): array
    {
        $startDate = date('Y-m-d', strtotime("-{$period}"));

        $analytics = $this->query(
            "SELECT
                COUNT(DISTINCT session_id) as total_sessions,
                COUNT(*) as total_events,
                COUNT(CASE WHEN event_type = 'property_added' THEN 1 END) as properties_added,
                COUNT(CASE WHEN event_type = 'comparison_viewed' THEN 1 END) as comparisons_viewed,
                COUNT(DISTINCT user_id) as unique_users,
                AVG(JSON_LENGTH(properties_compared)) as avg_properties_compared
             FROM comparison_analytics
             WHERE created_at >= ?",
            [$startDate]
        )->fetch();

        // Get popular criteria
        $popularCriteria = $this->query(
            "SELECT JSON_UNQUOTE(JSON_EXTRACT(event_data, '$.criteria_count')) as criteria_count,
                    COUNT(*) as usage_count
             FROM comparison_analytics
             WHERE event_type = 'criteria_changed' AND created_at >= ?
             GROUP BY criteria_count
             ORDER BY usage_count DESC LIMIT 5",
            [$startDate]
        )->fetchAll();

        $analytics['popular_criteria_counts'] = $popularCriteria;

        return $analytics ?: [
            'total_sessions' => 0,
            'total_events' => 0,
            'properties_added' => 0,
            'comparisons_viewed' => 0,
            'unique_users' => 0,
            'avg_properties_compared' => 0,
            'popular_criteria_counts' => []
        ];
    }

    /**
     * Clean up expired sessions
     */
    public function cleanupExpiredSessions(): array
    {
        $expiredCount = $this->query(
            "UPDATE property_comparison_sessions SET is_active = 0 WHERE expires_at < NOW() AND is_active = 1"
        );

        return [
            'success' => true,
            'expired_sessions' => $expiredCount,
            'message' => "Cleaned up {$expiredCount} expired sessions"
        ];
    }
}
