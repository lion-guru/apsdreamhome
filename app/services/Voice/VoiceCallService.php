<?php

namespace App\Services\Voice;

use App\Traits\ServiceTenantTrait;

class VoiceCallService
{
    use ServiceTenantTrait;

    protected $db;

    public function __construct()
    {
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function scheduleCall($leadId, $phone, $agentId, $scheduledDate, $scheduledTime = '10:00:00', $scriptTemplate = 'property_introduction', $priority = 'medium', $leadName = '')
    {
        $existing = $this->db->fetch(
            "SELECT id FROM ai_calling_schedule WHERE lead_id = ? AND status IN ('pending','processing')" . $this->tenantSql(),
            [$leadId]
        );
        if ($existing) {
            return ['success' => false, 'message' => 'Lead already has a pending schedule', 'schedule_id' => $existing['id']];
        }

        $tid = $this->tenantId();
        $this->db->execute(
            "INSERT INTO ai_calling_schedule (lead_id, phone, priority, scheduled_date, scheduled_time, timezone, script_template, max_attempts, attempt_count, status, ai_agent_id, tenant_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 'Asia/Kolkata', ?, 3, 0, 'pending', ?, ?, NOW(), NOW())",
            [$leadId, $phone, $priority, $scheduledDate, $scheduledTime, $scriptTemplate, $agentId, $tid > 1 ? $tid : 1]
        );

        $scheduleId = $this->db->lastInsertId();

        $this->db->execute(
            "UPDATE leads SET next_activity_date = ? WHERE id = ?" . $this->tenantSql(),
            array_merge([$scheduledDate . ' ' . $scheduledTime, $leadId], $tid > 1 ? [$tid] : [])
        );

        $this->logActivity('CALL_SCHEDULED', "Lead #$leadId scheduled for $scheduledDate $scheduledTime with agent $agentId", $leadId);

        return [
            'success' => true,
            'schedule_id' => $scheduleId,
            'lead_id' => $leadId,
            'scheduled_date' => $scheduledDate,
            'scheduled_time' => $scheduledTime,
            'message' => 'Call scheduled successfully'
        ];
    }

    public function initiateCall($scheduleId)
    {
        $schedule = $this->db->fetch(
            "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.property_interest,
                    a.agent_name, a.agent_id as agent_identifier
             FROM ai_calling_schedule s
             LEFT JOIN leads l ON l.id = s.lead_id
             LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
             WHERE s.id = ?",
            [$scheduleId]
        );

        if (!$schedule) {
            return ['success' => false, 'error' => 'Schedule not found'];
        }
        if ($schedule['status'] !== 'pending') {
            return ['success' => false, 'error' => 'Schedule status is ' . $schedule['status']];
        }

        $this->db->execute(
            "UPDATE ai_calling_schedule SET status = 'processing', attempt_count = attempt_count + 1, last_attempt_at = NOW(), updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
            array_merge([$scheduleId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
        );

        try {
            $script = $this->db->fetch(
                "SELECT * FROM ai_call_scripts WHERE script_code = ? AND is_active = 1 LIMIT 1" . $this->tenantSql(),
                [$schedule['script_template']]
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        $tid = $this->tenantId();
        $sessionData = [
            'lead_id' => $schedule['lead_id'],
            'phone' => $schedule['phone'] ?: ($schedule['lead_phone'] ?? ''),
            'call_type' => 'outbound',
            'status' => 'in_progress',
            'ai_agent_id' => $schedule['ai_agent_id'],
            'script_template' => $schedule['script_template'],
            'scheduled_at' => $schedule['scheduled_date'] . ' ' . ($schedule['scheduled_time'] ?? '10:00:00'),
            'started_at' => date('Y-m-d H:i:s'),
            'duration_seconds' => 0,
            'tenant_id' => $tid > 1 ? $tid : 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('ai_call_sessions', $sessionData);
        $sessionId = $this->db->lastInsertId();

        $this->db->execute(
            "UPDATE ai_calling_schedule SET call_session_id = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
            array_merge([$sessionId, $scheduleId], $tid > 1 ? [$tid] : [])
        );

        $this->db->execute(
            "UPDATE ai_calling_agents SET current_calls = current_calls + 1, total_calls_made = total_calls_made + 1, last_active_at = NOW() WHERE agent_id = ?" . $this->tenantSql(),
            array_merge([$schedule['ai_agent_id']], $tid > 1 ? [$tid] : [])
        );

        $greeting = $script['greeting_text'] ?? 'Hello, this is an automated call from APS Dream Home.';
        $intro = $script['introduction_text'] ?? "I'm calling regarding your inquiry about " . ($schedule['property_interest'] ?? 'our properties') . ".";
        $closing = $script['closing_text'] ?? 'Thank you for your time. We will follow up with you soon.';

        // Make the actual call via AsteriskService
        $asteriskResult = null;
        try {
            $asterisk = new AsteriskService();
            $phone = $sessionData['phone'];
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;

            $asteriskResult = $asterisk->makeCall($cleanPhone, $schedule['script_template'] ?? 'default', [
                'variables' => [
                    'SCHEDULE_ID' => (string)$scheduleId,
                    'SESSION_ID' => (string)$sessionId,
                    'LEAD_ID' => (string)($schedule['lead_id'] ?? ''),
                    'LEAD_NAME' => $schedule['lead_name'] ?? '',
                ]
            ]);

            if ($asteriskResult['success']) {
                $this->db->execute(
                    "UPDATE ai_call_sessions SET call_sid = ? WHERE id = ?" . $this->tenantSql(),
                    [$asteriskResult['call_id'], $sessionId]
                );
            }
        } catch (\Exception $e) {
            error_log("VoiceCallService initiateCall Asterisk error: " . $e->getMessage());
        }

        return [
            'success' => true,
            'schedule_id' => $scheduleId,
            'session_id' => $sessionId,
            'lead_name' => $schedule['lead_name'] ?? 'Valued Customer',
            'lead_phone' => $sessionData['phone'],
            'agent_name' => $schedule['agent_name'] ?? 'AI Agent',
            'call_id' => $asteriskResult['call_id'] ?? null,
            'asterisk' => $asteriskResult ? ($asteriskResult['success'] ? 'connected' : 'failed') : 'unavailable',
            'script' => [
                'greeting' => $greeting,
                'introduction' => $intro,
                'questions' => $script['questions_to_ask'] ?? '[]',
                'closing' => $closing
            ],
            'duration_estimated' => 120,
            'message' => $asteriskResult && $asteriskResult['success']
                ? 'Call initiated via SIM card'
                : 'Session created. ' . ($asteriskResult['message'] ?? 'Asterisk unavailable'),
        ];
    }

    public function processResponse($sessionId, $userInput, $intent = null, $sentiment = 'neutral')
    {
        $session = $this->db->fetch("SELECT * FROM ai_call_sessions WHERE id = ?" . $this->tenantSql(), [$sessionId]);
        if (!$session) {
            return ['success' => false, 'error' => 'Session not found'];
        }

        $transcript = $session['call_transcript'] ?? '';
        $updatedTranscript = $transcript
            ? $transcript . "\n[USER]: " . $userInput
            : "[USER]: " . $userInput;

        $detectedIntent = $intent;
        if (!$detectedIntent) {
            $input = mb_strtolower($userInput);
            if (preg_match('/(price|cost|kitna|rate|budget)/', $input)) $detectedIntent = 'price_inquiry';
            elseif (preg_match('/(visit|dekhna|dikhana|site|location)/', $input)) $detectedIntent = 'site_visit';
            elseif (preg_match('/(booking|book|register|buy|kharid)/', $input)) $detectedIntent = 'booking';
            elseif (preg_match('/(loan|finance|emi|installment)/', $input)) $detectedIntent = 'loan_inquiry';
            elseif (preg_match('/(bye|goodbye|nhi chahiye|no|not interested|baad)/', $input)) $detectedIntent = 'disinterest';
            else $detectedIntent = 'general';
        }

        $responseText = $this->generateResponse($detectedIntent, $session);
        $updatedTranscript .= "\n[AI]: " . $responseText;

        $this->db->execute(
            "UPDATE ai_call_sessions SET call_transcript = ?, sentiment_score = ?, ai_summary = ?, updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
            [$updatedTranscript, $sentiment, $responseText, $sessionId]
        );

        $this->db->execute(
            "UPDATE ai_call_sessions SET call_sid = ?, sentiment = ? WHERE id = ?" . $this->tenantSql(),
            ['SES-' . $sessionId, $sentiment, $sessionId]
        );

        $nextAction = 'continue';
        if ($detectedIntent === 'disinterest') $nextAction = 'end_call';
        elseif ($detectedIntent === 'booking' || $detectedIntent === 'site_visit') $nextAction = 'transfer_to_sales';

        return [
            'success' => true,
            'session_id' => $sessionId,
            'intent' => $detectedIntent,
            'response_text' => $responseText,
            'next_action' => $nextAction
        ];
    }

    public function endCall($sessionId, $summary = null, $outcome = 'unknown')
    {
        $session = $this->db->fetch("SELECT * FROM ai_call_sessions WHERE id = ?" . $this->tenantSql(), [$sessionId]);
        if (!$session) {
            return ['success' => false, 'error' => 'Session not found'];
        }

        $duration = $session['started_at'] ? time() - strtotime($session['started_at']) : 0;

        $this->db->execute(
            "UPDATE ai_call_sessions SET status = 'completed', ended_at = NOW(), duration_seconds = ?, ai_summary = COALESCE(?, ai_summary), updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
            [$duration, $summary, $sessionId]
        );

        if ($session['ai_agent_id']) {
            $this->db->execute(
                "UPDATE ai_calling_agents SET current_calls = GREATEST(current_calls - 1, 0), successful_calls = successful_calls + 1, avg_call_duration = ? WHERE agent_id = ?" . $this->tenantSql(),
                [$duration, $session['ai_agent_id']]
            );
        }

        $this->db->execute(
            "UPDATE ai_calling_schedule SET status = 'completed', result_notes = ?, updated_at = NOW() WHERE call_session_id = ?" . $this->tenantSql(),
            [$summary, $sessionId]
        );

        if ($session['lead_id']) {
            $this->db->execute(
                "UPDATE leads SET last_activity_date = NOW(), status = CASE WHEN ? IN ('interested','followup_needed') THEN 'contacted' ELSE status END WHERE id = ?" . $this->tenantSql(),
                [$outcome, $session['lead_id']]
            );
        }

        if (in_array($outcome, ['interested', 'followup_needed']) && $session['lead_id']) {
            $lead = $this->db->fetch("SELECT name, phone, email, property_interest, budget_range FROM leads WHERE id = ?" . $this->tenantSql(), [$session['lead_id']]);
            if ($lead) {
                $this->db->execute(
                    "INSERT INTO ai_call_extracted_leads (call_session_id, lead_id, extracted_name, extracted_phone, extracted_email, interest_level, quality_score, tenant_id, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $sessionId,
                        $session['lead_id'],
                        $lead['name'],
                        $lead['phone'],
                        $lead['email'] ?? '',
                        $outcome === 'interested' ? 'hot' : 'warm',
                        75,
                        $this->tenantId()
                    ]
                );
            }
        }

        $this->logActivity('CALL_COMPLETED', "Session #$sessionId completed, outcome: $outcome, duration: {$duration}s", $session['lead_id']);

        return [
            'success' => true,
            'session_id' => $sessionId,
            'status' => 'completed',
            'duration_seconds' => $duration,
            'outcome' => $outcome
        ];
    }

    public function getPendingCalls($limit = 10)
    {
        return $this->db->fetchAll(
            "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.property_interest,
                    a.agent_name, a.agent_id as agent_identifier
             FROM ai_calling_schedule s
             LEFT JOIN leads l ON l.id = s.lead_id
             LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id" . $this->tenantSqlForAlias('s') . "
             WHERE s.status = 'pending' AND s.scheduled_date <= CURDATE()
             ORDER BY s.priority ASC, s.scheduled_date ASC, s.scheduled_time ASC
             LIMIT ?",
            [$limit]
        );
    }

    public function processScheduledCalls($limit = 5)
    {
        $pending = $this->getPendingCalls($limit);
        $results = [];

        foreach ($pending as $call) {
            $results[] = $this->initiateCall($call['id']);
        }

        return [
            'success' => true,
            'processed' => count($results),
            'results' => $results
        ];
    }

    public function getAgentStats($agentId = null)
    {
        $where = $agentId ? "WHERE agent_id = ?" : "";
        $params = $agentId ? [$agentId] : [];
        $where .= $this->tenantSqlForAlias('s');

        $stats = $this->db->fetch(
            "SELECT COUNT(*) as total_calls,
                    SUM(CASE WHEN status IN ('completed','failed','no_answer') THEN 1 ELSE 0 END) as processed,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
             FROM ai_calling_schedule s $where",
            $params
        );

        $total = (int)($stats['total_calls'] ?? 0);
        $successful = (int)($stats['successful'] ?? 0);

        return [
            'total_scheduled' => $total,
            'processed' => (int)($stats['processed'] ?? 0),
            'successful' => $successful,
            'failed' => (int)($stats['failed'] ?? 0),
            'conversion_rate' => $total > 0 ? round(($successful / $total) * 100, 1) : 0
        ];
    }

    public function getLeadCallHistory($leadId)
    {
        return $this->db->fetchAll(
            "SELECT s.*, a.agent_name
             FROM ai_call_sessions s
             LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id" . $this->tenantSqlForAlias('s') . "
             WHERE s.lead_id = ?
             ORDER BY s.created_at DESC
             LIMIT 20",
            [$leadId]
        );
    }

    public function getScheduleAnalytics()
    {
        $today = date('Y-m-d');
        return [
            'today_scheduled' => $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE DATE(scheduled_date) = ?" . $this->tenantSql(), [$today])['c'] ?? 0,
            'total_pending' => $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'pending'" . $this->tenantSql())['c'] ?? 0,
            'total_completed' => $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'completed'" . $this->tenantSql())['c'] ?? 0,
            'total_failed' => $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'failed'" . $this->tenantSql())['c'] ?? 0,
            'agents_active' => $this->db->fetch("SELECT COUNT(*) as c FROM ai_calling_agents WHERE status = 'active'" . $this->tenantSql())['c'] ?? 0,
        ];
    }

    public function getCallsByAgent()
    {
        return $this->db->fetchAll(
            "SELECT a.agent_name, a.agent_id, a.status,
                    COUNT(s.id) as total_calls,
                    SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as completed
             FROM ai_calling_agents a" . $this->tenantSqlForAlias('a') . "
             LEFT JOIN ai_calling_schedule s ON s.ai_agent_id = a.agent_id" . $this->tenantSqlForAlias('s') . "
             GROUP BY a.agent_id
             ORDER BY total_calls DESC"
        );
    }

    public function getCallsOverTime($days = 14)
    {
        return $this->db->fetchAll(
            "SELECT DATE(scheduled_date) as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
             FROM ai_calling_schedule
              WHERE scheduled_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)" . $this->tenantSql() . "
              GROUP BY DATE(scheduled_date)
             ORDER BY date ASC",
            [$days]
        );
    }

    public function getAgentList()
    {
        return $this->db->fetchAll(
            "SELECT agent_id, agent_name, status, current_calls, max_concurrent_calls, daily_call_limit, total_calls_made FROM ai_calling_agents ORDER BY agent_name" . $this->tenantSql()
        );
    }

    public function getScriptList()
    {
        try {
            return $this->db->fetchAll(
                "SELECT id, script_code, script_name, is_active, usage_count FROM ai_call_scripts ORDER BY script_name" . $this->tenantSql()
            );
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
    }

    public function getAvailableLeadsForScheduling($limit = 50)
    {
        return $this->db->fetchAll(
             "SELECT l.id, l.name, l.phone, l.property_interest, l.status, l.budget_range
             FROM leads l" . $this->tenantSqlForAlias('l') . "
             WHERE l.status IN ('new','contacted','nurture')
             AND l.id NOT IN (SELECT lead_id FROM ai_calling_schedule WHERE status IN ('pending','processing')" . $this->tenantSql() . ")
             ORDER BY l.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }

    public function rescheduleCall($scheduleId, $newDate, $newTime = '10:00:00')
    {
        $this->db->execute(
            "UPDATE ai_calling_schedule SET scheduled_date = ?, scheduled_time = ?, status = 'pending', attempt_count = 0, updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
            array_merge([$newDate, $newTime, $scheduleId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
        );
        return ['success' => true, 'message' => 'Call rescheduled'];
    }

    public function cancelSchedule($scheduleId)
    {
        $this->db->execute(
            "UPDATE ai_calling_schedule SET status = 'cancelled', updated_at = NOW() WHERE id = ?" . $this->tenantSql(),
            array_merge([$scheduleId], $this->tenantId() > 1 ? [$this->tenantId()] : [])
        );
        return ['success' => true, 'message' => 'Call cancelled'];
    }

    protected function generateResponse($intent, $session)
    {
        $responses = [
            'price_inquiry' => 'I understand you are interested in pricing. We have various options available. Would you like me to share our current rates and available payment plans?',
            'site_visit' => 'I would be happy to schedule a site visit for you. I can arrange a visit at your convenience. What date and time works best for you?',
            'booking' => 'Great interest! Let me connect you with our sales team to proceed with the booking process. One of our representatives will assist you shortly.',
            'loan_inquiry' => 'We have tie-ups with major banks for home loans. Would you like to know about the financing options and EMI plans available?',
            'disinterest' => 'I understand, thank you for your time today. Feel free to reach out if you have any questions in the future. Have a great day!',
            'general' => 'I see. Could you tell me more about what you are looking for? We have residential plots, commercial spaces, and ready-to-move properties in prime locations.',
        ];

        return $responses[$intent] ?? $responses['general'];
    }

    protected function logActivity($type, $description, $leadId = null)
    {
        if (!$leadId) return;
        try {
            $tid = $this->tenantId();
            $this->db->execute(
                "INSERT INTO lead_activities (lead_id, activity_type, description, tenant_id, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                [$leadId, $type, $description, $tid > 1 ? $tid : 1]
            );
        } catch (\Exception $e) {
                    error_log("VoiceCallService.php: " . $e->getMessage());
        }
    }
}
