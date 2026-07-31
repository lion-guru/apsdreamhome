<?php

namespace App\Services\Voice;

use App\Core\Database\Database;
use App\Services\Gateway\TwilioService;
use \App\Traits\ServiceTenantTrait;

/**
 * APS Dream Home - Twilio Voice Gateway
 *
 * Single facade for Twilio Programmable Voice:
 *   - Outbound calls (delegates to TwilioService::makeCall)
 *   - TwiML URL construction (via TwiMLBuilder)
 *   - Call status / recording / transfer / hangup
 *   - HMAC signature verification for inbound webhooks
 *
 * All operations are recorded in `gateway_logs` with `gateway='twilio-voice'`
 * and call details mirrored in `ai_call_sessions` for the OLN pipeline.
 */
class TwilioVoiceService
{
    /** @var TwilioService */
    protected $twilio;

    /** @var \PDO|null */
    protected $pdo;

    /** @var array in-process stats */
    protected $stats = [
        'calls_initiated'  => 0,
        'calls_transferred' => 0,
        'calls_hungup'     => 0,
        'status_checks'    => 0,
        'recordings_fetched' => 0,
    ];

    public function __construct(TwilioService $twilio = null)
    {
        $this->twilio = $twilio ?: new TwilioService();
        try {
            $db = Database::getInstance();
            $this->pdo = method_exists($db, 'getPdo') ? $db->getPdo() : null;
        } catch (\Throwable $e) {
            $this->pdo = null;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Outbound call                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Make an outbound voice call.
     *
     * @param string $to         E.164 phone number
     * @param string $twimlUrl   Public URL serving TwiML (e.g. /api/twilio/voice)
     * @param string|null $from  Override the default From number
     * @param array   $options   { record, timeout, statusCallback, leadId, agentId, sessionMeta }
     * @return array{success:bool,sid:?string,error:?string,call_sid:string}
     */
    public function makeCall($to, $twimlUrl, $from = null, array $options = [])
    {
        $this->stats['calls_initiated']++;

        $resp = $this->twilio->makeCall($to, $twimlUrl, array_filter([
            'from'            => $from,
            'record'          => !empty($options['record']),
            'timeout'         => $options['timeout'] ?? null,
            'statusCallback'  => $options['statusCallback'] ?? null,
        ], function ($v) { return $v !== null && $v !== ''; }));

        $callSid = $resp['sid'] ?? null;
        $ok = (bool)($resp['success'] ?? false);

        if ($ok && $callSid && !empty($options['leadId'])) {
            $this->recordSession([
                'lead_id'   => $options['leadId'],
                'phone'     => $to,
                'call_sid'  => $callSid,
                'agent_id'  => $options['agentId'] ?? null,
                'twiml_url' => $twimlUrl,
                'meta'      => $options['sessionMeta'] ?? [],
            ]);
        }

        $this->logCall('make_call', $to, $callSid, $ok ? 'success' : 'error', $resp['error'] ?? null);

        return [
            'success'   => $ok,
            'sid'       => $callSid,
            'error'     => $resp['error'] ?? null,
            'call_sid'  => $callSid,
            'to'        => $to,
            'twiml_url' => $twimlUrl,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  TwiML generation                                                   */
    /* ------------------------------------------------------------------ */

    /**
     * Build a TwiMLBuilder (for advanced use). Most callers should use
     * `makeCall()` with a static TwiML URL that calls one of the
     * generate* methods below.
     */
    public function builder()
    {
        return new TwiMLBuilder();
    }

    /**
     * Generate TwiML for an AI-driven site-visit booking call.
     * Asks the caller to confirm interest, captures DTMF 1/2/3.
     */
    public function generateSiteVisitTwiML(array $params = [])
    {
        $gatherUrl = $params['gatherUrl'] ?? '/api/twilio/voice/gather';
        $message   = $params['message']
            ?? "Hello, this is APS Dream Home. We're calling about your site visit request. Press 1 to confirm, 2 to reschedule, or 3 to speak with an agent.";

        return $this->builder()
            ->say($message, 'alice', 'en')
            ->gather([
                'numDigits'   => 1,
                'action'      => $gatherUrl,
                'method'      => 'POST',
                'timeout'     => 8,
                'finishOnKey' => '#',
            ])
            ->pause(1)
            ->say("We didn't receive any input. Goodbye.", 'alice', 'en')
            ->hangup()
            ->toXml();
    }

    /**
     * Generate TwiML for a property-inquiry call.
     * Speaks a property summary, captures interest level via DTMF.
     */
    public function generatePropertyInquiryTwiML(array $params = [])
    {
        $gatherUrl = $params['gatherUrl'] ?? '/api/twilio/voice/gather';
        $property  = $params['property'] ?? [];
        $name      = $property['name']     ?? 'a property';
        $price     = $property['price']    ?? null;
        $location  = $property['location'] ?? 'our featured location';

        $priceStr = $price !== null
            ? " listed at {$price} rupees"
            : '';

        $message = $params['message']
            ?? "Hello, this is APS Dream Home calling about {$name}{$priceStr} in {$location}. "
             . "Press 1 if you're interested, 2 to receive a brochure, or 3 to opt out.";

        return $this->builder()
            ->say($message, 'alice', 'en')
            ->gather([
                'numDigits' => 1,
                'action'    => $gatherUrl,
                'method'    => 'POST',
                'timeout'   => 8,
            ])
            ->pause(1)
            ->say("Thank you. Goodbye.", 'alice', 'en')
            ->hangup()
            ->toXml();
    }

    /**
     * Generate TwiML for a lead follow-up call.
     */
    public function generateFollowUpTwiML(array $params = [])
    {
        $gatherUrl = $params['gatherUrl'] ?? '/api/twilio/voice/gather';
        $leadName  = $params['leadName']  ?? 'there';
        $message   = $params['message']
            ?? "Hi {$leadName}, this is APS Dream Home following up on your recent inquiry. "
             . "Press 1 to continue the conversation, 2 to schedule a callback, or 3 to opt out.";

        return $this->builder()
            ->say($message, 'alice', 'en')
            ->gather([
                'numDigits' => 1,
                'action'    => $gatherUrl,
                'method'    => 'POST',
                'timeout'   => 8,
            ])
            ->pause(1)
            ->say("Thank you for your time. Goodbye.", 'alice', 'en')
            ->hangup()
            ->toXml();
    }

    /**
     * Generate a simple inbound greeting (used by the /api/twilio/voice webhook).
     */
    public function generateGreetingTwiML($message = null)
    {
        $msg = $message ?? "Thank you for calling APS Dream Home. Please hold while we connect you.";
        return $this->builder()
            ->say($msg, 'alice', 'en')
            ->pause(1)
            ->hangup()
            ->toXml();
    }

    /* ------------------------------------------------------------------ */
    /*  Call status & recordings                                           */
    /* ------------------------------------------------------------------ */

    /**
     * Look up a call's current status (queued, ringing, in-progress, completed, failed, busy, no-answer).
     */
    public function getCallStatus($callSid)
    {
        $this->stats['status_checks']++;
        $resp = $this->twilioHttp('GET',
            '/Accounts/' . $this->twilio->getAccountSid() . '/Calls/' . $callSid . '.json',
            [],
            'get_call_status',
            $callSid
        );
        $ok = (bool)($resp['success'] ?? false);
        return [
            'success' => $ok,
            'sid'     => $callSid,
            'status'  => $resp['body']['status'] ?? null,
            'duration'=> $resp['body']['duration'] ?? null,
            'error'   => $resp['error'] ?? null,
        ];
    }

    /**
     * Fetch recordings for a given call SID.
     * Returns array of {sid, duration, uri, media_url} objects.
     */
    public function getCallRecordings($callSid)
    {
        $this->stats['recordings_fetched']++;
        $resp = $this->twilioHttp('GET',
            '/Accounts/' . $this->twilio->getAccountSid() . '/Calls/' . $callSid . '/Recordings.json',
            [],
            'get_call_recordings',
            $callSid
        );
        $ok = (bool)($resp['success'] ?? false);
        $items = $resp['body']['recordings'] ?? [];
        $out = [];
        foreach ($items as $r) {
            $out[] = [
                'sid'         => $r['sid']         ?? null,
                'duration'    => $r['duration']    ?? null,
                'uri'         => $r['uri']         ?? null,
                'media_url'   => 'https://api.twilio.com' . ($r['uri'] ?? ''),
                'date_created'=> $r['date_created']?? null,
            ];
        }
        return [
            'success'    => $ok,
            'call_sid'   => $callSid,
            'recordings' => $out,
            'error'      => $resp['error'] ?? null,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Live call control                                                  */
    /* ------------------------------------------------------------------ */

    /**
     * Transfer a live call to another number.
     * Updates the existing call's TwiML URL to a new <Dial> flow.
     */
    public function transferCall($callSid, $to)
    {
        $this->stats['calls_transferred']++;
        $twiml = $this->builder()
            ->say("Transferring your call now.", 'alice', 'en')
            ->dial($to, ['timeout' => 30, 'callerId' => $this->twilio->getFromNumber()])
            ->toXml();

        $params = ['Url' => 'data:application/xml;base64,' . base64_encode($twiml)];
        $resp = $this->twilioHttp('POST',
            '/Accounts/' . $this->twilio->getAccountSid() . '/Calls/' . $callSid . '.json',
            $params,
            'transfer_call',
            $callSid
        );
        $ok = (bool)($resp['success'] ?? false);
        $this->logCall('transfer_call', $to, $callSid, $ok ? 'success' : 'error', $resp['error'] ?? null);
        return ['success' => $ok, 'sid' => $callSid, 'transferred_to' => $to, 'error' => $resp['error'] ?? null];
    }

    /**
     * End a call in progress.
     */
    public function hangupCall($callSid)
    {
        $this->stats['calls_hungup']++;
        $resp = $this->twilioHttp('POST',
            '/Accounts/' . $this->twilio->getAccountSid() . '/Calls/' . $callSid . '.json',
            ['Status' => 'completed'],
            'hangup_call',
            $callSid
        );
        $ok = (bool)($resp['success'] ?? false);
        $this->logCall('hangup_call', null, $callSid, $ok ? 'success' : 'error', $resp['error'] ?? null);
        return ['success' => $ok, 'sid' => $callSid, 'error' => $resp['error'] ?? null];
    }

    /* ------------------------------------------------------------------ */
    /*  Webhook signature verification                                     */
    /* ------------------------------------------------------------------ */

    /**
     * Verify an inbound webhook request from Twilio.
     * Twilio signs the URL + sorted POST params with HMAC-SHA1 using your AuthToken.
     *
     * @param string $url       Full request URL (with query string)
     * @param array  $params    All POST parameters (sorted by key)
     * @param string $signature Value of X-Twilio-Signature header
     */
    public function verifyWebhookSignature($url, array $params, $signature)
    {
        $token = $_ENV['TWILIO_AUTH_TOKEN'] ?? getenv('TWILIO_AUTH_TOKEN') ?: '';
        if (!$token || !$signature) return false;
        ksort($params);
        $data = $url . implode('', array_map(
            function ($k) use ($params) { return $k . $params[$k]; },
            array_keys($params)
        ));
        $expected = base64_encode(hash_hmac('sha1', $data, $token, true));
        return hash_equals($expected, $signature);
    }

    /**
     * Public-but-undocumented raw HTTP helper for the voice service.
     * Calls Twilio's REST API directly (uses the same auth header as TwilioService).
     *
     * @param string $method   GET | POST
     * @param string $path     e.g. /Accounts/AC.../Calls/CA.../Recordings.json
     * @param array  $params   body params (form-encoded for POST, query for GET)
     * @param string $action   short action name (for logging)
     * @param string $recipient log recipient
     * @return array{success:bool,body:array,error:?string,http_code:int}
     */
    public function twilioHttp($method, $path, array $params, $action = 'unknown', $recipient = null)
    {
        $sid  = $this->twilio->getAccountSid();
        $auth = $_ENV['TWILIO_AUTH_TOKEN'] ?? getenv('TWILIO_AUTH_TOKEN') ?: '';
        $testMode = ($_ENV['TWILIO_TEST_MODE'] ?? '') === 'true' || getenv('TWILIO_TEST_MODE') === 'true';

        $url = 'https://api.twilio.com/2010-04-01' . $path;
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        if ($testMode || !$sid || !$auth) {
            // Test mode / no creds: return a synthetic 200 with empty body.
            $this->logCall($action, $recipient, null, 'success', null);
            return [
                'success'   => true,
                'body'      => ['test_mode' => true, 'status' => 'in-progress', 'recordings' => []],
                'error'     => null,
                'http_code' => 200,
            ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $auth);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded']);
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        $raw = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        $body = $raw ? json_decode($raw, true) : [];
        $ok = $http >= 200 && $http < 300;
        $this->logCall($action, $recipient, null, $ok ? 'success' : 'error', $ok ? null : ($body['message'] ?? $err));
        return [
            'success'   => $ok,
            'body'      => is_array($body) ? $body : ['raw' => $raw],
            'error'     => $ok ? null : ($body['message'] ?? $err),
            'http_code' => (int)$http,
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Session persistence                                                */
    /* ------------------------------------------------------------------ */

    /**
     * Record the call in ai_call_sessions for the OLN pipeline.
     * Mirrors what VoiceCallService::initiateCall() does for in-app sessions.
     *
     * Note: stores TwiML URL + session meta in `extracted_data` JSON column
     * to avoid creating new columns. The real TwiML URL is also persisted
     * via the webhook status callback flow when it lands.
     */
    protected function recordSession(array $data)
    {
        if (!$this->pdo) return;
        try {
            $existingCols = $this->getTableColumns('ai_call_sessions');
            $row = [
                'lead_id'   => $data['lead_id']   ?? null,
                'phone'     => $data['phone']     ?? null,
                'call_type' => 'outbound',
                'status'    => 'queued',
                'ai_agent_id' => $data['agent_id'] ?? null,
                'call_sid'  => $data['call_sid']  ?? null,
                'started_at'=> date('Y-m-d H:i:s'),
                'created_at'=> date('Y-m-d H:i:s'),
                'updated_at'=> date('Y-m-d H:i:s'),
            ];
            // Persist TwiML URL + meta in extracted_data JSON column if available.
            if (in_array('extracted_data', $existingCols, true)) {
                $row['extracted_data'] = json_encode(array_merge(
                    $data['sessionMeta'] ?? [],
                    ['twiml_url' => $data['twiml_url'] ?? null]
                ));
            }
            // Only insert columns that actually exist in the table.
            $cols = array_values(array_intersect(array_keys($row), $existingCols));
            if (empty($cols)) return;
            $placeholders = implode(',', array_fill(0, count($cols), '?'));
            $colList = implode(',', $cols);
            $stmt = $this->pdo->prepare("INSERT INTO ai_call_sessions ({$colList}) VALUES ({$placeholders})");
            $stmt->execute(array_map(fn($c) => $row[$c], $cols));
        } catch (\Throwable $e) {
            $this->logToFile([
                'action' => 'recordSession',
                'error'  => $e->getMessage(),
                'data'   => $data,
            ]);
        }
    }

    /**
     * Cache the columns of a table (avoid SHOW COLUMNS on every call).
     */
    protected $columnCache = [];
    protected function getTableColumns($table)
    {
        if (isset($this->columnCache[$table])) return $this->columnCache[$table];
        if (!$this->pdo) return [];
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM `{$table}`");
            $cols = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
            $this->columnCache[$table] = $cols;
            return $cols;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Update a session when Twilio sends status callbacks.
     */
    public function updateSessionStatus($callSid, $status, array $extra = [])
    {
        if (!$this->pdo || !$callSid) return;
        try {
            $row = [
                'status'        => $status,
                'duration_seconds' => $extra['duration'] ?? null,
                'ended_at'      => in_array($status, ['completed', 'failed', 'busy', 'no-answer', 'canceled'], true)
                    ? date('Y-m-d H:i:s') : null,
                'updated_at'    => date('Y-m-d H:i:s'),
            ];
            $sets = [];
            $vals = [];
            foreach ($row as $k => $v) {
                $sets[] = "{$k} = ?";
                $vals[] = $v;
            }
            $vals[] = $callSid;
            $stmt = $this->pdo->prepare("UPDATE ai_call_sessions SET " . implode(',', $sets) . " WHERE call_sid = ?");
            $stmt->execute($vals);
        } catch (\Throwable $e) {
            $this->logToFile([
                'action' => 'updateSessionStatus',
                'error'  => $e->getMessage(),
                'call_sid' => $callSid,
            ]);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Stats / observability                                              */
    /* ------------------------------------------------------------------ */

    public function getStats()
    {
        return $this->stats;
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                          */
    /* ------------------------------------------------------------------ */

    protected function logCall($action, $recipient, $callSid, $status, $error = null)
    {
        if (!$this->pdo) {
            $this->logToFile(compact('action', 'recipient', 'callSid', 'status', 'error'));
            return;
        }
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO gateway_logs
                    (gateway, action, recipient, request_payload, response_payload, status, http_code, duration_ms, cost, error_message, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,NOW())'
            );
            $stmt->execute([
                'twilio-voice',
                $action,
                $callSid ?? $recipient,
                json_encode(['recipient' => $recipient, 'call_sid' => $callSid]),
                null,
                $status,
                0,
                0,
                0.013,    // ~$0.013 per minute outbound voice
                $error,
            ]);
        } catch (\Throwable $e) {
            $this->logToFile(['action' => $action, 'error' => $e->getMessage()]);
        }
    }

    protected function logToFile(array $entry)
    {
        $dir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : dirname(__DIR__, 3) . '/storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents($dir . '/gateway_twilio_voice.log', json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
