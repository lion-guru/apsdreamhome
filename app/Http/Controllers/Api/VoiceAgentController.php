<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class VoiceAgentController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->parseJsonBody();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    /**
     * Populate $_POST from a JSON request body so controllers can read
     * $_POST['key'] regardless of whether the client sent form-encoded
     * or application/json payloads.
     */
    private function parseJsonBody(): void
    {
        if (empty($_POST)
            && !empty($_SERVER['CONTENT_TYPE'])
            && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
        ) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $_POST = $data;
            }
        }
    }

    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function startCall()
    {
        try {
            $leadId = $_POST['lead_id'] ?? null;
            $phone = $_POST['phone'] ?? '';
            $agentType = $_POST['agent_type'] ?? 'followup';
            $scriptCode = $_POST['script_code'] ?? 'default';

            if (!$leadId && !$phone) {
                return $this->jsonResponse(['success' => false, 'error' => 'lead_id or phone is required'], 400);
            }

            if ($leadId) {
                $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
                if (!$lead) {
                    return $this->jsonResponse(['success' => false, 'error' => 'Lead not found'], 404);
                }
                $phone = $phone ?: ($lead['phone'] ?? '');
            }

            $sessionId = bin2hex(random_bytes(16));

            $agent = $this->db->fetch(
                "SELECT * FROM ai_calling_agents WHERE status = 'active' ORDER BY current_calls ASC LIMIT 1"
            );

            $agentName = $agent['agent_name'] ?? 'AI Calling Agent';
            $agentId = $agent['id'] ?? null;

            $script = null;
            try {
                $script = $this->db->fetch(
                    "SELECT * FROM ai_calling_scripts WHERE script_code = ? AND is_active = 1 LIMIT 1",
                    [$scriptCode]
                );
            } catch (\Exception $e) {
                $script = null;
            }

            $this->db->execute(
                "INSERT INTO ai_call_sessions (session_id, lead_id, phone, agent_id, agent_type, script_code, agent_name, status, started_at, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'in_progress', NOW(), NOW())",
                [$sessionId, $leadId, $phone, $agentId, $agentType, $scriptCode, $agentName]
            );

            if ($agentId) {
                $this->db->execute(
                    "UPDATE ai_calling_agents SET current_calls = current_calls + 1 WHERE id = ?",
                    [$agentId]
                );
            }

            return $this->jsonResponse([
                'success' => true,
                'session_id' => $sessionId,
                'agent_name' => $agentName,
                'agent_type' => $agentType,
                'script_info' => $script ? [
                    'name' => $script['name'] ?? '',
                    'greeting' => $script['greeting'] ?? '',
                    'questions' => isset($script['questions']) ? json_decode($script['questions'], true) : []
                ] : null,
                'lead_phone' => $phone
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function processResponse()
    {
        try {
            $sessionId = $_POST['session_id'] ?? '';
            $transcript = $_POST['transcript'] ?? '';
            $leadId = $_POST['lead_id'] ?? null;

            if (!$sessionId || !$transcript) {
                return $this->jsonResponse(['success' => false, 'error' => 'session_id and transcript are required'], 400);
            }

            $session = $this->db->fetch("SELECT * FROM ai_call_sessions WHERE session_id = ?", [$sessionId]);
            if (!$session) {
                return $this->jsonResponse(['success' => false, 'error' => 'Session not found'], 404);
            }

            $transcriptLower = mb_strtolower($transcript);
            $intent = 'unknown';
            if (preg_match('/(price|cost|kitna|rate|budget)/', $transcriptLower)) $intent = 'price_inquiry';
            elseif (preg_match('/(visit|dekhna|dikhana|site|location)/', $transcriptLower)) $intent = 'site_visit';
            elseif (preg_match('/(booking|book|register|buy|kharid)/', $transcriptLower)) $intent = 'booking';
            elseif (preg_match('/(loan|finance|emi|installment)/', $transcriptLower)) $intent = 'loan_inquiry';
            elseif (preg_match('/(size|area|sqft|dimension|plot|measure)/', $transcriptLower)) $intent = 'size_inquiry';
            elseif (preg_match('/(bye|goodbye|nhi chahiye|no|not interested|baad mein)/', $transcriptLower)) $intent = 'disinterest';
            elseif (preg_match('/(hello|hi|namaste|good)/', $transcriptLower)) $intent = 'greeting';

            $qualificationResult = null;
            if ($intent !== 'disinterest' && $intent !== 'greeting' && $intent !== 'unknown') {
                $qualificationResult = [
                    'is_interested' => true,
                    'intent' => $intent,
                    'needs_followup' => in_array($intent, ['price_inquiry', 'loan_inquiry', 'size_inquiry']),
                    'needs_site_visit' => $intent === 'site_visit'
                ];
            } elseif ($intent === 'disinterest') {
                $qualificationResult = ['is_interested' => false, 'intent' => $intent, 'needs_followup' => false];
            }

            $responseText = '';
            $nextAction = 'continue';
            $suggestedSlots = [];

            switch ($intent) {
                case 'price_inquiry':
                    $responseText = 'I understand you are interested in pricing. Would you like me to share our current rates and available payment plans?';
                    $suggestedSlots = ['ask_budget', 'ask_property_type'];
                    break;
                case 'site_visit':
                    $responseText = 'I would be happy to schedule a site visit for you. What time would be convenient?';
                    $nextAction = 'schedule_visit';
                    $suggestedSlots = ['preferred_date', 'preferred_time'];
                    break;
                case 'booking':
                    $responseText = 'Great interest! Let me connect you with our sales team to proceed with the booking.';
                    $nextAction = 'transfer_to_sales';
                    break;
                case 'loan_inquiry':
                    $responseText = 'We have tie-ups with major banks for home loans. Would you like to know about the financing options available?';
                    $suggestedSlots = ['ask_budget', 'ask_employment_type'];
                    break;
                case 'size_inquiry':
                    $responseText = 'We have various plot sizes available. What size are you looking for?';
                    $suggestedSlots = ['ask_size', 'ask_budget'];
                    break;
                case 'disinterest':
                    $responseText = 'I understand, thank you for your time. Feel free to reach out if you need anything in the future.';
                    $nextAction = 'end_call';
                    break;
                case 'greeting':
                    $responseText = 'Hello! Thank you for calling APS Dream Home. How can I assist you today? Are you looking to buy a property or just exploring?';
                    break;
                default:
                    $responseText = 'I see. Could you tell me more about what you are looking for? We have residential plots, commercial spaces, and ready-to-move properties.';
            }

            $existingTranscript = $session['transcript'] ?? '';
            $updatedTranscript = $existingTranscript ? $existingTranscript . "\n[USER]: " . $transcript . "\n[AI]: " . $responseText : "[USER]: " . $transcript . "\n[AI]: " . $responseText;

            $this->db->execute(
                "UPDATE ai_call_sessions SET transcript = ?, last_intent = ?, last_response = ?, analysis = ?, updated_at = NOW() WHERE session_id = ?",
                [$updatedTranscript, $intent, $responseText, json_encode(['intent' => $intent, 'qualification' => $qualificationResult, 'next_action' => $nextAction, 'suggested_slots' => $suggestedSlots]), $sessionId]
            );

            if ($leadId) {
                $this->db->execute(
                    "INSERT INTO lead_activities (lead_id, activity_type, description, metadata, created_at)
                     VALUES (?, 'voice_response', ?, ?, NOW())",
                    [$leadId, "Voice response processed: intent={$intent}", json_encode(['session_id' => $sessionId, 'transcript' => $transcript, 'intent' => $intent])]
                );
            }

            return $this->jsonResponse([
                'success' => true,
                'session_id' => $sessionId,
                'intent' => $intent,
                'next_action' => $nextAction,
                'response_text' => $responseText,
                'suggested_slots' => $suggestedSlots,
                'qualification_result' => $qualificationResult
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getSession($id = null)
    {
        try {
            $session = $this->db->fetch(
                "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.email as lead_email,
                        l.property_interest, l.budget_range, l.status as lead_status,
                        a.agent_name as agent_name, a.status as agent_type_name
                 FROM ai_call_sessions s
                 LEFT JOIN leads l ON s.lead_id = l.id
                 LEFT JOIN ai_calling_agents a ON s.agent_id = a.id
                 WHERE s.id = ? OR s.session_id = ?",
                [$id, $id]
            );

            if (!$session) {
                return $this->jsonResponse(['success' => false, 'error' => 'Session not found'], 404);
            }

            if ($session['transcript']) {
                $session['transcript_lines'] = explode("\n", $session['transcript']);
            }
            if ($session['analysis']) {
                $session['analysis'] = json_decode($session['analysis'], true);
            }

            return $this->jsonResponse(['success' => true, 'session' => $session]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function endCall()
    {
        try {
            $sessionId = $_POST['session_id'] ?? '';
            $summary = $_POST['summary'] ?? '';
            $sentiment = $_POST['sentiment'] ?? 'neutral';
            $outcome = $_POST['outcome'] ?? 'unknown';

            if (!$sessionId) {
                return $this->jsonResponse(['success' => false, 'error' => 'session_id is required'], 400);
            }

            $session = $this->db->fetch("SELECT * FROM ai_call_sessions WHERE session_id = ?", [$sessionId]);
            if (!$session) {
                return $this->jsonResponse(['success' => false, 'error' => 'Session not found'], 404);
            }

            $this->db->execute(
                "UPDATE ai_call_sessions SET status = 'completed', ended_at = NOW(), summary = ?, sentiment = ?, outcome = ?, updated_at = NOW() WHERE session_id = ?",
                [$summary, $sentiment, $outcome, $sessionId]
            );

            if ($session['agent_id']) {
                $this->db->execute(
                    "UPDATE ai_calling_agents SET current_calls = GREATEST(current_calls - 1, 0) WHERE id = ?",
                    [$session['agent_id']]
                );
            }

            if ($session['lead_id'] && $outcome === 'interested') {
                $lead = $this->db->fetch("SELECT name, phone, email, property_interest, budget_range, city, state FROM leads WHERE id = ?", [$session['lead_id']]);
                if ($lead) {
                    $this->db->execute(
                        "INSERT INTO ai_call_extracted_leads (lead_id, session_id, name, phone, email, property_interest, budget_range, city, state, source, summary, extracted_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'voice_call', ?, NOW())",
                        [
                            $session['lead_id'], $sessionId, $lead['name'], $lead['phone'], $lead['email'],
                            $lead['property_interest'], $lead['budget_range'], $lead['city'], $lead['state'], $summary
                        ]
                    );
                }
            }

            if ($outcome === 'followup_needed') {
                $this->db->execute(
                    "INSERT INTO ai_calling_schedule (lead_id, user_id, phone, agent_type, scheduled_date, scheduled_time, priority, status, created_at)
                     VALUES (?, ?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 'medium', 'scheduled', NOW())",
                    [$session['lead_id'], $session['lead_id'], $session['phone'] ?? '', 'followup']
                );
            }

            return $this->jsonResponse([
                'success' => true,
                'session_id' => $sessionId,
                'status' => 'completed',
                'duration_seconds' => $session['started_at'] ? (time() - strtotime($session['started_at'])) : 0,
                'outcome' => $outcome,
                'sentiment' => $sentiment
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getSchedule()
    {
        try {
            $date = $_GET['date'] ?? date('Y-m-d');
            $agentType = $_GET['agent_type'] ?? null;
            $status = $_GET['status'] ?? null;

            $sql = "SELECT s.*, l.name as lead_name, l.phone as lead_phone,
                           a.agent_name as agent_name, a.status as agent_type
                    FROM ai_calling_schedule s
                    LEFT JOIN leads l ON s.lead_id = l.id
                    LEFT JOIN ai_calling_agents a ON s.ai_agent_id = a.id
                    WHERE (s.scheduled_date = ? OR ? IS NULL)";
            $params = [$date, $date];

            if ($agentType) {
                $sql .= " AND s.agent_type = ?";
                $params[] = $agentType;
            }

            if ($status) {
                $sql .= " AND s.status = ?";
                $params[] = $status;
            }

            $sql .= " ORDER BY s.priority ASC, s.scheduled_date ASC LIMIT 200";

            $schedule = $this->db->fetchAll($sql, $params);

            return $this->jsonResponse(['success' => true, 'schedule' => $schedule]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function scheduleCall()
    {
        try {
            $leadId = $_POST['lead_id'] ?? null;
            $phone = $_POST['phone'] ?? '';
            $agentType = $_POST['agent_type'] ?? 'followup';
            $scheduledDate = $_POST['scheduled_date'] ?? date('Y-m-d', strtotime('+1 day'));
            $priority = $_POST['priority'] ?? 'medium';
            $notes = $_POST['notes'] ?? '';

            if (!$leadId) {
                return $this->jsonResponse(['success' => false, 'error' => 'lead_id is required'], 400);
            }

            $lead = $this->db->fetch("SELECT id, phone FROM leads WHERE id = ?", [$leadId]);
            if (!$lead) {
                return $this->jsonResponse(['success' => false, 'error' => 'Lead not found'], 404);
            }

            $this->db->execute(
                "INSERT INTO ai_calling_schedule (lead_id, user_id, phone, agent_type, scheduled_date, scheduled_time, priority, notes, status, created_at)
                 VALUES (?, ?, ?, ?, ?, '09:00:00', ?, ?, 'scheduled', NOW())",
                [$leadId, $lead['user_id'] ?? null, $phone ?: ($lead['phone'] ?? ''), $agentType, $scheduledDate, $priority, $notes]
            );

            $scheduleId = $this->db->lastInsertId();

            return $this->jsonResponse([
                'success' => true,
                'schedule_id' => $scheduleId,
                'lead_id' => $leadId,
                'scheduled_date' => $scheduledDate,
                'agent_type' => $agentType
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getExtractedLeads()
    {
        try {
            $status = $_GET['status'] ?? null;
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
            $to = $_GET['to'] ?? date('Y-m-d');

            $sql = "SELECT e.*, l.name as lead_name, l.phone as lead_phone,
                           s.session_id, s.sentiment, s.duration_seconds
                    FROM ai_call_extracted_leads e
                    LEFT JOIN leads l ON e.lead_id = l.id
                    LEFT JOIN ai_call_sessions s ON e.session_id = s.session_id
                    WHERE DATE(e.extracted_at) BETWEEN ? AND ?";
            $params = [$from, $to];

            if ($status) {
                $sql .= " AND e.converted = ?";
                $params[] = $status === 'converted' ? 1 : 0;
            }

            $sql .= " ORDER BY e.extracted_at DESC LIMIT 200";

            $leads = $this->db->fetchAll($sql, $params);

            return $this->jsonResponse(['success' => true, 'extracted_leads' => $leads]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function convertExtractedLead($id)
    {
        try {
            $extracted = $this->db->fetch("SELECT * FROM ai_call_extracted_leads WHERE id = ?", [$id]);
            if (!$extracted) {
                return $this->jsonResponse(['success' => false, 'error' => 'Extracted lead not found'], 404);
            }

            if ($extracted['converted']) {
                return $this->jsonResponse(['success' => false, 'error' => 'Lead already converted'], 400);
            }

            $source = $extracted['source'] ?? 'voice_call';
            $leadData = [
                'name' => $extracted['name'],
                'phone' => $extracted['phone'],
                'email' => $extracted['email'],
                'property_interest' => $extracted['property_interest'],
                'budget_range' => $extracted['budget_range'],
                'city' => $extracted['city'],
                'state' => $extracted['state'],
                'source' => $source,
                'status' => 'interested',
                'notes' => "Extracted from voice call session: {$extracted['session_id']}\nSummary: {$extracted['summary']}",
                'created_at' => date('Y-m-d H:i:s')
            ];

            $columns = implode(', ', array_keys($leadData));
            $placeholders = implode(', ', array_fill(0, count($leadData), '?'));
            $this->db->execute(
                "INSERT INTO leads ({$columns}) VALUES ({$placeholders})",
                array_values($leadData)
            );

            $newLeadId = $this->db->lastInsertId();

            $this->db->execute(
                "UPDATE ai_call_extracted_leads SET converted = 1, converted_lead_id = ?, converted_at = NOW() WHERE id = ?",
                [$newLeadId, $id]
            );

            return $this->jsonResponse([
                'success' => true,
                'lead_id' => $newLeadId,
                'extracted_lead_id' => $id,
                'message' => 'Lead converted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getStats()
    {
        try {
            $today = date('Y-m-d');

            $totalCallsToday = $this->db->fetch(
                "SELECT COUNT(*) as count FROM ai_call_sessions WHERE DATE(started_at) = ?",
                [$today]
            );

            $connectedCalls = $this->db->fetch(
                "SELECT COUNT(*) as count FROM ai_call_sessions WHERE DATE(started_at) = ? AND status = 'completed'",
                [$today]
            );

            $interestedLeads = $this->db->fetch(
                "SELECT COUNT(*) as count FROM ai_call_sessions WHERE DATE(started_at) = ? AND outcome = 'interested'",
                [$today]
            );

            $bookingsMade = $this->db->fetch(
                "SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = ?",
                [$today]
            );

            $avgDuration = $this->db->fetch(
                "SELECT AVG(TIMESTAMPDIFF(SECOND, started_at, ended_at)) as avg_sec
                 FROM ai_call_sessions WHERE DATE(started_at) = ? AND ended_at IS NOT NULL",
                [$today]
            );

            $total = (int)($totalCallsToday['count'] ?? 0);
            $connected = (int)($connectedCalls['count'] ?? 0);

            $conversionRate = $total > 0 ? round(($connected / $total) * 100, 1) : 0;

            return $this->jsonResponse([
                'success' => true,
                'stats' => [
                    'total_calls_today' => $total,
                    'connected_calls' => $connected,
                    'interested_leads' => (int)($interestedLeads['count'] ?? 0),
                    'bookings_made' => (int)($bookingsMade['count'] ?? 0),
                    'conversion_rate' => $conversionRate,
                    'avg_call_duration_seconds' => round((float)($avgDuration['avg_sec'] ?? 0))
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getCallHistory()
    {
        try {
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
            $offset = ($page - 1) * $limit;
            $status = $_GET['status'] ?? null;
            $agentType = $_GET['agent_type'] ?? null;
            $from = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
            $to = $_GET['to'] ?? date('Y-m-d');

            $where = "WHERE DATE(s.started_at) BETWEEN ? AND ?";
            $params = [$from, $to];

            if ($status) {
                $where .= " AND s.status = ?";
                $params[] = $status;
            }

            if ($agentType) {
                $where .= " AND s.agent_type = ?";
                $params[] = $agentType;
            }

            $total = $this->db->fetch(
                "SELECT COUNT(*) as count FROM ai_call_sessions s {$where}",
                $params
            );

            $calls = $this->db->fetchAll(
                "SELECT s.*, l.name as lead_name, l.phone as lead_phone,
                        a.agent_name as agent_name
                 FROM ai_call_sessions s
                 LEFT JOIN leads l ON s.lead_id = l.id
                 LEFT JOIN ai_calling_agents a ON s.agent_id = a.id
                 {$where}
                 ORDER BY s.started_at DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            );

            foreach ($calls as &$call) {
                if ($call['started_at'] && $call['ended_at']) {
                    $call['duration_seconds'] = strtotime($call['ended_at']) - strtotime($call['started_at']);
                } else {
                    $call['duration_seconds'] = 0;
                }
            }
            unset($call);

            return $this->jsonResponse([
                'success' => true,
                'calls' => $calls,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => (int)($total['count'] ?? 0),
                    'total_pages' => max(1, ceil((int)($total['count'] ?? 0) / $limit))
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
