<?php

namespace App\Http\Controllers\Api;

use App\Core\Controller;
use App\Services\Voice\TwilioVoiceService;
use App\Services\Voice\TwiMLBuilder;

/**
 * APS Dream Home - Twilio Voice Webhook Controller
 *
 * Receives inbound POSTs from Twilio:
 *   - /api/twilio/voice          : initial call answer
 *   - /api/twilio/voice/status   : call status changes (queued/completed/...)
 *   - /api/twilio/voice/recording: recording ready
 *   - /api/twilio/voice/gather   : DTMF/speech result from <Gather>
 *
 * All responses are TwiML XML. All inbound requests are verified via
 * the X-Twilio-Signature HMAC.
 *
 * Skip CSRF — Twilio can't carry our CSRF token.
 */
class TwilioVoiceWebhookController extends Controller
{
    /** @var TwilioVoiceService */
    protected $voice;

    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->voice = new TwilioVoiceService();
    }

    /**
     * Build the full URL Twilio used to call us (for HMAC verification).
     * Behind ngrok/Apache we may see X-Forwarded-Proto/Host instead of HTTPS.
     */
    protected function buildCallbackUrl()
    {
        $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;
        if (!$proto) {
            $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        }
        $proto = strtolower((string) $proto);
        if (!in_array($proto, ['http', 'https'], true)) {
            $proto = 'http';
        }

        $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri  = $_SERVER['REQUEST_URI'] ?? '/api/twilio/voice';
        return $proto . '://' . $host . $uri;
    }

    /**
     * Verify the incoming Twilio request. In test mode (TWILIO_TEST_MODE=true),
     * we skip verification so test scripts can drive the webhooks.
     */
    protected function verifyTwilioOrTest()
    {
        $testMode = ($_ENV['TWILIO_TEST_MODE'] ?? '') === 'true' || getenv('TWILIO_TEST_MODE') === 'true';
        if ($testMode) return true;
        $sig = $_SERVER['HTTP_X_TWILIO_SIGNATURE'] ?? '';
        $params = $_POST ?: [];
        return $this->voice->verifyWebhookSignature($this->buildCallbackUrl(), $params, $sig);
    }

    /**
     * Return a TwiML response.
     */
    protected function respondTwiML($xml)
    {
        header('Content-Type: text/xml; charset=utf-8');
        echo $xml;
        exit;
    }

    /**
     * Resolve the lead name + tenant for a scheduled AI call so the dynamic
     * script can personalise its greeting (and read ai_call_scripts in the
     * correct tenant scope). Best-effort: null when unavailable.
     */
    protected function resolveScheduleLead(int $scheduleId): ?array
    {
        if ($scheduleId <= 0) {
            return null;
        }
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $pdo->prepare(
                "SELECT l.name AS name, s.tenant_id AS tenant_id
                   FROM ai_calling_schedule s
                   LEFT JOIN leads l ON l.id = s.lead_id
                  WHERE s.id = ?"
            );
            $stmt->execute([$scheduleId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('TwilioVoiceWebhookController::resolveScheduleLead: ' . $e->getMessage());
            return null;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/twilio/voice — initial inbound / outbound answer        */
    /* ------------------------------------------------------------------ */
    public function voice()
    {
        $params = $_POST;
        $callSid = $params['CallSid'] ?? '';
        $from    = $params['From']    ?? '';
        $to      = $params['To']      ?? '';
        $direction = $params['Direction'] ?? 'outbound-api';

        if (!$this->verifyTwilioOrTest()) {
            http_response_code(403);
            echo 'Forbidden: invalid Twilio signature';
            return;
        }

        // For inbound calls, play a greeting and gather DTMF.
        // For outbound AI calls, generate the appropriate TwiML for the
        // agent type (site_visit / property_inquiry / follow_up).
        $type = $params['CustomParam'] ?? $_GET['type'] ?? 'greeting';
        $gatherUrl = '/api/twilio/voice/gather';

        // Dynamic script path: when the AI dialer passes ?script=<code>,
        // build TwiML from ai_call_scripts rather than the static menu.
        // Falls back to the static menu if the script cannot be resolved.
        $scriptCode = trim((string)($_GET['script'] ?? ''));
        if ($scriptCode !== '') {
            $scheduleId = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;
            $lead = $this->resolveScheduleLead($scheduleId);
            $xml = $this->voice->generateTwiMLFromScript($scriptCode, [
                'gatherUrl' => $gatherUrl,
                'lead_name' => $lead['name'] ?? '',
                'tenant_id' => isset($lead['tenant_id']) ? (int)$lead['tenant_id'] : 1,
            ]);
            $this->respondTwiML($xml);
            return;
        }

        switch ($type) {
            case 'site_visit':
                $xml = $this->voice->generateSiteVisitTwiML(['gatherUrl' => '/api/twilio/voice/gather']);
                break;
            case 'property_inquiry':
                $xml = $this->voice->generatePropertyInquiryTwiML(['gatherUrl' => '/api/twilio/voice/gather']);
                break;
            case 'follow_up':
                $xml = $this->voice->generateFollowUpTwiML(['gatherUrl' => '/api/twilio/voice/gather']);
                break;
            case 'greeting':
            default:
                $xml = $this->voice->generateGreetingTwiML();
                break;
        }

        $this->respondTwiML($xml);
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/twilio/voice/status — call status callback              */
    /* ------------------------------------------------------------------ */
    public function status()
    {
        $params = $_POST;
        $callSid = $params['CallSid'] ?? '';
        $status  = $params['CallStatus'] ?? 'unknown';
        $duration = isset($params['CallDuration']) ? (int)$params['CallDuration'] : null;

        if (!$this->verifyTwilioOrTest()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $this->voice->updateSessionStatus($callSid, $status, ['duration' => $duration]);

        // Close the outbound schedule row so the CRM dialer reports the real
        // outcome once Twilio finishes the call (previously left 'processing').
        $terminal = [
            'completed' => 'completed',
            'busy'      => 'failed',
            'failed'    => 'failed',
            'no-answer' => 'failed',
            'canceled'  => 'cancelled',
        ];
        $target = $terminal[strtolower($status)] ?? null;
        if ($target !== null && $callSid !== '') {
            try {
                $db = \App\Core\Database\Database::getInstance();
                $pdo = method_exists($db, 'getPdo') ? $db->getPdo() : null;
                if ($pdo) {
                    $stmt = $pdo->prepare(
                        "UPDATE ai_calling_schedule s
                           JOIN ai_call_sessions ss ON ss.id = s.call_session_id
                              SET s.status = ?,
                                  s.result_notes = CONCAT('call_status=', ?, ' duration=', ?),
                                  s.updated_at = NOW()
                            WHERE ss.call_sid = ?"
                    );
                    $stmt->execute([$target, $status, $duration !== null ? (string)$duration : '', $callSid]);
                }
            } catch (\Throwable $e) {
                error_log('TwilioVoiceWebhookController::status schedule close: ' . $e->getMessage());
            }
        }

        // Twilio expects 200 OK + TwiML (or 204) for status callbacks.
        header('Content-Type: text/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?><Response/>';
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/twilio/voice/recording — recording ready                */
    /* ------------------------------------------------------------------ */
    public function recording()
    {
        $params = $_POST;
        $callSid       = $params['CallSid']       ?? '';
        $recordingSid  = $params['RecordingSid']  ?? '';
        $recordingUrl  = $params['RecordingUrl']  ?? '';
        $duration      = $params['RecordingDuration'] ?? null;

        if (!$this->verifyTwilioOrTest()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!$this->voice || !$callSid) {
            header('Content-Type: text/xml; charset=utf-8');
            echo '<?xml version="1.0" encoding="UTF-8"?><Response/>';
            exit;
        }

        // Try to persist the recording URL to ai_call_sessions for playback.
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = method_exists($db, 'getPdo') ? $db->getPdo() : null;
            if ($pdo) {
                $stmt = $pdo->prepare(
                    "UPDATE ai_call_sessions
                        SET recording_url = ?,
                            recording_duration = COALESCE(?, recording_duration),
                            updated_at = NOW()
                      WHERE call_sid = ?"
                );
                $stmt->execute([$recordingUrl, $duration !== null ? (int)$duration : null, $callSid]);
            }
        } catch (\Throwable $e) {
        // Best-effort; never fail the webhook.
        error_log($e->getMessage());
        }

        header('Content-Type: text/xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8"?><Response/>';
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/twilio/voice/gather — DTMF/speech input                  */
    /* ------------------------------------------------------------------ */
    public function gather()
    {
        $params = $_POST;
        $callSid   = $params['CallSid']   ?? '';
        $digits    = $params['Digits']    ?? '';
        $speech    = $params['SpeechResult'] ?? '';
        $type      = $params['CustomParam'] ?? $_GET['type'] ?? 'site_visit';

        if (!$this->verifyTwilioOrTest()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        // Map the DTMF digit to a semantic outcome so the CRM learns what the
        // customer chose — not just the numeral.
        $outcome = null;
        $interest = 'none';
        $followUp = 0;
        $followUpDate = null;
        if ($digits === '1') {
            $outcome = 'interested';
            $interest = 'warm';
            $followUp = 1;
            $followUpDate = (new \DateTimeImmutable('+1 day'))->format('Y-m-d');
        } elseif ($digits === '2') {
            $outcome = 'callback';
            $interest = 'cold';
            $followUp = 1;
            $followUpDate = (new \DateTimeImmutable('+2 days'))->format('Y-m-d');
        } elseif ($digits === '3') {
            $outcome = 'transfer';
            $interest = 'hot';
            $followUp = 0;
        } elseif ($digits !== '') {
            $outcome = 'no_response';
            $interest = 'none';
        }

        // Persist the DTMF result against the session AND close the schedule
        // row so the dialer does not re-call someone who already answered.
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = method_exists($db, 'getPdo') ? $db->getPdo() : null;
            if ($pdo && $callSid) {
                $sets = [
                    "call_transcript = CONCAT(COALESCE(call_transcript, ''), IFNULL(?, ''), IFNULL(CONCAT(' ', ?), ''))",
                    "transcript = CONCAT(COALESCE(transcript, ''), IFNULL(?, ''), IFNULL(CONCAT(' ', ?), ''))",
                    "updated_at = NOW()",
                ];
                $params = [$digits ?: null, $speech ?: null, $digits ?: null, $speech ?: null];

                if ($outcome !== null) {
                    $sets[] = 'customer_response = ?';
                    $sets[] = 'interest_level = ?';
                    $sets[] = 'outcome = ?';
                    $sets[] = 'follow_up_required = ?';
                    $params[] = $outcome;
                    $params[] = $interest;
                    $params[] = $outcome;
                    $params[] = $followUp;
                    if ($followUpDate !== null) {
                        $sets[] = 'follow_up_date = ?';
                        $params[] = $followUpDate;
                    }
                }

                $stmt = $pdo->prepare(
                    'UPDATE ai_call_sessions SET ' . implode(', ', $sets) . ' WHERE call_sid = ?'
                );
                $params[] = $callSid;
                $stmt->execute($params);

                if ($outcome !== null) {
                    $stmt = $pdo->prepare(
                        "UPDATE ai_calling_schedule s
                           JOIN ai_call_sessions ss ON ss.id = s.call_session_id
                              SET s.status = 'completed',
                                  s.result_notes = CONCAT('digit=', ?, ' outcome=', ?),
                                  s.updated_at = NOW()
                            WHERE ss.call_sid = ?"
                    );
                    $stmt->execute([$digits, $outcome, $callSid]);
                }
            }
        } catch (\Throwable $e) {
        // ignore
        error_log($e->getMessage());
        }
        $builder = new TwiMLBuilder();

        if ($digits === '1') {
            $builder->say("Thank you. Your response has been recorded. An agent will follow up shortly.", 'alice', 'en')
                    ->hangup();
        } elseif ($digits === '2') {
            $builder->say("Got it. We will reschedule and call you back. Goodbye.", 'alice', 'en')
                    ->hangup();
        } elseif ($digits === '3') {
            $forward = trim((string)($_ENV['AGENT_FORWARD_NUMBER'] ?? getenv('AGENT_FORWARD_NUMBER') ?: ''));
            if ($forward !== '' && preg_match('/^\+[1-9][0-9]{8,14}$/', $forward)) {
                $builder->say("Connecting you to an agent now. Please hold.", 'alice', 'en')
                        ->dial($forward, ['timeout' => 30]);
            } else {
                $builder->say("Thank you. One of our agents will call you back shortly. Goodbye.", 'alice', 'en')
                        ->hangup();
            }
        } else {
            $builder->say("Sorry, we did not understand your selection. Goodbye.", 'alice', 'en')
                    ->hangup();
        }

        $this->respondTwiML($builder->toXml());
    }
}
