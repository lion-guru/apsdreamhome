<?php

namespace App\Services\Voice;

use App\Services\AI\VoiceService as UnifiedVoiceService;

/**
 * TwilioVoiceService - Legacy wrapper for backward compatibility
 *
 * @deprecated Use App\Services\AI\VoiceService instead
 * Twilio voice integration merged into unified VoiceService.
 */
class TwilioVoiceService extends UnifiedVoiceService
{
    /**
     * @deprecated Use parent constructor instead
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Verify an incoming Twilio webhook signature (X-Twilio-Signature).
     *
     * Twilio computes HMAC-SHA1 over the full request URL plus each POSTed
     * parameter (name + value, sorted by name, no separators), then base64s
     * the raw digest. We recompute and constant-time compare.
     *
     * When the configured twilio.test_mode is on, verification is skipped so
     * test scripts can drive the webhooks without valid signatures.
     */
    public function verifyWebhookSignature(string $url, array $params, string $signature): bool
    {
        try {
            $cfg = \App\Services\ServiceConfigService::getInstance()->getApiConfig('twilio');
        } catch (\Throwable $e) {
            error_log('TwilioVoiceService::verifyWebhookSignature - config load failed: ' . $e->getMessage());
            return false; // fail closed: cannot verify -> reject
        }

        $testMode = in_array(strtolower(trim((string)($cfg['test_mode'] ?? ''))), ['1', 'true', 'yes'], true);
        if ($testMode) {
            return true;
        }

        $authToken = (string)($cfg['auth_token'] ?? '');
        if ($authToken === '' || $signature === '') {
            return false; // no token or no signature -> reject
        }

        ksort($params);
        $data = $url;
        foreach ($params as $name => $value) {
            $data .= $name . $value;
        }

        $expected = base64_encode(hash_hmac('sha1', $data, $authToken, true));
        return hash_equals($expected, $signature);
    }

    /**
     * Build an IVR menu TwiML (say + gather) with a post-timeout fallback.
     */
    protected function buildGatherMenu(string $message, string $fallback, string $action): string
    {
        $e = fn($s) => htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . '<Response>'
            . '<Gather numDigits="1" action="' . $e($action) . '" method="POST" timeout="10">'
            . '<Say voice="alice" language="en">' . $e($message) . '</Say>'
            . '</Gather>'
            . '<Say voice="alice" language="en">' . $e($fallback) . '</Say>'
            . '</Response>';
    }

    /**
     * Greeting menu for inbound/general calls.
     */
    public function generateGreetingTwiML(): string
    {
        return $this->buildGatherMenu(
            'Thank you for calling APS Dream Home. Press 1 to schedule a site visit. Press 2 to speak with an agent. Press 3 to hear our EMI plans.',
            'We did not receive any input. Thank you for calling APS Dream Home. Goodbye.',
            '/api/twilio/voice/gather'
        );
    }

    /**
     * Greeting menu for site-visit booking calls.
     */
    public function generateSiteVisitTwiML(array $options = []): string
    {
        $action = $options['gatherUrl'] ?? '/api/twilio/voice/gather';
        return $this->buildGatherMenu(
            'Welcome to APS Dream Home. You are booking a site visit. Press 1 to confirm your preferred time. Press 2 to reschedule. Press 3 to talk to an agent.',
            'We did not receive any input. Your site visit preferences were not saved. Goodbye.',
            $action
        );
    }

    /**
     * Greeting menu for property-inquiry calls.
     */
    public function generatePropertyInquiryTwiML(array $options = []): string
    {
        $action = $options['gatherUrl'] ?? '/api/twilio/voice/gather';
        return $this->buildGatherMenu(
            'Welcome to APS Dream Home. You are calling about a property enquiry. Press 1 for a site visit. Press 2 for price and EMI details. Press 3 to speak with an agent.',
            'We did not receive any input. A team member will call you back shortly. Goodbye.',
            $action
        );
    }

    /**
     * Greeting menu for follow-up calls.
     */
    public function generateFollowUpTwiML(array $options = []): string
    {
        $action = $options['gatherUrl'] ?? '/api/twilio/voice/gather';
        return $this->buildGatherMenu(
            'Hi, this is APS Dream Home calling to follow up on your enquiry. Press 1 to confirm your interest. Press 2 to request a callback. Press 3 to speak with an agent.',
            'We did not receive any input. We will try again later. Goodbye.',
            $action
        );
    }

    /**
     * Map a Twilio call status onto our ai_call_sessions.status enum and persist.
     * Twilio statuses: queued initiated ringing in-progress completed busy failed no-answer canceled
     */
    public function updateSessionStatus(string $callSid, string $status, array $extra = []): void
    {
        if ($callSid === '') {
            return;
        }
        $map = [
            'queued' => 'scheduled',
            'initiated' => 'in_progress',
            'ringing' => 'in_progress',
            'in-progress' => 'in_progress',
            'completed' => 'completed',
            'busy' => 'no_answer',
            'failed' => 'failed',
            'no-answer' => 'no_answer',
            'canceled' => 'failed',
        ];
        $mapped = $map[strtolower($status)] ?? 'in_progress';

        $duration = isset($extra['duration']) ? (int)$extra['duration'] : null;

        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = method_exists($db, 'getPdo') ? $db->getPdo() : null;
            if (!$pdo) {
                return;
            }
            $stmt = $pdo->prepare(
                "UPDATE ai_call_sessions
                    SET status = ?,
                        duration_seconds = COALESCE(?, duration_seconds),
                        started_at = COALESCE(started_at, IF(status = 'in_progress', NOW(), started_at)),
                        ended_at = CASE WHEN ? IN ('completed','failed','no_answer') THEN NOW() ELSE ended_at END,
                        updated_at = NOW()
                  WHERE call_sid = ?"
            );
            $stmt->execute([$mapped, $duration, $mapped, $callSid]);
        } catch (\Throwable $e) {
            error_log('TwilioVoiceService::updateSessionStatus: ' . $e->getMessage());
        }
    }
}
