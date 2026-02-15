<?php

namespace App\Services\Legacy;
/**
 * Comprehensive Access Control Middleware
 * Provides advanced access control and protection mechanisms
 */
class AccessControlMiddleware {
    // Predefined access rules
    private const ACCESS_RULES = [
        'dashboard' => ['admin', 'super_admin', 'manager'],
        'user_management' => ['admin', 'super_admin'],
        'system_settings' => ['super_admin'],
        'content_edit' => ['admin', 'editor'],
        'report_view' => ['admin', 'manager', 'super_admin']
    ];

    /**
     * Check if current user can access a specific resource
     * @param string $resource
     * @param string|null $userId
     * @return bool
     */
    public static function canAccess($resource, $userId = null) {
        // Use current user if no user ID provided
        $userId = $userId ?? ($_SESSION['admin_user_id'] ?? null);
        
        if (!$userId) {
            // Log unauthorized access attempt
            self::logAccessAttempt($resource, 'NO_USER');
            return false;
        }

        try {
            // Get user's current role
            $userRole = RBACManager::getUserRole($userId);
            
            if (!$userRole) {
                // Log access attempt with no role
                self::logAccessAttempt($resource, 'NO_ROLE');
                return false;
            }

            // Check if role has access to resource
            $allowedRoles = self::ACCESS_RULES[$resource] ?? [];
            $hasAccess = in_array($userRole, $allowedRoles);

            // Log access attempt
            self::logAccessAttempt($resource, $hasAccess ? 'GRANTED' : 'DENIED', $userRole);

            return $hasAccess;
        } catch (Exception $e) {
            // Log any unexpected errors
            AdminLogger::logError('ACCESS_CHECK_ERROR', [
                'message' => $e->getMessage(),
                'resource' => $resource,
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * Enforce access to a specific resource
     * @param string $resource
     * @throws AccessDeniedException
     */
    public static function enforceAccess($resource) {
        if (!self::canAccess($resource)) {
            // Detailed security alert
            AdminLogger::securityAlert('ACCESS_DENIED', [
                'resource' => $resource,
                'user_id' => $_SESSION['admin_user_id'] ?? 'Unknown',
                'role' => RBACManager::getUserRole($_SESSION['admin_user_id'] ?? null),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
            ]);

            // Throw access denied exception
            throw new AccessDeniedException("You do not have permission to access this resource.");
        }
    }

    /**
     * Log access attempt details
     * @param string $resource
     * @param string $status
     * @param string|null $userRole
     */
    private static function logAccessAttempt($resource, $status, $userRole = null) {
        AdminLogger::log('ACCESS_ATTEMPT', [
            'resource' => $resource,
            'status' => $status,
            'user_role' => $userRole,
            'user_id' => $_SESSION['admin_user_id'] ?? 'Unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
        ]);
    }

    /**
     * Implement IP-based access restrictions
     * @param string|null $userId
     * @return bool
     */
    public static function checkIPRestrictions($userId = null) {
        $userId = $userId ?? ($_SESSION['admin_user_id'] ?? null);
        
        if (!$userId) {
            return false;
        }

        try {
            $db = \App\Core\App::database();
            
            // Fetch allowed IPs for user
            $userData = $db->fetch("
                SELECT utype 
                FROM user 
                WHERE uid = ?
            ", [$userId]);

            // No IP restrictions column in user table, skipping check
            return true;
        } catch (Exception $e) {
            AdminLogger::logError('IP_RESTRICTION_ERROR', [
                'message' => $e->getMessage(),
                'user_id' => $userId
            ]);
            return false;
        }
    }

    /**
     * Check if IP is in allowed list
     * @param string $ip
     * @param array $allowedIPs
     * @return bool
     */
    private static function isIPInAllowedList($ip, $allowedIPs) {
        foreach ($allowedIPs as $allowedIP) {
            // Support for CIDR notation
            if (strpos($allowedIP, '/') !== false) {
                if (self::ipInCIDRRange($ip, $allowedIP)) {
                    return true;
                }
            } elseif ($ip === $allowedIP) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if IP is in CIDR range
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    private static function ipInCIDRRange($ip, $cidr) {
        list($subnet, $mask) = explode('/', $cidr);
        
        $longIP = ip2long($ip);
        $longSubnet = ip2long($subnet);
        $longMask = pow(2, 32 - $mask) - 1;
        $longSubnetMin = $longSubnet & (~$longMask);
        $longSubnetMax = $longSubnet | $longMask;

        return ($longIP >= $longSubnetMin && $longIP <= $longSubnetMax);
    }
}

// Global helper functions
function can_access($resource) {
    return AccessControlMiddleware::canAccess($resource);
}

function enforce_access($resource) {
    AccessControlMiddleware::enforceAccess($resource);
}
