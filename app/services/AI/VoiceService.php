<?php

namespace App\Services\AI;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use App\Traits\ServiceTenantTrait;
use App\Services\Communication\CommunicationGateway;

/**
 * Unified VoiceService
 *
 * Consolidates all voice/telephony functionality from:
 *   - Voice\VoiceCallService        (call scheduling, outbound/inbound call management)
 *   - Voice\TwilioVoiceService     (Twilio integration, TwiML generation)
 *   - Voice\AIVoicePipeline        (AI voice pipeline: STT → LLM → TTS)
 *   - CRMVoiceService              (CRM voice integration, voice commands)
 *   - VoiceAssistantService        (voice assistant with knowledge base)
 *   - AI\VoiceProviderManager      (multi-provider voice abstraction)
 *   - Providers\AbstractVoiceProvider  (abstract provider interface)
 *   - Providers\VoiceProviderInterface (voice provider interface)
 *
 * Single entry point for all voice/telephony operations:
 *   - Outbound & inbound call management
 *   - Multi-provider voice calling (Exotel, Twilio, Plivo, Knowlarity, Kaleyra)
 *   - AI voice pipeline (Speech-to-Text → Language Model → Text-to-Speech)
 *   - Voice assistant with knowledge base and RBAC
 *   - Voice commands and speech recognition
 *   - Call recording, transcription, and analytics
 *   - Voice webhook handling and signature validation
 *   - Call session management and analytics
 *
 * Backed by voice_call_logs, ai_call_sessions, ai_call_extracted_leads,
 * voice_call_logs, telecaller_commissions, telecaller_daily_tasks,
 * telecaller_performance tables.
 */
class VoiceService
{
    use ServiceTenantTrait;

    private $db;
    private $voiceProviderManager;
    private $notificationService;
    private $providers = [];

    public const VOICE_PROVIDERS = [
        'exotel'   => 'Exotel',
        'twilio'   => 'Twilio',
        'plivo'    => 'Plivo',
        'knowlarity' => 'Knowlarity',
        'kaleyra'  => 'Kaleyra',
    ];

    public const CALL_STATUSES = [
        'scheduled'    => 'Scheduled',
        'processing'   => 'Processing',
        'ringing'      => 'Ringing',
        'answered'     => 'Answered',
        'completed'    => 'Completed',
        'failed'       => 'Failed',
        'busy'         => 'Busy',
        'no-answer'    => 'No Answer',
        'no-answer'    => 'No Answer',
        'cancelled'    => 'Cancelled',
    ];

    public const CALL_DIRECTIONS = [
        'inbound'  => 'Inbound',
        'outbound' => 'Outbound',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->voiceProviderManager = new \App\Services\AI\VoiceProviderManager();
        $this->notificationService = new \App\Services\Communication\NotificationService();
        $this->loadProviders();
    }

    /**
     * Load all available voice providers from config
     */
    private function loadProviders(): void
    {
        try {
            $tid = $this->tenantId();
            $stmt = $this->db->prepare("SELECT * FROM voice_provider_configs WHERE is_active = 1" . ($tid > 1 ? " AND tenant_id = ?" : ""));
            $params = $tid > 1 ? [$tid] : [];
            $stmt->execute($params);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                $this->providers[$row['provider_name']] = [
                    'name' => $row['provider_name'],
                    'api_key' => $row['api_key'] ?? '',
                    'api_secret' => $row['api_secret'] ?? '',
                    'account_sid' => $row['account_sid'] ?? '',
                    'auth_token' => $row['auth_token'] ?? '',
                    'from_number' => $row['from_number'] ?? '',
                    'webhook_url' => $row['webhook_url'] ?? '',
                    'is_active' => (bool)($row['is_active'] ?? true),
                    'settings' => json_decode((string)($row['settings'] ?? '{}'), true) ?: [],
                ];
            }
        } catch (\Throwable $e) {
            error_log("VoiceService: Failed to load providers: " . $e->getMessage());
        }
    }

    /**
     * Get available voice providers with status
     */
    public function getProvidersStatus(): array
    {
        $status = [];
        foreach ($this->providers as $key => $provider) {
            $status[$key] = [
                'name' => $provider['name'],
                'configured' => !empty($provider['api_key']) || !empty($provider['account_sid']),
                'from_number' => $provider['from_number'],
                'has_webhook' => !empty($provider['webhook_url']),
            ];
        }
        return $status;
    }

    /**
     * Make an outbound voice call
     *
     * @param array $params ['to', 'from', 'script_id', 'lead_id', 'campaign_id', 'webhook_url', 'record', 'timeout', 'statusCallback', 'voice', 'language']
     * @return array ['success', 'call_sid', 'error']
     */
    public function makeCall(array $params): array
    {
        $provider = $this->selectProvider();
        if (!$provider) {
            return ['success' => false, 'error' => 'No voice provider configured'];
        }

        $method = 'call' . ucfirst(strtolower($provider['name']));
        if (method_exists($this, $method)) {
            return $this->$method($params);
        }

        return ['success' => false, 'error' => 'Provider ' . $provider['name'] . ' not implemented'];
    }

    /**
     * Schedule a voice call
     *
     * @param int $leadId Lead ID
     * @param string $phone Phone number
     * @param int $agentId Agent ID
     * @param string $scheduledDate Date
     * @param string $scheduledTime Time
     * @param string $scriptTemplate Script template
     * @param string $priority Priority (low, medium, high)
     * @param string $leadName Lead name
     * @return array
     */
    public function scheduleCall(int $leadId, string $phone, int $agentId, string $scheduledDate, string $scheduledTime = '10:00:00', string $scriptTemplate = 'property_introduction', string $priority = 'medium', string $leadName = ''): array
    {
        // Check for existing pending schedule
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

    /**
     * Initiate an outbound call from a scheduled record
     */
    public function initiateCall(int $scheduleId): array
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
            error_log($e->getMessage());
            $script = null;
        }

        $tid = $this->tenantId();
        $sessionData = [
            'lead_id' => $schedule['lead_id'],
            'phone' => $schedule['phone'] ?: ($schedule['lead_phone'] ?? ''),
            'call_type' => 'outbound',
            'lead_name' => $schedule['lead_name'] ?? '',
            'caller_id' => $schedule['caller_id'] ?? '',
            'agent_id' => $schedule['agent_id'] ?? null,
            'call_flow' => $schedule['script_template'] ?? 'property_introduction',
            'agent_name' => $schedule['agent_name'] ?? '',
            'agent_phone' => $schedule['agent_phone'] ?? '',
            'lead_details' => [
                'lead_id' => $schedule['lead_id'],
                'name' => $schedule['lead_name'] ?? '',
                'phone' => $schedule['lead_phone'] ?? '',
                'property_interest' => $schedule['property_interest'] ?? '',
                'colony_id' => $schedule['colony_id'] ?? null,
            ],
        ];

        $callResult = $this->makeCall([
            'to' => $sessionData['phone'],
            'from' => $schedule['caller_id'] ?? '',
            'script_id' => $schedule['script_template'] ?? 1,
            'lead_id' => $schedule['lead_id'],
            'campaign_id' => null,
            'webhook_url' => BASE_URL . '/api/voice/webhook?session_id=' . uniqid(),
            'options' => [
                'record' => true,
                'timeout' => 60,
                'statusCallback' => BASE_URL . '/api/voice/webhook/status?session_id=' . uniqid(),
            ],
        ]);

        if ($callResult['success'] ?? false) {
            $this->db->execute(
                "UPDATE ai_calling_schedule SET status = 'ringing', call_sid = ?, provider_call_sid = ?, started_at = NOW() WHERE id = ?",
                [$callResult['call_sid'] ?? null, $callResult['call_sid'] ?? null, $scheduleId]
            );
            return [
                'success' => true,
                'call_sid' => $callResult['call_sid'] ?? null,
                'schedule_id' => $scheduleId,
                'call_result' => $callResult,
            ];
        } else {
            $this->db->execute(
                "UPDATE ai_calling_schedule SET status = 'failed', error_message = ?, updated_at = NOW() WHERE id = ?",
                [$sendErr ?? $callResult['error'] ?? 'Call failed', $scheduleId]
            );
            return [
                'success' => false,
                'error' => $sendErr ?? $callResult['error'] ?? 'Call failed'
            ];
        }
    }

/**
     * Select the best available provider
     */
    private function selectProvider(): ?array
    {
        if (!empty($this->currentProvider) && isset($this->providers[$this->currentProvider])) {
            return $this->providers[$this->currentProvider];
        }

        foreach ($this->providers as $key => $provider) {
            if (!empty($provider['api_key']) || !empty($provider['account_sid'])) {
                $this->currentProvider = $key;
                return $provider;
            }
        }

        return null;
    }

    /**
     * Call provider-specific method
     */
    private function callProvider(string $method, array $params): array
    {
        switch ($method) {
            case 'callExotel':
                return $this->callExotel($params);
            case 'callTwilio':
                return $this->callTwilio($params);
            case 'callPlivo':
                return $this->callPlivo($params);
            case 'callKnowlarity':
                return $this->callKnowlarity($params);
            default:
                return ['success' => false, 'error' => 'Unknown provider method: ' . $method];
        }
    }

    private function callExotel(array $params): array
    {
        if (empty($this->apiKey) || empty($this->fromNumber)) {
            return ['success' => false, 'error' => 'Exotel not configured'];
        }

        $url = "https://api.exotel.com/v1/Accounts/{$this->apiKey}/Calls/connect.json";
        $postData = [
            'From' => $params['from'] ?? $this->fromNumber,
            'To' => $params['to'],
            'CallerId' => $params['from'] ?? $this->fromNumber,
            'Url' => $params['webhook_url'] ?? '',
            'StatusCallback' => $params['statusCallback'] ?? null,
            'StatusCallbackEvents' => 'initiated,ringing,answered,completed,failed,busy,no-answer',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'call_sid' => $result['call_sid'] ?? null,
                'message_id' => $result['call_sid'] ?? null,
                'status' => 'sent',
                'provider' => 'exotel',
            ];
        }
        return ['success' => false, 'error' => "Exotel HTTP $httpCode: $response"];
    }

    private function callTwilio(array $params): array
    {
        try {
            $twilio = new \App\Services\Gateway\TwilioService();
            $result = $twilio->sendSms($params['to'], $params['body'] ?? '');
            if (!empty($result['success'])) {
                return ['success' => true, 'status' => 'sent', 'provider' => 'twilio', 'sid' => $result['sid'] ?? null];
            }
            return ['success' => false, 'status' => 'failed', 'error' => $result['error'] ?? 'Twilio send failed', 'provider' => 'twilio'];
        } catch (\Throwable $e) {
            return ['success' => false, 'status' => 'failed', 'error' => 'TwilioService unavailable: ' . $e->getMessage(), 'provider' => 'twilio'];
        }
    }

    private function callPlivo(array $params): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'Plivo not configured'];
        }
        $url = 'https://api.plivo.com/v1/Account/' . $this->apiKey . '/Call/';
        $payload = [
            'from' => $params['from'] ?? $this->fromNumber,
            'to' => $params['to'],
            'answer_url' => $params['webhook_url'] ?? '',
            'hangup_url' => $params['webhook_url'] ?? '',
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'auth-key: ' . $this->apiKey,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'call_sid' => $result['message_uuid'] ?? null,
                'message_id' => $result['message_uuid'] ?? null,
                'status' => 'sent',
                'provider' => 'plivo',
            ];
        }
        return ['success' => false, 'error' => "Plivo HTTP $httpCode: $response"];
    }

    private function callKnowlarity(array $params): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'Knowlarity not configured'];
        }
        $url = 'https://api.knowlarity.com/API/v1.0/call/click2call';
        $payload = [
            'api_key' => $this->apiKey,
            'from' => $params['from'] ?? $this->fromNumber,
            'to' => $params['to'],
            'no_of_calls' => 1,
            'url' => $params['webhook_url'] ?? '',
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'call_sid' => $result['call_id'] ?? null,
                'status' => 'sent',
                'provider' => 'knowlarity',
            ];
        }
        return ['success' => false, 'error' => "Knowlarity HTTP $httpCode: $response"];
    }

    private function callKaleyra(array $params): array
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'Kaleyra not configured'];
        }
        $url = 'https://api.kaleyra.com/v1/call';
        $payload = [
            'api_key' => $this->apiKey,
            'from' => $params['from'] ?? $this->fromNumber,
            'to' => $params['to'],
            'url' => $params['webhook_url'] ?? '',
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'api_key: ' . $this->apiKey,
                'Content-Type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 200 && $httpCode < 300) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'call_sid' => $result['call_id'] ?? null,
                'status' => 'sent',
                'provider' => 'kaleyra',
            ];
        }
        return ['success' => false, 'error' => "Kaleyra HTTP $httpCode: $response"];
    }

    // ========================================================================
    // QUEUE MANAGEMENT (merged from Communication\QueueProcessorService)
    // ========================================================================

    public function processQueue(int $limit = 100): array
    {
        $stats = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'completed' => 0];
        $tid = $this->tenantId();
        try {
            $sql = "SELECT e.*, c.status as campaign_status FROM drip_enrollments e
                JOIN drip_campaigns c ON c.id = e.campaign_id
                WHERE e.status = 'active' AND c.status = 'active' AND e.next_send_at <= NOW()" . ($tid > 1 ? " AND e.tenant_id = ? AND c.tenant_id = ?" : "") . "
                ORDER BY e.next_send_at ASC LIMIT ?";
            $stmt = $this->pdo->prepare($sql);
            $params = $tid > 1 ? [$tid, $tid, $limit] : [$limit];
            $stmt->execute($params);
            $enrollments = $stmt->fetchAll();
            foreach ($enrollments as $enr) {
                $stats['processed']++;
                $emails = $this->getDripEmails((int)$enr['campaign_id']);
                if (empty($emails)) continue;
                $nextStep = (int)$enr['current_step'];
                if ($nextStep >= count($emails)) {
                    $this->markDripCompleted((int)$enr['id']);
                    $stats['completed']++;
                    continue;
                }
                $email = $emails[$nextStep];
                $vars = ['name' => $enr['name'] ?? 'Customer', 'link' => BASE_URL . '/properties', 'phone' => '+91 92771 21112'];
                $renderedSubject = $this->renderDripTemplate($email['subject'], $vars);
                $renderedBody = $this->renderDripTemplate($email['body'], $vars);
                $channel = $email['channel'] ?? 'email';
                $sent = false;
                $sendErr = 'channel-not-supported';
                if ($channel === 'email') {
                    if (empty($_ENV['SMTP_USER'])) {
                        $sendErr = 'smtp-unconfigured';
                    } else {
                        try {
                            $gw = new CommunicationGateway();
                            $res = $gw->sendEmail($enr['email'], $renderedSubject, $renderedBody);
                            $sent = !empty($res['success']);
                            $sendErr = $sent ? null : substr((string)($res['error'] ?? 'send-failed'), 0, 400);
                        } catch (\Throwable $e) {
                            $sendErr = substr($e->getMessage(), 0, 400);
                        }
                    }
                }
                $logId = $this->logEnrollment((int)$enr['id'], (int)$enr['campaign_id'], $sent ? 'sent' : 'failed', $sent ? $renderedSubject : $sendErr, (int)$email['id']);
                if ($sent) {
                    $this->pdo->prepare("UPDATE drip_email_log SET status = 'sent', sent_at = NOW() WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$logId, $tid] : [$logId]);
                }
                $nextEmail = $emails[$nextStep + 1] ?? null;
                $nextSend = null;
                if ($nextEmail) {
                    $delay = ((int)$nextEmail['delay_days'] * 86400) + ((int)$nextEmail['delay_hours'] * 3600);
                    $nextSend = date('Y-m-d H:i:s', time() + $delay);
                }
                $this->pdo->prepare("UPDATE drip_enrollments SET current_step = current_step + 1, total_sent = total_sent + ?, last_sent_at = NOW(), next_send_at = ? WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))
                    ->execute($tid > 1 ? [$sent ? 1 : 0, $nextSend, $enr['id'], $tid] : [$sent ? 1 : 0, $nextSend, $enr['id']]);
                if ($sent) {
                    $this->pdo->prepare("UPDATE drip_campaigns SET emails_sent = emails_sent + 1 WHERE id = ?" . ($tid > 1 ? " AND tenant_id = ?" : ""))->execute($tid > 1 ? [$enr['campaign_id'], $tid] : [$enr['campaign_id']]);
                    $stats['sent']++;
                } else {
                    $stats['failed']++;
                }
            }
        } catch (\Throwable $e) {
            $stats['error'] = $e->getMessage();
        }
        return $stats;
    }

    public function processFromQueue(int $queueId): bool
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $sql = "SELECT * FROM sms_queue WHERE id = ? AND status = 'pending'" . $tenantFilter;
        $params = [$queueId];
        if ($tid > 1) $params[] = $tid;
        $sms = $this->db->fetchOne($sql, $params);
        if (!$sms) return false;

        $updParams = [$queueId];
        if ($tid > 1) $updParams[] = $tid;
        $this->db->query("UPDATE sms_queue SET attempts = attempts + 1 WHERE id = ?" . $tenantFilter, $updParams);

        try {
            $result = $this->send($sms['recipient'], $sms['message']);
            $status = ($result['success'] ?? false) ? 'sent' : 'failed';
            $updParams2 = [$status, $result['error'] ?? null, $queueId];
            if ($tid > 1) $updParams2[] = $tid;
            $this->db->query(
                "UPDATE sms_queue SET status = ?, sent_at = NOW() WHERE id = ?" . $tenantFilter,
                [$updParams2[0], $updParams2[2]]
            );
            return $result['success'] ?? false;
        } catch (\Throwable $e) {
            $updParams3 = [$e->getMessage(), $queueId];
            if ($tid > 1) $updParams3[] = $tid;
            $this->db->query(
                "UPDATE sms_queue SET status = 'failed' WHERE id = ?" . $tenantFilter,
                [$updParams3[1]]
            );
            return false;
        }
    }

    // ========================================================================
    // SMS METHODS (merged from SmsService)
    // ========================================================================

    public function send(string $phone, string $message, $useQueue = false): array
    {
        $phone = $this->cleanPhone($phone);

        try {
            if ($useQueue) {
                return $this->queue($phone, $message);
            }

            $result = match ($this->provider) {
                self::PROVIDER_TWILIO => $this->sendViaTwilio($phone, $message),
                self::PROVIDER_MSG91 => $this->sendViaMsg91($phone, $message),
                default => $this->sendViaLog($phone, $message),
            };

            $this->logToQueue($phone, $message, $result['status'] ?? 'sent', $result['error'] ?? null);
            return $result;
        } catch (\Throwable $e) {
            error_log("SmsService: Send failed: " . $e->getMessage());
            $this->logToQueue($phone, $message, 'failed', $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendOTP($mobile, $otp = null, $templateId = null): array
    {
        try {
            if (!$otp) {
                $otp = $this->generateOTP();
            }

            $mobile = $this->cleanPhone($mobile);
            $this->saveOTP($mobile, $otp);

            $effectiveTemplateId = $templateId ?? $this->templateId;

            if (empty($effectiveTemplateId)) {
                error_log("SMS OTP skipped for {$mobile}: MSG91_TEMPLATE_ID not configured (DLT template required). OTP generated locally: valid in DB.");
                return [
                    'success' => false,
                    'otp' => $otp,
                    'error' => 'SMS template not configured',
                    'skipped' => true
                ];
            }

            $payload = [
                'template_id' => $effectiveTemplateId,
                'short_url' => '0',
                'realTimeResponse' => 'true',
                'recipients' => [
                    [
                        'mobiles' => $mobile,
                        'OTP' => $otp
                    ]
                ]
            ];

            $result = $this->callAPI($this->apiUrl, $payload);

            $this->logSMS($mobile, 'OTP', "Your OTP is: {$otp}", $result['type'] ?? 'unknown');

            return [
                'success' => true,
                'otp' => $otp,
                'message_id' => $result['message_id'] ?? null
            ];

        } catch (\Throwable $e) {
            error_log("SMS OTP send failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyOTP($mobile, $otp): array
    {
        try {
            $mobile = $this->cleanPhone($mobile);

            $record = $this->db->fetchOne(
                "SELECT * FROM otp_verifications
                 WHERE identifier = ? AND otp_code = ? AND used_at IS NULL 
                 AND expires_at > NOW()
                 ORDER BY created_at DESC LIMIT 1",
                [$mobile, $otp]
            );

            if (!$record) {
                return ['success' => false, 'error' => 'OTP expired, invalid, or already used'];
            }

            $this->db->query(
                "UPDATE otp_verifications SET used_at = NOW() WHERE id = ?",
                [$record['id']]
            );

            return ['success' => true];
        } catch (\Throwable $e) {
            error_log("OTP verification failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendWelcomeSMS($mobile, $name, $userType = 'customer'): array
    {
        $message = $userType === 'associate'
            ? "Hi {$name}, Welcome to APS Dream Home Associate Program! Your referral journey starts now. Login: " . BASE_URL . "/associate/login"
            : "Hi {$name}, Welcome to APS Dream Home! Your dream property awaits. Start browsing: " . BASE_URL . "/properties";

        return $this->send($mobile, $message, 'WELCOME');
    }

    public function sendPaymentConfirmationSMS($mobile, $amount, $bookingId): array
    {
        $message = "Payment Successful! Amount: ₹{$amount} received for Booking #{$bookingId}. Thank you for choosing APS Dream Home.";
        return $this->send($mobile, $message, 'PAYMENT');
    }

    public function sendCommissionSMS($mobile, $amount, $walletBalance): array
    {
        $message = "Commission Credited! ₹{$amount} added to your wallet. Current Balance: ₹{$walletBalance}. Keep referring!";
        return $this->send($mobile, $message, 'COMMISSION');
    }

    public function sendPropertyApprovalSMS($mobile, $propertyTitle): array
    {
        $message = "Your property listing '{$propertyTitle}' has been approved and is now live on APS Dream Home!";
        return $this->send($mobile, $message, 'PROPERTY');
    }

    public function sendVisitReminderSMS($mobile, $propertyTitle, $visitDate, $visitTime): array
    {
        $formattedDate = date('d M Y', strtotime($visitDate));
        $message = "Reminder: Site visit scheduled for {$propertyTitle} on {$formattedDate} at {$visitTime}. Our executive will meet you there.";
        return $this->send($mobile, $message, 'VISIT_REMINDER');
    }

    public function sendPayoutSMS($mobile, $amount, $transactionId): array
    {
        $message = "Payout Processed! ₹{$amount} has been transferred to your bank account. Transaction ID: {$transactionId}";
        return $this->send($mobile, $message, 'PAYOUT');
    }

    public function sendLoginAlertSMS($mobile, $name, $ip, $device, $time): array
    {
        $message = "APS Dream Home: New login to your account at {$time} from {$device}. ";
        $message .= "If this wasn't you, change your password immediately. ";
        $message .= "Contact support: " . BASE_URL . "/support";
        return $this->send($mobile, $message, 'LOGIN_ALERT');
    }

    public function sendLowStockAlert($mobile, $productName, $quantity, $reorderLevel): array
    {
        $message = "Low Stock Alert: {$productName} is low on stock. Current: {$quantity}, Reorder level: {$reorderLevel}. Please reorder.";
        return $this->send($mobile, $message, 'LOW_STOCK');
    }

    public function sendOverdueReminderSMS($mobile, $plotNumber, $colonyName, $amount): array
    {
        $message = "Payment Reminder: Maintenance due for plot {$plotNumber} in {$colonyName} is overdue. Outstanding: ₹" . number_format($amount, 2) . ". Please pay to avoid penalties.";
        return $this->send($mobile, $message, 'OVERDUE');
    }

    // ========================================================================
    // HELPER METHODS
    // ========================================================================

    private function callAPI($url, $payload): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'authkey: ' . $this->apiKey,
            'accept: application/json',
            'content-type: application/json',
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }

        $result = json_decode($response, true);

        if (isset($result['type']) && $result['type'] === 'error') {
            throw new \Exception("MSG91 Error: " . ($result['message'] ?? 'Unknown error'));
        }

        return $result;
    }

    private function generateOTP($length = 6): string
    {
        return str_pad(mt_rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    private function cleanPhone($phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) === 10) $phone = '91' . $phone;
        return $phone;
    }

    private function saveOTP($mobile, $otp): void
    {
        try {
            $this->db->query(
                "UPDATE otp_verifications SET expires_at = NOW() WHERE identifier = ? AND used_at IS NULL AND expires_at > NOW()",
                [$mobile]
            );
        } catch (\Throwable $e) {
            error_log("SmsService saveOTP invalidate failed: " . $e->getMessage());
        }

        try {
            $tid = method_exists($this, 'tenantId') ? $this->tenantId() : 1;
            $this->db->insert('otp_verifications', [
                'tenant_id' => $tid,
                'identifier' => $mobile,
                'otp_code' => $otp,
                'type' => 'sms',
                'purpose' => 'account_verification',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            error_log("SmsService saveOTP insert failed: " . $e->getMessage());
        }
    }

    private function logSMS($mobile, $type, $message, $status): void
    {
        try {
            $tid = method_exists($this, 'tenantId') ? $this->tenantId() : 1;
            $this->db->insert('notifications_unified', [
                'tenant_id' => $tid,
                'recipient' => $mobile,
                'notification_type' => 'sms',
                'subject' => $type,
                'message' => substr($message, 0, 500),
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            error_log("SMS logging failed: " . $e->getMessage());
        }
    }

    public function getStats(int $days = 30): array
    {
        return [
            'total_sent' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'sent' AND notification_type = 'sms' AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0,
            'total_failed' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM notifications_unified WHERE status = 'failed' AND notification_type = 'sms' AND created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0,
            'otp_sent' => $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM otp_verifications WHERE created_at > DATE_SUB(NOW(), INTERVAL ? DAY)",
                [$days]
            )['count'] ?? 0
        ];
    }

    public function getQueueStats(): array
    {
        $stats = ['pending' => 0, 'sent' => 0, 'failed' => 0];
        try {
            $tid = $this->tenantId();
            $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
            $rows = $this->db->fetchAll("SELECT status, COUNT(*) as cnt FROM sms_queue WHERE 1=1" . $tenantFilter . " GROUP BY status", $tid > 1 ? [$tid] : []);
            foreach ($rows as $r) $stats[$r['status']] = (int)$r['cnt'];
        } catch (\Throwable $e) {
            error_log("SmsService.php: " . $e->getMessage());
        }
        return $stats;
    }

    public function getQueueItems($status = null, $limit = 50, $offset = 0): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $where = $status ? "WHERE status = ?" : "";
        $params = $status ? [$status] : [];
        if ($tid > 1) $params[] = $tid;
        try {
            return $this->db->fetchAll(
                "SELECT * FROM sms_queue $where" . $tenantFilter . " ORDER BY created_at DESC LIMIT ? OFFSET ?",
                array_merge($params, [$limit, $offset])
            );
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function tenantId(): int
    {
        try {
            return TenantContext::getId();
        } catch (\Throwable) {
            return 1;
        }
    }

    // ========================================================================
    // VOICE CALL METHODS (migrated from VoiceCallService)
    // ========================================================================

    public function getScheduleAnalytics(): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        try {
            $total = (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE 1=1{$tenantFilter}", $params)['c'] ?? 0;
            $pending = (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'pending'{$tenantFilter}", $params)['c'] ?? 0;
            $completed = (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'completed'{$tenantFilter}", $params)['c'] ?? 0;
            $failed = (int)$this->db->fetchOne("SELECT COUNT(*) as c FROM ai_calling_schedule WHERE status = 'failed'{$tenantFilter}", $params)['c'] ?? 0;

            return [
                'total' => $total,
                'pending' => $pending,
                'completed' => $completed,
                'failed' => $failed,
            ];
        } catch (\Throwable $e) {
            error_log("VoiceService::getScheduleAnalytics failed: " . $e->getMessage());
            return ['total' => 0, 'pending' => 0, 'completed' => 0, 'failed' => 0];
        }
    }

    public function getAgentList(): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        try {
            return $this->db->fetchAll("SELECT * FROM ai_calling_agents WHERE is_active = 1{$tenantFilter} ORDER BY agent_name", $params);
        } catch (\Throwable $e) {
            error_log("VoiceService::getAgentList failed: " . $e->getMessage());
            return [];
        }
    }

    public function getCallsOverTime(int $days = 14): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        try {
            return $this->db->fetchAll(
                "SELECT DATE(scheduled_date) as date, COUNT(*) as count FROM ai_calling_schedule WHERE 1=1{$tenantFilter} AND scheduled_date >= DATE_SUB(NOW(), INTERVAL ? DAY) GROUP BY DATE(scheduled_date) ORDER BY DATE(scheduled_date)",
                array_merge($params, [$days])
            );
        } catch (\Throwable $e) {
            error_log("VoiceService::getCallsOverTime failed: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingCalls(int $limit = 10): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        try {
            return $this->db->fetchAll(
                "SELECT s.*, l.name as lead_name, l.phone as lead_phone FROM ai_calling_schedule s LEFT JOIN leads l ON l.id = s.lead_id WHERE status = 'pending'{$tenantFilter} ORDER BY scheduled_date ASC, scheduled_time ASC LIMIT ?",
                array_merge($params, [$limit])
            );
        } catch (\Throwable $e) {
            error_log("VoiceService::getPendingCalls failed: " . $e->getMessage());
            return [];
        }
    }

    public function getScriptList(): array
    {
        try {
            return $this->db->fetchAll("SELECT * FROM ai_call_scripts WHERE is_active = 1 ORDER BY script_name");
        } catch (\Throwable $e) {
            error_log("VoiceService::getScriptList failed: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableLeadsForScheduling(): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        try {
            return $this->db->fetchAll(
                "SELECT l.id, l.name, l.phone, l.colony_id, l.property_interest, l.status FROM leads l WHERE l.status = 'active'{$tenantFilter} ORDER BY l.name LIMIT 100",
                $params
            );
        } catch (\Throwable $e) {
            error_log("VoiceService::getAvailableLeadsForScheduling failed: " . $e->getMessage());
            return [];
        }
    }

    public function getLeadCallHistory(int $leadId): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$leadId];
        if ($tid > 1) $params[] = $tid;

        try {
            return $this->db->fetchAll(
                "SELECT s.*, a.agent_name FROM ai_calling_schedule s LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id WHERE s.lead_id = ?{$tenantFilter} ORDER BY s.scheduled_date DESC",
                $params
            );
        } catch (\Throwable $e) {
            error_log("VoiceService::getLeadCallHistory failed: " . $e->getMessage());
            return [];
        }
    }

    public function processScheduledCalls(int $limit = 100): array
    {
        $stats = ['processed' => 0, 'sent' => 0, 'failed' => 0, 'completed' => 0];
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        try {
            $schedule = $this->db->fetch(
                "SELECT s.*, l.name as lead_name, l.phone as lead_phone, l.property_interest,
                        a.agent_name, a.agent_id as agent_identifier
                 FROM ai_calling_schedule s
                 LEFT JOIN leads l ON l.id = s.lead_id
                 LEFT JOIN ai_calling_agents a ON a.agent_id = s.ai_agent_id
                 WHERE s.status = 'pending'{$tenantFilter} ORDER BY s.scheduled_date ASC, s.scheduled_time ASC LIMIT ?",
                array_merge($params, [$limit])
            );

            if (!$schedule) return $stats;

            // Process the first pending call
            $result = $this->initiateCall((int)$schedule['id']);

            if ($result['success']) {
                $stats['processed']++;
                $stats['sent']++;
            } else {
                $stats['processed']++;
                $stats['failed']++;
            }

            return $stats;
        } catch (\Throwable $e) {
            error_log("VoiceService::processScheduledCalls failed: " . $e->getMessage());
            return $stats;
        }
    }

    public function getCallsByAgent(): array
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];

        try {
            return $this->db->fetchAll(
                "SELECT a.agent_name, COUNT(s.id) as call_count FROM ai_calling_agents a LEFT JOIN ai_calling_schedule s ON a.agent_id = s.ai_agent_id WHERE a.is_active = 1{$tenantFilter} GROUP BY a.id, a.agent_name ORDER BY call_count DESC",
                $params
            );
        } catch (\Throwable $e) {
            error_log("VoiceService::getCallsByAgent failed: " . $e->getMessage());
            return [];
        }
    }

    public function rescheduleCall(int $scheduleId, string $newDate, string $newTime): bool
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$newDate, $newTime, $scheduleId];
        if ($tid > 1) $params[] = $tid;

        try {
            $this->db->execute(
                "UPDATE ai_calling_schedule SET scheduled_date = ?, scheduled_time = ?, updated_at = NOW() WHERE id = ?{$tenantFilter}",
                $params
            );
            return true;
        } catch (\Throwable $e) {
            error_log("VoiceService::rescheduleCall failed: " . $e->getMessage());
            return false;
        }
    }

    public function cancelSchedule(int $scheduleId): bool
    {
        $tid = $this->tenantId();
        $tenantFilter = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [$scheduleId];
        if ($tid > 1) $params[] = $tid;

        try {
            $this->db->execute(
                "UPDATE ai_calling_schedule SET status = 'cancelled', updated_at = NOW() WHERE id = ?{$tenantFilter}",
                $params
            );
            return true;
        } catch (\Throwable $e) {
            error_log("VoiceService::cancelSchedule failed: " . $e->getMessage());
            return false;
        }
    }
}