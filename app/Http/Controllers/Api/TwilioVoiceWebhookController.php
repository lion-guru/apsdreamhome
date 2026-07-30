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
     */
    protected function buildCallbackUrl()
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri    = $_SERVER['REQUEST_URI'] ?? '/api/twilio/voice';
        return $scheme . '://' . $host . $uri;
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
                            recording_sid = ?,
                            duration_seconds = COALESCE(?, duration_seconds),
                            updated_at = NOW()
                      WHERE call_sid = ?"
                );
                $stmt->execute([$recordingUrl, $recordingSid, $duration !== null ? (int)$duration : null, $callSid]);
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

        // Persist the DTMF result against the session.
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = method_exists($db, 'getPdo') ? $db->getPdo() : null;
            if ($pdo && $callSid) {
                $stmt = $pdo->prepare(
                    "UPDATE ai_call_sessions
                        SET digits_pressed = ?,
                            speech_result  = ?,
                            updated_at = NOW()
                      WHERE call_sid = ?"
                );
                $stmt->execute([$digits ?: null, $speech ?: null, $callSid]);
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
            $builder->say("Connecting you to an agent now. Please hold.", 'alice', 'en')
                    ->dial($_ENV['AGENT_FORWARD_NUMBER'] ?? '+919876543210', ['timeout' => 30]);
        } else {
            $builder->say("Sorry, we did not understand your selection. Goodbye.", 'alice', 'en')
                    ->hangup();
        }

        $this->respondTwiML($builder->toXml());
    }
}
