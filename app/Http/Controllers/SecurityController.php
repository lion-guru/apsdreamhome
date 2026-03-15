<?php

/**
 * Controller for Security Management operations
 */

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Services\SecurityService;
use App\Core\Security;
use Exception;

class SecurityController extends BaseController
{
    private $securityService;

    public function __construct()
    {
        parent::__construct();
        $this->securityService = new SecurityService();
    }

    /**
     * Run comprehensive security tests
     */
    public function runTests()
    {
        try {
            $results = $this->securityService->runSecurityTests();

            return $this->jsonResponse([
                'success' => true,
                'results' => $results
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to run security tests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get security dashboard
     */
    public function dashboard()
    {
        $this->requireLogin();

        try {
            // Get security statistics
            $securityStats = $this->getSecurityStatistics();

            // Get recent security events
            $recentEvents = $this->getRecentSecurityEvents();

            // Get system vulnerabilities
            $vulnerabilities = $this->getSystemVulnerabilities();

            $this->render('pages/security-dashboard', [
                'page_title' => 'Security Dashboard - APS Dream Home',
                'page_description' => 'Monitor and manage system security',
                'security_stats' => $securityStats,
                'recent_events' => $recentEvents,
                'vulnerabilities' => $vulnerabilities
            ]);
        } catch (Exception $e) {
            error_log("Security Dashboard Error: " . $e->getMessage());
            $this->render('pages/security-dashboard', [
                'page_title' => 'Security Dashboard - APS Dream Home',
                'page_description' => 'Monitor and manage system security',
                'error' => 'Failed to load security data'
            ]);
        }
    }

    /**
     * Get security statistics
     */
    private function getSecurityStatistics()
    {
        try {
            // Get total security events
            $totalEvents = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM security_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );

            // Get blocked attempts
            $blockedAttempts = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM security_events WHERE action = 'blocked' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );

            // Get failed logins
            $failedLogins = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM security_events WHERE action = 'login_failed' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );

            // Get suspicious activities
            $suspiciousActivities = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM security_events WHERE severity = 'high' AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
            );

            return [
                'total_events' => $totalEvents['count'] ?? 0,
                'blocked_attempts' => $blockedAttempts['count'] ?? 0,
                'failed_logins' => $failedLogins['count'] ?? 0,
                'suspicious_activities' => $suspiciousActivities['count'] ?? 0,
                'security_score' => $this->calculateSecurityScore($totalEvents['count'] ?? 0, $blockedAttempts['count'] ?? 0, $failedLogins['count'] ?? 0)
            ];
        } catch (Exception $e) {
            error_log("Security Statistics Error: " . $e->getMessage());
            return [
                'total_events' => 0,
                'blocked_attempts' => 0,
                'failed_logins' => 0,
                'suspicious_activities' => 0,
                'security_score' => 0
            ];
        }
    }

    /**
     * Get recent security events
     */
    private function getRecentSecurityEvents()
    {
        try {
            $events = $this->db->fetchAll(
                "SELECT 
                    se.action,
                    se.description,
                    se.ip_address,
                    se.user_agent,
                    se.severity,
                    se.created_at
                 FROM security_events se 
                 ORDER BY se.created_at DESC 
                 LIMIT 20"
            );

            return $events;
        } catch (Exception $e) {
            error_log("Recent Security Events Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get system vulnerabilities
     */
    private function getSystemVulnerabilities()
    {
        try {
            $vulnerabilities = $this->db->fetchAll(
                "SELECT 
                    v.type,
                    v.description,
                    v.severity,
                    v.status,
                    v.created_at
                 FROM vulnerabilities v 
                 WHERE v.status = 'open' 
                 ORDER BY v.severity DESC, v.created_at DESC 
                 LIMIT 10"
            );

            return $vulnerabilities;
        } catch (Exception $e) {
            error_log("System Vulnerabilities Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate security score
     */
    private function calculateSecurityScore($totalEvents, $blockedAttempts, $failedLogins)
    {
        // Base score
        $score = 100;

        // Deduct points for security issues
        $score -= min($failedLogins * 5, 50); // Max 50 points for failed logins
        $score -= min($blockedAttempts * 2, 30); // Max 30 points for blocked attempts
        $score -= min($totalEvents * 0.5, 20); // Max 20 points for total events

        return max($score, 0);
    }

    /**
     * Block IP address
     */
    public function blockIP()
    {
        $this->requireLogin();

        try {
            $data = $this->getRequestData();

            $blockData = [
                'ip_address' => Security::sanitize($data['ip_address'] ?? ''),
                'reason' => Security::sanitize($data['reason'] ?? 'Manual block'),
                'duration' => intval($data['duration'] ?? 24),
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Add to blocked IPs
            $this->db->execute(
                "INSERT INTO blocked_ips (ip_address, reason, duration, created_at) VALUES (?, ?, ?)",
                [
                    $blockData['ip_address'],
                    $blockData['reason'],
                    $blockData['duration'],
                    $blockData['created_at']
                ]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'IP address blocked successfully',
                'block_data' => $blockData
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to block IP address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unblock IP address
     */
    public function unblockIP()
    {
        $this->requireLogin();

        try {
            $data = $this->getRequestData();

            // Remove from blocked IPs
            $this->db->execute(
                "DELETE FROM blocked_ips WHERE ip_address = ?",
                [Security::sanitize($data['ip_address'] ?? '')]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'IP address unblocked successfully'
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to unblock IP address: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get blocked IPs
     */
    public function getBlockedIPs()
    {
        $this->requireLogin();

        try {
            $blockedIPs = $this->db->fetchAll(
                "SELECT 
                    bi.ip_address,
                    bi.reason,
                    bi.duration,
                    bi.created_at
                 FROM blocked_ips bi 
                 ORDER BY bi.created_at DESC 
                 LIMIT 50"
            );

            return $this->jsonResponse([
                'success' => true,
                'blocked_ips' => $blockedIPs
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get blocked IPs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get request data from various sources
     */
    private function getRequestData(): array
    {
        $data = [];

        // Get JSON data
        $input = file_get_contents('php://input');
        if (!empty($input)) {
            $data = json_decode($input, true) ?: [];
        }

        // Merge with POST data
        if (!empty($_POST)) {
            $data = array_merge($data, $_POST);
        }

        // Merge with GET data
        if (!empty($_GET)) {
            $data = array_merge($data, $_GET);
        }

        return $data;
    }
}
