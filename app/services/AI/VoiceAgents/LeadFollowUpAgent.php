<?php

namespace App\Services\AI\VoiceAgents;

use App\Services\AI\Agents\BaseAgent;
use App\Services\Voice\VoiceCallService;
use App\Services\Voice\TwilioVoiceService;
use Exception;

class LeadFollowUpAgent extends BaseAgent
{
    private $follow_up_delays = [
        'new' => 1,
        'contacted' => 3,
        'qualified' => 7,
        'proposal' => 3,
        'negotiation' => 2,
        'nurture' => 15
    ];

    private $status_labels = [
        'new' => 'New Lead - Immediate follow-up needed',
        'contacted' => 'Contacted - Sent property details',
        'qualified' => 'Qualified - Interested buyer',
        'proposal' => 'Proposal Sent - Awaiting decision',
        'negotiation' => 'Negotiation - Price discussion',
        'closed_won' => 'Closed Won - Deal finalized',
        'closed_lost' => 'Closed Lost - Not interested',
        'nurture' => 'Nurture - Long term follow-up'
    ];

    public function __construct()
    {
        parent::__construct(12, 'Lead Follow-up Agent');
    }

    public function process($input, $context = [])
    {
        $this->status = 'processing';

        $action = $input['action'] ?? 'process_followup';
        $lead_id = $input['lead_id'] ?? null;

        if ($action !== 'schedule_only' && !$lead_id) {
            return $this->handleError('Lead ID is required for follow-up processing');
        }

        $this->logActivity('FOLLOWUP_PROCESSING', "Action: $action, Lead: " . ($lead_id ?? 'N/A'));

        switch ($action) {
            case 'get_context':
                $result = $this->getLeadContext($lead_id);
                break;
            case 'get_script':
                $lead_type = $input['lead_type'] ?? 'general';
                $result = $this->getFollowUpScript($lead_type);
                break;
            case 'update_status':
                $result = $this->updateLeadStatus($lead_id, $input['status'] ?? '', $input['notes'] ?? '');
                break;
            case 'schedule_next':
                $recommended = $input['recommended_date'] ?? null;
                $result = $this->scheduleNextFollowUp($lead_id, $recommended);
                break;
            case 'log_call':
                $result = $this->logCallAttempt($lead_id, $input['result'] ?? '', $input['notes'] ?? '');
                break;
            case 'schedule_only':
                $result = $this->scheduleNextFollowUp(
                    $input['lead_id'] ?? null,
                    $input['recommended_date'] ?? null
                );
                break;
            default:
                $result = $this->processFollowUp($lead_id, $input);
        }

        $this->status = 'ready';
        return $result;
    }

    public function processFollowUp($leadId, $input = [])
    {
        $context = $this->getLeadContext($leadId);
        if (!$context['success']) {
            return $context;
        }

        $lead = $context['lead'];
        $script = $this->getFollowUpScript($lead['status'] ?? 'general');

        $follow_up_notes = $input['notes'] ?? '';
        $call_connected = $input['call_connected'] ?? false;
        $customer_response = $input['response'] ?? '';

        $auto_status = $this->determineAutoStatus($lead, $customer_response, $call_connected);

        if ($auto_status !== 'no_change') {
            $this->updateLeadStatus($leadId, $auto_status);
        }

        $next_follow_up = $this->scheduleNextFollowUp($leadId);

        $call_log = $this->logCallAttempt($leadId, $call_connected ? 'connected' : 'no_answer', $follow_up_notes);

        return [
            'success' => true,
            'lead_id' => $leadId,
            'lead_name' => $lead['name'] ?? 'Unknown',
            'current_status' => $lead['status'] ?? 'new',
            'new_status' => $auto_status !== 'no_change' ? $auto_status : ($lead['status'] ?? 'new'),
            'next_follow_up' => $next_follow_up,
            'script_used' => $script['script_name'] ?? 'N/A',
            'call_logged' => $call_log['success'] ?? false,
            'message' => 'Follow-up processed successfully'
        ];
    }

    public function getLeadContext($leadId)
    {
        $lead = $this->db->fetch("SELECT * FROM leads WHERE id = ?", [$leadId]);
        if (!$lead) {
            return ['success' => false, 'error' => 'Lead not found'];
        }

        $call_history = $this->db->fetchAll(
            "SELECT id, call_sid, ai_summary as summary, sentiment, follow_up_date, created_at
             FROM ai_call_sessions WHERE lead_id = ? ORDER BY created_at DESC LIMIT 5",
            [$leadId]
        );

        $property_interest = [];
        if ($lead['property_interest']) {
            $property = $this->db->fetch(
                "SELECT id, name, property_type, location, price FROM user_properties WHERE id = ?",
                [$lead['property_interest']]
            );
            if ($property) {
                $property_interest = $property;
            }
        }

        $schedule = $this->db->fetch(
            "SELECT id, scheduled_date, scheduled_time, status FROM ai_calling_schedule WHERE lead_id = ? ORDER BY created_at DESC LIMIT 1",
            [$leadId]
        );

        return [
            'success' => true,
            'lead' => $lead,
            'call_history' => $call_history,
            'property_interest' => $property_interest,
            'last_schedule' => $schedule,
            'follow_up_recommendation' => $this->getFollowUpRecommendation($lead)
        ];
    }

    public function getFollowUpScript($leadType)
    {
        try {
            $script = $this->db->fetch(
                "SELECT * FROM ai_call_scripts WHERE script_code = ? OR script_name LIKE ? ORDER BY usage_count ASC LIMIT 1",
                ["follow_up_$leadType", "%$leadType%"]
            );
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }

        if (!$script) {
            $script = $this->db->fetch(
                "SELECT * FROM ai_call_scripts WHERE script_code = 'follow_up_call' ORDER BY usage_count ASC LIMIT 1"
            );
        }

        if ($script) {
            $this->db->execute("UPDATE ai_call_scripts SET usage_count = usage_count + 1 WHERE id = ?", [$script['id']]);
        }

        return $script ?: [
            'script_name' => 'Default Follow-up',
            'script_code' => 'follow_up_default',
            'greeting_text' => 'Hello, this call is regarding your property inquiry.',
            'introduction_text' => 'I am following up on your recent interest in our properties.',
            'questions_to_ask' => json_encode(['Are you still interested?', 'Do you have any questions?']),
            'closing_text' => 'Thank you for your time. We will keep you updated.'
        ];
    }

    public function updateLeadStatus($leadId, $status, $notes = '')
    {
        $valid_statuses = ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost', 'nurture'];
        if ($status && !in_array($status, $valid_statuses)) {
            return ['success' => false, 'error' => "Invalid status: $status"];
        }

        if ($status) {
            $update_fields = "status = ?, updated_at = NOW()";
            $params = [$status];

            if ($notes) {
                $update_fields .= ", notes = CONCAT(IFNULL(notes, ''), ?, '\n')";
                $params[] = "[AI] $notes\n";
            }

            $params[] = $leadId;
            $this->db->execute("UPDATE leads SET $update_fields WHERE id = ?", $params);

            $this->logActivity('LEAD_STATUS_UPDATED', "Lead #$leadId -> $status: $notes");

            return [
                'success' => true,
                'lead_id' => $leadId,
                'new_status' => $status,
                'message' => "Lead status updated to $status"
            ];
        }

        return ['success' => false, 'error' => 'No status provided'];
    }

    public function scheduleNextFollowUp($leadId, $recommendedDate = null)
    {
        if (!$recommendedDate) {
            $lead = $this->db->fetch("SELECT status, name, phone FROM leads WHERE id = ?", [$leadId]);
            if (!$lead) {
                return ['success' => false, 'error' => 'Lead not found'];
            }

            $delay_days = $this->follow_up_delays[$lead['status']] ?? 7;
            $recommendedDate = date('Y-m-d', strtotime("+$delay_days days"));
        }

        $existing = $this->db->fetch(
            "SELECT id FROM ai_calling_schedule WHERE lead_id = ? AND status IN ('pending','processing')",
            [$leadId]
        );

        if (!$existing) {
            try {
                $lead = $this->db->fetch("SELECT phone, status FROM leads WHERE id = ?", [$leadId]);
                $voiceSvc = new VoiceCallService();
                $agent = $this->db->fetch(
                    "SELECT agent_id FROM ai_calling_agents WHERE agent_type LIKE '%follow%' OR agent_id = 'agent_12' AND status = 'active' LIMIT 1"
                );
                $agentId = $agent['agent_id'] ?? 'agent_12';
                $phone = $lead['phone'] ?? '';
                $script = $this->getScriptTemplateForStatus($lead['status'] ?? 'new');
                $priority = in_array($lead['status'] ?? '', ['qualified', 'negotiation']) ? 'high' : 'medium';
                $voiceSvc->scheduleCall($leadId, $phone, $agentId, $recommendedDate, '10:00:00', $script, $priority);
            } catch (\Exception $e) {
                error_log('LeadFollowUpAgent::scheduleNextFollowUp VoiceCallService error: ' . $e->getMessage());
                $this->db->insert('ai_calling_schedule', [
                    'lead_id' => $leadId, 'phone' => '', 'scheduled_date' => $recommendedDate,
                    'scheduled_time' => '10:00', 'priority' => 'medium', 'status' => 'pending',
                    'script_template' => 'follow_up_call', 'max_attempts' => 3
                ]);
            }
        } else {
            $this->db->execute(
                "UPDATE ai_calling_schedule SET scheduled_date = ?, updated_at = NOW() WHERE id = ?",
                [$recommendedDate, $existing['id']]
            );
        }

        if ($leadId) {
            $this->db->execute(
                "UPDATE leads SET next_activity_date = ? WHERE id = ?",
                [$recommendedDate . ' 10:00:00', $leadId]
            );
        }

        $this->logActivity('FOLLOWUP_SCHEDULED', "Lead #$leadId on $recommendedDate");

        return [
            'success' => true,
            'lead_id' => $leadId,
            'scheduled_date' => $recommendedDate,
            'message' => "Next follow-up scheduled for $recommendedDate"
        ];
    }

    protected function getScriptTemplateForStatus($status)
    {
        $map = [
            'new' => 'follow_up_new_lead',
            'contacted' => 'follow_up_contacted',
            'qualified' => 'follow_up_qualified',
            'proposal' => 'follow_up_proposal',
            'negotiation' => 'follow_up_negotiation',
            'nurture' => 'follow_up_nurture',
        ];
        return $map[$status] ?? 'follow_up_call';
    }

    public function logCallAttempt($leadId, $result, $notes = '')
    {
        $sentiment = 'neutral';
        if (stripos($notes, 'interested') !== false || stripos($notes, 'positive') !== false) {
            $sentiment = 'positive';
        } elseif (stripos($notes, 'not interested') !== false || stripos($notes, 'negative') !== false) {
            $sentiment = 'negative';
        }

        $follow_up_needed = ($result === 'no_answer' || $result === 'busy') ? 1 : 0;

        $this->db->insert('ai_call_sessions', [
            'lead_id' => $leadId,
            'ai_agent_id' => $this->agentId,
            'ai_summary' => $notes,
            'sentiment' => $sentiment,
            'follow_up_required' => $follow_up_needed,
            'follow_up_date' => $follow_up_needed ? date('Y-m-d H:i:s', strtotime('+1 day')) : null,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $log_id = $this->db->lastInsertId();

        $this->logActivity('CALL_LOGGED', "Lead #$leadId, Result: $result, Log #$log_id");

        return [
            'success' => true,
            'log_id' => $log_id,
            'lead_id' => $leadId,
            'result' => $result,
            'sentiment' => $sentiment,
            'follow_up_needed' => (bool)$follow_up_needed
        ];
    }

    private function getFollowUpRecommendation($lead)
    {
        $status = $lead['status'] ?? 'new';
        $delay = $this->follow_up_delays[$status] ?? 7;
        $label = $this->status_labels[$status] ?? 'Unknown status';

        return [
            'current_status' => $status,
            'status_label' => $label,
            'recommended_follow_up_days' => $delay,
            'next_suggested_action' => $this->getSuggestedAction($status)
        ];
    }

    private function getSuggestedAction($status)
    {
        $actions = [
            'new' => 'Send introductory property details and call within 24 hours',
            'contacted' => 'Share relevant property listings and qualify budget',
            'qualified' => 'Schedule site visit or send proposal',
            'proposal' => 'Follow up on proposal, address objections',
            'negotiation' => 'Discuss pricing and payment terms',
            'nurture' => 'Send monthly property updates and market trends',
            'closed_won' => 'Thank you call and request for referrals',
            'closed_lost' => 'Archive lead or move to nurture for future projects'
        ];
        return $actions[$status] ?? 'General follow-up';
    }

    private function determineAutoStatus($lead, $response, $callConnected)
    {
        if (!$callConnected) {
            return 'no_change';
        }

        $response_lower = strtolower($response);
        $status = $lead['status'] ?? 'new';

        if (stripos($response_lower, 'not interested') !== false ||
            stripos($response_lower, 'no thanks') !== false ||
            stripos($response_lower, 'don\'t call') !== false) {
            return 'closed_lost';
        }

        if (stripos($response_lower, 'interested') !== false ||
            stripos($response_lower, 'want to buy') !== false ||
            stripos($response_lower, 'site visit') !== false ||
            stripos($response_lower, 'meeting') !== false) {
            if ($status === 'new' || $status === 'contacted') {
                return 'qualified';
            }
            return 'negotiation';
        }

        if (stripos($response_lower, 'price') !== false ||
            stripos($response_lower, 'cost') !== false ||
            stripos($response_lower, 'emi') !== false) {
            if ($status === 'new') {
                return 'contacted';
            }
            if ($status === 'contacted') {
                return 'qualified';
            }
            return 'proposal';
        }

        if (stripos($response_lower, 'later') !== false ||
            stripos($response_lower, 'busy') !== false ||
            stripos($response_lower, 'call later') !== false) {
            return 'no_change';
        }

        if ($status === 'new' && $callConnected) {
            return 'contacted';
        }

        return 'no_change';
    }

    /**
     * Execute a real outbound Twilio call to follow up on a lead.
     * Wires this AI agent into TwilioVoiceService (Cluster 2 - 2026-06-05).
     *
     * @param int    $leadId
     * @param string $phone        E.164 phone
     * @param array  $context      { leadName, lastInquiry, daysSinceContact }
     * @return array{success:bool,sid:?string,error:?string}
     */
    public function executeCall($leadId, $phone, array $context = [])
    {
        try {
            $voice = new TwilioVoiceService();
            $leadName = $context['leadName'] ?? 'there';

            $baseUrl = $this->resolveBaseUrl();
            $twimlUrl = $baseUrl . '/api/twilio/voice?type=follow_up&lead_id=' . $leadId;

            $result = $voice->makeCall($phone, $twimlUrl, null, [
                'record'      => true,
                'leadId'      => $leadId,
                'agentId'     => $this->agentId,
                'sessionMeta' => [
                    'agent' => $this->agentName,
                    'kind'  => 'lead_followup',
                    'context' => $context,
                ],
                'statusCallback' => $baseUrl . '/api/twilio/voice/status',
            ]);

            $this->logActivity('OUTBOUND_CALL_INITIATED', "Follow-up call to lead $leadId at $phone (SID: " . ($result['sid'] ?? 'none') . ")");
            return $result;
        } catch (\Throwable $e) {
            error_log("LeadFollowUpAgent::executeCall failed: " . $e->getMessage());
            return ['success' => false, 'sid' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resolve the public base URL Twilio should call back to.
     */
    protected function resolveBaseUrl()
    {
        if (!empty($_ENV['APP_URL'])) return rtrim($_ENV['APP_URL'], '/');
        if (!empty($_ENV['BASE_URL_PUBLIC'])) return rtrim($_ENV['BASE_URL_PUBLIC'], '/');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host;
    }
}
