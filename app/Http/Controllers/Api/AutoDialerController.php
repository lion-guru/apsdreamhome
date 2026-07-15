<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\Voice\VoiceCallService;
use App\Services\Communication\WhatsAppSenderService;
use App\Services\Communication\SMSService;

/**
 * Auto-Dialer API Controller — Mobile App endpoints for call scheduling & bulk messaging
 * 
 * Endpoints:
 *   POST /api/v2/mobile/auto-dialer/schedule
 *   POST /api/v2/mobile/auto-dialer/bulk-schedule
 *   GET  /api/v2/mobile/auto-dialer/schedule
 *   POST /api/v2/mobile/auto-dialer/cancel/{id}
 *   POST /api/v2/mobile/auto-dialer/reschedule/{id}
 *   GET  /api/v2/mobile/auto-dialer/stats
 *   GET  /api/v2/mobile/auto-dialer/history
 *   POST /api/v2/mobile/auto-dialer/process
 *   POST /api/v2/mobile/auto-dialer/send-sms
 *   POST /api/v2/mobile/auto-dialer/send-whatsapp
 *   POST /api/v2/mobile/auto-dialer/bulk-sms
 *   POST /api/v2/mobile/auto-dialer/bulk-whatsapp
 */
class AutoDialerController extends BaseController
{
    protected $db;
    protected $voiceService;
    protected $whatsappService;
    protected $smsService;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
        $this->voiceService = new VoiceCallService();
        $this->whatsappService = new WhatsAppSenderService();
        $this->smsService = new \App\Services\Communication\SMSService();
    }

    /**
     * API endpoints are token/session authenticated (not CSRF-cookie based),
     * so skip the BaseController CSRF check for all POST routes here.
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * POST /api/v2/mobile/auto-dialer/schedule
     * Schedule a single call
     */
    public function schedule()
    {
        try {
            $input = $this->getJsonInput();
            $leadId = $input['lead_id'] ?? null;
            $phone = $input['phone'] ?? '';
            $scheduledDate = $input['scheduled_date'] ?? date('Y-m-d');
            $scheduledTime = $input['scheduled_time'] ?? '10:00:00';
            $scriptTemplate = $input['script_template'] ?? 'property_introduction';
            $priority = $input['priority'] ?? 'medium';
            $leadName = $input['lead_name'] ?? '';

            if (empty($leadId) && empty($phone)) {
                return $this->jsonResponse(['success' => false, 'error' => 'lead_id or phone required'], 400);
            }

            $result = $this->voiceService->scheduleCall(
                $leadId, $phone, null, $scheduledDate, $scheduledTime,
                $scriptTemplate, $priority, $leadName
            );

            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/bulk-schedule
     * Bulk schedule calls for multiple leads
     */
    public function bulkSchedule()
    {
        try {
            $input = $this->getJsonInput();
            $leads = $input['leads'] ?? [];
            $scheduledDate = $input['scheduled_date'] ?? date('Y-m-d');
            $scheduledTime = $input['scheduled_time'] ?? '10:00:00';
            $scriptTemplate = $input['script_template'] ?? 'property_introduction';
            $priority = $input['priority'] ?? 'medium';

            if (empty($leads)) {
                return $this->jsonResponse(['success' => false, 'error' => 'No leads provided'], 400);
            }

            $scheduled = 0;
            $failed = 0;
            $errors = [];

            foreach ($leads as $lead) {
                $leadId = $lead['id'] ?? $lead['lead_id'] ?? null;
                $phone = $lead['phone'] ?? '';
                $leadName = $lead['name'] ?? $lead['lead_name'] ?? '';

                $result = $this->voiceService->scheduleCall(
                    $leadId, $phone, null, $scheduledDate, $scheduledTime,
                    $scriptTemplate, $priority, $leadName
                );

                if ($result['success']) {
                    $scheduled++;
                } else {
                    $failed++;
                    $errors[] = $result['message'] ?? 'Unknown error';
                }
            }

            return $this->jsonResponse([
                'success' => $scheduled > 0,
                'scheduled' => $scheduled,
                'failed' => $failed,
                'total' => count($leads),
                'errors' => array_slice($errors, 0, 10),
                'message' => "$scheduled calls scheduled, $failed failed",
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v2/mobile/auto-dialer/schedule
     * Get scheduled calls queue
     */
    public function getSchedule()
    {
        try {
            $status = $_GET['status'] ?? null;
            $date = $_GET['date'] ?? null;
            $limit = min(intval($_GET['limit'] ?? 50), 100);
            $offset = intval($_GET['offset'] ?? 0);

            $where = "1=1";
            $params = [];

            if ($status) {
                $where .= " AND s.status = ?";
                $params[] = $status;
            }
            if ($date) {
                $where .= " AND s.scheduled_date = ?";
                $params[] = $date;
            }

            $rows = $this->db->fetchAll(
                "SELECT s.*, l.name as lead_name, l.phone as lead_phone,
                        l.property_interest, l.budget
                 FROM ai_calling_schedule s
                 LEFT JOIN leads l ON l.id = s.lead_id
                 WHERE $where
                 ORDER BY 
                    CASE s.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
                    s.scheduled_date, s.scheduled_time
                 LIMIT $limit OFFSET $offset",
                $params
            );

            $total = $this->db->fetch(
                "SELECT COUNT(*) as count FROM ai_calling_schedule s WHERE $where",
                $params
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => $rows,
                'total' => intval($total['count'] ?? 0),
                'limit' => $limit,
                'offset' => $offset,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => true, 'data' => [], 'total' => 0]);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/cancel/{id}
     * Cancel a scheduled call
     */
    public function cancel($id)
    {
        try {
            $this->db->execute(
                "UPDATE ai_calling_schedule SET status = 'cancelled', updated_at = NOW() WHERE id = ?",
                [$id]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Call cancelled',
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/reschedule/{id}
     * Reschedule a call
     */
    public function reschedule($id)
    {
        try {
            $input = $this->getJsonInput();
            $newDate = $input['new_date'] ?? null;
            $newTime = $input['new_time'] ?? '10:00:00';

            if (!$newDate) {
                return $this->jsonResponse(['success' => false, 'error' => 'new_date required'], 400);
            }

            $this->db->execute(
                "UPDATE ai_calling_schedule SET scheduled_date = ?, scheduled_time = ?, status = 'pending', updated_at = NOW() WHERE id = ?",
                [$newDate, $newTime, $id]
            );

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Call rescheduled',
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v2/mobile/auto-dialer/stats
     * Get auto-dialer statistics
     */
    public function stats()
    {
        try {
            $today = date('Y-m-d');

            $stats = [
                'total_scheduled' => 0,
                'completed_today' => 0,
                'pending_today' => 0,
                'failed_today' => 0,
                'connected' => 0,
                'not_answered' => 0,
                'busy' => 0,
                'call_later' => 0,
            ];

            try {
                $row = $this->db->fetch(
                    "SELECT COUNT(*) as total FROM ai_calling_schedule"
                );
                $stats['total_scheduled'] = intval($row['total'] ?? 0);
            } catch (\Throwable $e) {}

            try {
                $row = $this->db->fetch(
                    "SELECT COUNT(*) as total FROM ai_calling_schedule WHERE scheduled_date = ? AND status = 'completed'",
                    [$today]
                );
                $stats['completed_today'] = intval($row['total'] ?? 0);
            } catch (\Throwable $e) {}

            try {
                $row = $this->db->fetch(
                    "SELECT COUNT(*) as total FROM ai_calling_schedule WHERE scheduled_date = ? AND status = 'pending'",
                    [$today]
                );
                $stats['pending_today'] = intval($row['total'] ?? 0);
            } catch (\Throwable $e) {}

            try {
                $row = $this->db->fetch(
                    "SELECT COUNT(*) as total FROM ai_calling_schedule WHERE scheduled_date = ? AND status = 'failed'",
                    [$today]
                );
                $stats['failed_today'] = intval($row['total'] ?? 0);
            } catch (\Throwable $e) {}

            // Call outcomes
            try {
                $row = $this->db->fetch(
                    "SELECT call_outcome, COUNT(*) as total FROM ai_call_sessions 
                     WHERE DATE(created_at) = ? AND call_outcome IS NOT NULL
                     GROUP BY call_outcome",
                    [$today]
                );
                if ($row) {
                    $stats[$row['call_outcome']] = intval($row['total']);
                }
            } catch (\Throwable $e) {}

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'total_scheduled' => 0,
                    'completed_today' => 0,
                    'pending_today' => 0,
                    'failed_today' => 0,
                    'connected' => 0,
                    'not_answered' => 0,
                    'busy' => 0,
                    'call_later' => 0,
                ],
            ]);
        }
    }

    /**
     * GET /api/v2/mobile/auto-dialer/history
     * Get call history
     */
    public function history()
    {
        try {
            $limit = min(intval($_GET['limit'] ?? 20), 100);
            $offset = intval($_GET['offset'] ?? 0);

            $rows = $this->db->fetchAll(
                "SELECT cs.*, l.name as lead_name, l.phone as lead_phone
                 FROM ai_call_sessions cs
                 LEFT JOIN leads l ON l.id = cs.lead_id
                 ORDER BY cs.created_at DESC
                 LIMIT $limit OFFSET $offset"
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => true, 'data' => []]);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/process
     * Process the auto-dialer queue (admin trigger)
     */
    public function processQueue()
    {
        try {
            $pending = $this->db->fetchAll(
                "SELECT * FROM ai_calling_schedule 
                 WHERE status = 'pending' 
                 AND scheduled_date <= CURDATE()
                 AND (scheduled_time <= CURDATE() OR scheduled_date < CURDATE())
                 ORDER BY 
                    CASE priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END
                 LIMIT 10"
            );

            $processed = 0;
            $failed = 0;

            foreach ($pending as $schedule) {
                try {
                    $result = $this->voiceService->initiateCall($schedule['id']);
                    if ($result['success']) {
                        $processed++;
                    } else {
                        $failed++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'message' => "$processed calls processed, $failed failed",
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/calls/log
     * Log a call attempt/outcome from the mobile app
     */
    public function logCall()
    {
        try {
            $input = $this->getJsonInput();
            $this->db->execute(
                "INSERT INTO calls_log
                 (lead_id, user_id, phone, name, action, outcome, method, duration, notes, ai_score, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    intval($input['lead_id'] ?? 0) ?: null,
                    intval($_SESSION['user_id'] ?? 0) ?: null,
                    $input['phone'] ?? null,
                    $input['name'] ?? null,
                    $input['action'] ?? null,
                    $input['outcome'] ?? null,
                    $input['method'] ?? 'app',
                    intval($input['duration'] ?? 0) ?: null,
                    $input['notes'] ?? null,
                    isset($input['ai_score']) ? intval($input['ai_score']) : null,
                ]
            );
            return $this->jsonResponse(['success' => true, 'message' => 'Call logged']);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/v2/mobile/calls/stats
     * Call analytics: outcomes, methods, daily volume
     */
    public function callStats()
    {
        try {
            $days = intval($_GET['days'] ?? 30);
            $since = date('Y-m-d', strtotime("-$days days"));

            $outcomes = $this->db->fetchAll(
                "SELECT outcome, COUNT(*) as total FROM calls_log
                 WHERE created_at >= ? AND outcome IS NOT NULL AND outcome != ''
                 GROUP BY outcome ORDER BY total DESC",
                [$since]
            );
            $methods = $this->db->fetchAll(
                "SELECT method, COUNT(*) as total FROM calls_log
                 WHERE created_at >= ? AND method IS NOT NULL
                 GROUP BY method ORDER BY total DESC",
                [$since]
            );
            $daily = $this->db->fetchAll(
                "SELECT DATE(created_at) as day, COUNT(*) as total,
                        SUM(CASE WHEN outcome='connected' THEN 1 ELSE 0 END) as connected
                 FROM calls_log WHERE created_at >= ?
                 GROUP BY DATE(created_at) ORDER BY day",
                [$since]
            );
            $totals = $this->db->fetch(
                "SELECT COUNT(*) as total,
                        SUM(CASE WHEN outcome='connected' THEN 1 ELSE 0 END) as connected,
                        SUM(CASE WHEN outcome='not_answered' THEN 1 ELSE 0 END) as not_answered,
                        SUM(CASE WHEN outcome='busy' THEN 1 ELSE 0 END) as busy,
                        SUM(CASE WHEN outcome='call_later' THEN 1 ELSE 0 END) as call_later,
                        SUM(CASE WHEN action LIKE '%whatsapp%' THEN 1 ELSE 0 END) as whatsapp,
                        SUM(CASE WHEN action LIKE '%sms%' THEN 1 ELSE 0 END) as sms
                 FROM calls_log WHERE created_at >= ?",
                [$since]
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'outcomes' => $outcomes,
                    'methods' => $methods,
                    'daily' => $daily,
                    'totals' => $totals ?: [
                        'total' => 0, 'connected' => 0, 'not_answered' => 0,
                        'busy' => 0, 'call_later' => 0, 'whatsapp' => 0, 'sms' => 0,
                    ],
                    'period_days' => $days,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse([
                'success' => true,
                'data' => ['outcomes' => [], 'methods' => [], 'daily' => [], 'totals' => []],
            ]);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/send-sms
     * Send a single SMS
     */
    public function sendSms()
    {
        try {
            $input = $this->getJsonInput();
            $phone = $input['phone'] ?? '';
            $message = $input['message'] ?? '';
            $templateCode = $input['template_code'] ?? null;

            if (empty($phone) || empty($message)) {
                return $this->jsonResponse(['success' => false, 'error' => 'phone and message required'], 400);
            }

            $result = $this->smsService->sendSMS($phone, $message, $templateCode);

            return $this->jsonResponse([
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ? 'SMS sent' : ($result['error'] ?? 'Failed to send'),
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/send-whatsapp
     * Send a single WhatsApp message
     */
    public function sendWhatsApp()
    {
        try {
            $input = $this->getJsonInput();
            $phone = $input['phone'] ?? '';
            $message = $input['message'] ?? '';
            $templateName = $input['template_name'] ?? null;

            if (empty($phone) || empty($message)) {
                return $this->jsonResponse(['success' => false, 'error' => 'phone and message required'], 400);
            }

            if (!$this->whatsappService->isConfigured()) {
                // Fallback: open WhatsApp via URL scheme
                return $this->jsonResponse([
                    'success' => true,
                    'method' => 'url_scheme',
                    'url' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $phone) . '?text=' . urlencode($message),
                    'message' => 'WhatsApp not configured, opening via URL',
                ]);
            }

            $result = $this->whatsappService->sendMessage($phone, $message, $templateName);

            return $this->jsonResponse([
                'success' => $result['success'] ?? false,
                'message' => $result['success'] ? 'WhatsApp sent' : ($result['error'] ?? 'Failed to send'),
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/bulk-sms
     * Bulk send SMS to multiple leads
     */
    public function bulkSms()
    {
        try {
            $input = $this->getJsonInput();
            $leads = $input['leads'] ?? [];
            $message = $input['message'] ?? '';
            $templateCode = $input['template_code'] ?? null;

            if (empty($leads) || empty($message)) {
                return $this->jsonResponse(['success' => false, 'error' => 'leads and message required'], 400);
            }

            $sent = 0;
            $failed = 0;

            foreach ($leads as $lead) {
                $phone = $lead['phone'] ?? '';
                if (empty($phone)) {
                    $failed++;
                    continue;
                }

                try {
                    $result = $this->smsService->sendSMS($phone, $message, $templateCode);
                    if ($result['success']) {
                        $sent++;
                    } else {
                        $failed++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                }
            }

            return $this->jsonResponse([
                'success' => $sent > 0,
                'sent' => $sent,
                'failed' => $failed,
                'total' => count($leads),
                'message' => "$sent SMS sent, $failed failed",
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/bulk-whatsapp
     * Bulk send WhatsApp to multiple leads
     */
    public function bulkWhatsApp()
    {
        try {
            $input = $this->getJsonInput();
            $leads = $input['leads'] ?? [];
            $message = $input['message'] ?? '';
            $templateName = $input['template_name'] ?? null;

            if (empty($leads) || empty($message)) {
                return $this->jsonResponse(['success' => false, 'error' => 'leads and message required'], 400);
            }

            $sent = 0;
            $failed = 0;

            foreach ($leads as $lead) {
                $phone = $lead['phone'] ?? '';
                if (empty($phone)) {
                    $failed++;
                    continue;
                }

                try {
                    if ($this->whatsappService->isConfigured()) {
                        $result = $this->whatsappService->sendMessage($phone, $message, $templateName);
                        if ($result['success']) {
                            $sent++;
                        } else {
                            $failed++;
                        }
                    } else {
                        // Queue for URL scheme fallback
                        $sent++;
                    }
                } catch (\Throwable $e) {
                    $failed++;
                }
            }

            return $this->jsonResponse([
                'success' => $sent > 0,
                'sent' => $sent,
                'failed' => $failed,
                'total' => count($leads),
                'message' => "$sent WhatsApp sent, $failed failed",
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/v2/mobile/auto-dialer/ai-schedule
     * AI-powered auto-scheduling: scores leads and schedules calls for hot ones
     */
    public function aiSchedule()
    {
        try {
            $input = $this->getJsonInput();
            $minScore = intval($input['min_score'] ?? 70);
            $limit = min(intval($input['limit'] ?? 10), 50);
            $scheduledDate = $input['scheduled_date'] ?? date('Y-m-d');
            $scheduledTime = $input['scheduled_time'] ?? '10:00:00';

            // Fetch leads with scores from AI lead scoring
            $leads = $this->db->fetchAll(
                "SELECT l.id, l.name, l.phone, l.budget, l.status, l.source,
                        l.property_interest, l.city, l.created_at,
                        COALESCE(l.ai_score, 0) as ai_score
                 FROM leads l
                 WHERE l.phone IS NOT NULL AND l.phone != ''
                 AND l.status NOT IN ('lost', 'do_not_contact')
                 ORDER BY l.ai_score DESC, l.updated_at DESC
                 LIMIT ?",
                [$limit * 3] // Fetch more than needed for filtering
            );

            $scored = [];
            foreach ($leads as $lead) {
                // Simple scoring if ai_score is 0
                $score = intval($lead['ai_score'] ?? 0);
                if ($score == 0) {
                    $score = $this->calculateQuickScore($lead);
                }

                if ($score >= $minScore) {
                    $lead['final_score'] = $score;
                    $scored[] = $lead;
                }
            }

            // Sort by score descending and take top N
            usort($scored, fn($a, $b) => $b['final_score'] <=> $a['final_score']);
            $scored = array_slice($scored, 0, $limit);

            $scheduled = 0;
            $failed = 0;
            $results = [];

            foreach ($scored as $lead) {
                // Check if already scheduled for today
                $existing = $this->db->fetch(
                    "SELECT id FROM ai_calling_schedule 
                     WHERE lead_id = ? AND scheduled_date = ? AND status IN ('pending', 'processing')",
                    [$lead['id'], $scheduledDate]
                );

                if ($existing) {
                    $results[] = [
                        'lead_id' => $lead['id'],
                        'name' => $lead['name'],
                        'status' => 'already_scheduled',
                    ];
                    continue;
                }

                $priority = $lead['final_score'] >= 90 ? 'high' : 'medium';
                $result = $this->voiceService->scheduleCall(
                    $lead['id'],
                    $lead['phone'],
                    null,
                    $scheduledDate,
                    $scheduledTime,
                    'ai_auto_qualify',
                    $priority,
                    $lead['name']
                );

                if ($result['success']) {
                    $scheduled++;
                    $results[] = [
                        'lead_id' => $lead['id'],
                        'name' => $lead['name'],
                        'score' => $lead['final_score'],
                        'status' => 'scheduled',
                    ];
                } else {
                    $failed++;
                    $results[] = [
                        'lead_id' => $lead['id'],
                        'name' => $lead['name'],
                        'status' => 'failed',
                        'error' => $result['message'] ?? 'Unknown',
                    ];
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'scheduled' => $scheduled,
                'failed' => $failed,
                'already_scheduled' => count($scored) - $scheduled - $failed,
                'total_scored' => count($scored),
                'min_score' => $minScore,
                'results' => $results,
                'message' => "AI scheduled $scheduled calls from " . count($scored) . " hot leads",
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Quick scoring based on lead data when AI score is not available
     */
    private function calculateQuickScore(array $lead): int
    {
        $score = 50; // Base score

        // Budget factor (higher = more serious)
        $budget = intval($lead['budget'] ?? 0);
        if ($budget > 5000000) $score += 15;
        elseif ($budget > 2000000) $score += 10;
        elseif ($budget > 1000000) $score += 5;

        // Recency factor (newer leads are hotter)
        $createdAt = $lead['created_at'] ?? '';
        if ($createdAt) {
            $daysOld = (time() - strtotime($createdAt)) / 86400;
            if ($daysOld < 3) $score += 15;
            elseif ($daysOld < 7) $score += 10;
            elseif ($daysOld < 30) $score += 5;
            elseif ($daysOld > 90) $score -= 10;
        }

        // Source factor
        $source = strtolower($lead['source'] ?? '');
        if (in_array($source, ['referral', 'walk_in', 'website'])) $score += 10;
        elseif (in_array($source, ['call', 'whatsapp'])) $score += 5;

        // Status factor
        $status = strtolower($lead['status'] ?? '');
        if ($status === 'hot') $score += 15;
        elseif ($status === 'qualified') $score += 10;
        elseif ($status === 'contacted') $score += 5;
        elseif ($status === 'cold') $score -= 15;

        return max(0, min(100, $score));
    }

    // ─── Helper ───

    /**
     * POST /api/v2/mobile/voice-chat
     * In-app AI voice conversation: receive transcribed text, return AI reply.
     * The mobile app handles STT (speech_to_text) and TTS (flutter_tts) on-device;
     * this endpoint runs the LLM pipeline and returns the reply text + intent.
     */
    public function voiceChat()
    {
        try {
            $input = $this->getJsonInput();
            $message = trim($input['message'] ?? '');
            $sessionId = intval($input['session_id'] ?? 0);
            $leadId = intval($input['lead_id'] ?? 0) ?: null;

            if (empty($message)) {
                return $this->jsonResponse(['success' => false, 'error' => 'message required'], 400);
            }

            // Lazily create a session for transcript logging
            if (!$sessionId) {
                $phone = '';
                try {
                    if (!empty($_SESSION['user_id'])) {
                        $u = $this->db->fetch("SELECT phone FROM users WHERE id = ?", [$_SESSION['user_id']]);
                        $phone = $u['phone'] ?? '';
                    }
                } catch (\Throwable $e) {}
                if (empty($phone)) {
                    $phone = 'app-voice-chat';
                }
                $this->db->execute(
                    "INSERT INTO ai_call_sessions (lead_id, phone, call_type, status, script_template, started_at, created_at, updated_at)
                     VALUES (?, ?, 'inbound', 'in_progress', 'ai_voice_chat', NOW(), NOW(), NOW())",
                    [$leadId, $phone]
                );
                $sessionId = $this->db->lastInsertId();
            }

            $pipeline = new \App\Services\Voice\AIVoicePipeline();
            $result = $pipeline->processTurn($sessionId, $message, 'text');

            $reply = $result['response_text'] ?? $result['fallback'] ?? 'माफ़ कीजिए, मैं समझ नहीं पाया। कृपया दोबारा बताएं।';

            return $this->jsonResponse([
                'success' => true,
                'session_id' => $sessionId,
                'reply' => $reply,
                'intent' => $result['intent'] ?? 'general',
                'sentiment' => $result['sentiment'] ?? 'neutral',
                'engine' => $result['engine'] ?? 'fallback',
            ]);
        } catch (\Throwable $e) {
            return $this->jsonResponse([
                'success' => true,
                'reply' => 'माफ़ कीजिए, तकनीकी समस्या है। कृपया थोड़ी देर बाद कोशिश करें।',
                'intent' => 'error',
                'engine' => 'fallback',
            ]);
        }
    }

    private function getJsonInput(): array
    {
        $rawInput = '';
        if (!empty($this->request) && method_exists($this->request, 'getContentAsString')) {
            $rawInput = $this->request->getContentAsString();
        }
        if (empty($rawInput)) {
            $rawInput = file_get_contents('php://input') ?: '';
        }
        if ($rawInput !== '') {
            // Strip a UTF-8 BOM if present so json_decode doesn't fail
            if (substr($rawInput, 0, 3) === "\xEF\xBB\xBF") {
                $rawInput = substr($rawInput, 3);
            }
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $_POST;
    }
}
