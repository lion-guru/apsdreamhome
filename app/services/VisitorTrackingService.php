<?php

namespace App\Services;

use App\Core\Database\Database;

/**
 * Visitor Tracking Service
 * Tracks anonymous visitors for lead capture and follow-up
 */
class VisitorTrackingService
{
    private $db;
    private $sessionId;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->sessionId = $this->getSessionId();
    }

    /**
     * Get or create session ID
     */
    private function getSessionId()
    {
        if (isset($_COOKIE['visitor_session_id'])) {
            return $_COOKIE['visitor_session_id'];
        }

        $sessionId = session_id();
        if (empty($sessionId)) {
            session_start();
            $sessionId = session_id();
        }

        // Set cookie for 30 days
        setcookie('visitor_session_id', $sessionId, time() + (86400 * 30), '/');

        return $sessionId;
    }

    /**
     * Track visitor session
     */
    public function trackSession()
    {
        try {
            $ipAddress = $this->getIpAddress();
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $referrer = $_SERVER['HTTP_REFERER'] ?? '';
            $landingPage = $_SERVER['REQUEST_URI'] ?? '';

            // Check if session exists
            $existing = $this->db->fetchOne(
                "SELECT * FROM visitor_sessions WHERE session_id = ? LIMIT 1",
                [$this->sessionId]
            );

            if ($existing) {
                // Update existing session
                $this->db->query(
                    "UPDATE visitor_sessions SET 
                        last_visit = NOW(),
                        page_views = page_views + 1,
                        time_on_site = time_on_site + ?,
                        is_converted = ?
                    WHERE session_id = ?",
                    [
                        $this->calculateTimeSinceLastVisit($existing['last_visit']),
                        isset($_SESSION['user_id']) ? 1 : $existing['is_converted'],
                        $this->sessionId
                    ]
                );
            } else {
                // Create new session
                $this->db->insert('visitor_sessions', [
                    'session_id' => $this->sessionId,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'referrer' => $referrer,
                    'landing_page' => $landingPage,
                    'first_visit' => date('Y-m-d H:i:s'),
                    'last_visit' => date('Y-m-d H:i:s'),
                    'page_views' => 1,
                    'time_on_site' => 0,
                    'is_converted' => isset($_SESSION['user_id']) ? 1 : 0
                ]);
            }

            // Track page view
            $this->trackPageView($landingPage);

            return true;
        } catch (\Exception $e) {
            error_log("Visitor tracking error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Track individual page view
     */
    public function trackPageView($pageUrl, $pageTitle = null)
    {
        try {
            $this->db->insert('visitor_page_views', [
                'session_id' => $this->sessionId,
                'page_url' => $pageUrl,
                'page_title' => $pageTitle,
                'visited_at' => date('Y-m-d H:i:s')
            ]);

            return true;
        } catch (\Exception $e) {
            error_log("Page view tracking error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Track incomplete registration - now uses existing leads table
     */
    public function trackIncompleteRegistration($data)
    {
        try {
            $email = $data['email'] ?? null;
            $phone = $data['phone'] ?? null;
            $name = $data['name'] ?? null;
            $registrationType = $data['registration_type'] ?? 'standard';
            $stepCompleted = $data['step_completed'] ?? 1;
            $totalSteps = $data['total_steps'] ?? 1;

            // Check if lead already exists for this email/phone
            $existing = $this->db->fetchOne(
                "SELECT * FROM leads WHERE email = ? OR phone = ? LIMIT 1",
                [$email, $phone]
            );

            if ($existing) {
                // Update existing lead
                $this->db->query(
                    "UPDATE leads SET 
                        name = COALESCE(?, name),
                        email = COALESCE(?, email),
                        phone = COALESCE(?, phone),
                        message = CONCAT(COALESCE(message, ''), ?, ' '),
                        status = 'new',
                        updated_at = NOW()
                    WHERE id = ?",
                    [
                        $name,
                        $email,
                        $phone,
                        "[Incomplete Registration - Step {$stepCompleted}/{$totalSteps}]",
                        $existing['id']
                    ]
                );
            } else {
                // Create new lead
                $this->db->insert('leads', [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'message' => "[Incomplete Registration - Step {$stepCompleted}/{$totalSteps}]",
                    'status' => 'new',
                    'source' => 'incomplete_registration',
                    'priority' => 'medium',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return true;
        } catch (\Exception $e) {
            error_log("Incomplete registration tracking error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark visitor as converted (completed registration) - now uses leads table
     */
    public function markAsConverted($userId)
    {
        try {
            // Update visitor session
            $this->db->query(
                "UPDATE visitor_sessions SET 
                    is_converted = 1,
                    converted_user_id = ?,
                    converted_at = NOW()
                WHERE session_id = ?",
                [$userId, $this->sessionId]
            );

            // Update leads - mark leads from this session as converted
            $this->db->query(
                "UPDATE leads SET 
                    status = 'converted',
                    assigned_to = ?,
                    updated_at = NOW()
                WHERE email IN (SELECT email FROM visitor_sessions WHERE session_id = ?) 
                   OR phone IN (SELECT phone FROM visitor_sessions WHERE session_id = ?)",
                [$userId, $this->sessionId, $this->sessionId]
            );

            return true;
        } catch (\Exception $e) {
            error_log("Mark as converted error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get visitor session info
     */
    public function getVisitorSession()
    {
        try {
            return $this->db->fetchOne(
                "SELECT * FROM visitor_sessions WHERE session_id = ? LIMIT 1",
                [$this->sessionId]
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get IP address
     */
    private function getIpAddress()
    {
        $ip = '';

        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Calculate time since last visit
     */
    private function calculateTimeSinceLastVisit($lastVisit)
    {
        $last = strtotime($lastVisit);
        $now = time();
        return $now - $last;
    }

    /**
     * Determine interest type from data
     */
    private function determineInterestType($data)
    {
        if (isset($data['property_type'])) {
            return 'property_' . $data['property_type'];
        }
        if (isset($data['user_type'])) {
            return 'registration_' . $data['user_type'];
        }
        return 'general_inquiry';
    }

    /**
     * Determine lead priority
     */
    private function determinePriority($data)
    {
        // High priority if phone provided
        if (!empty($data['phone'])) {
            return 'high';
        }
        // Medium priority if email provided
        if (!empty($data['email'])) {
            return 'medium';
        }
        // Low priority otherwise
        return 'low';
    }
}
