<?php

namespace App\Services\Gateway;

use App\Core\Database\Database;
use App\Services\ServiceConfigService;

/**
 * APS Dream Home - Unified Twilio Gateway
 *
 * Single facade for the entire Twilio platform:
 *   - SMS      (Programmable Messaging)
 *   - WhatsApp (Business API via Twilio)
 *   - Voice    (Programmable Voice, TwiML)
 *   - Verify   (OTP / 2FA)
 *
 * Contract:
 *   - NEVER throws. Every public method returns ['success' => bool, 'error' => ?string, ...].
 *   - Every call is recorded in `gateway_logs` (success AND failure).
 *   - Honors `TWILIO_TEST_MODE=true` env var to short-circuit real HTTP.
 *   - Uses cURL (file_get_contents is forbidden in gateways).
 *
 * Required env (all 4 for full operation):
 *   TWILIO_ACCOUNT_SID, TWILIO_AUTH_TOKEN, TWILIO_FROM_NUMBER, TWILIO_WHATSAPP_NUMBER
 */
class TwilioService
{
    /** @var string|null */
    protected $accountSid;

    /** @var string|null */
    protected $authToken;

    /** @var string|null */
    protected $fromNumber;

    /** @var string|null */
    protected $whatsappNumber;

    /** @var bool */
    protected $testMode;

    /** @var string */
    protected $apiBase = 'https://api.twilio.com/2010-04-01';

    /** @var string */
    protected $verifyBase = 'https://verify.twilio.com/v2';

    /** @var \PDO|null */
    protected $pdo;

    /** @var array<string,mixed> in-memory stats since process start */
    private $stats = [
        'calls'      => 0,
        'successes'  => 0,
        'failures'   => 0,
        'rate_limited' => 0,
        'cost_total' => 0.0,
    ];

    /** @var array<int,float> sliding window of recent call timestamps (for rate limiting) */
    private $callTimestamps = [];

    /** @var int max calls per minute */
    protected $rateLimitPerMinute = 100;

    public function __construct()
    {
        // Fallback chain: DB (service_configs) → env → hardcoded default
        $dbCfg = self::getDbConfig();
        $this->accountSid     = ($dbCfg['account_sid'] ?? null)     ?: $this->env('TWILIO_ACCOUNT_SID');
        $this->authToken      = ($dbCfg['auth_token'] ?? null)      ?: $this->env('TWILIO_AUTH_TOKEN');
        $this->fromNumber     = ($dbCfg['from_number'] ?? null)      ?: $this->env('TWILIO_FROM_NUMBER');
        $this->whatsappNumber = ($dbCfg['whatsapp_number'] ?? null)  ?: $this->env('TWILIO_WHATSAPP_NUMBER');
        $this->testMode       = ($dbCfg['test_mode'] ?? '') === '1'
                             || ($dbCfg['test_mode'] ?? '') === 'true'
                             || $this->env('TWILIO_TEST_MODE') === 'true'
                             || $this->env('TWILIO_TEST_MODE') === '1'
                             || getenv('TWILIO_TEST_MODE') === 'true';

        $this->pdo = $this->resolvePdo();
    }

    private static function getDbConfig(): array
    {
        try {
            return ServiceConfigService::getApiConfig('twilio');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Resolve the PDO handle from Database singleton, if available.
     * Public so test suites can verify the connection path.
     */
    public function resolvePdo()
    {
        try {
            if (!class_exists(Database::class, false)) {
                // Try to autoload via the framework's autoloader
                if (defined('APP_ROOT') && file_exists(APP_ROOT . '/app/Core/Autoloader.php')) {
                    require_once APP_ROOT . '/app/Core/Autoloader.php';
                }
            }
            $db = Database::getInstance();
            return method_exists($db, 'getPdo') ? $db->getPdo() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Test/integration helper: inject a PDO handle directly.
     */
    public function setPdo(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ------------------------------------------------------------------ */
    /*  Public API                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Are all 4 credentials present and not placeholders?
     */
    public function isConfigured(): bool
    {
        if (!$this->accountSid || !$this->authToken) return false;
        if (strpos($this->accountSid, 'xxxxx') !== false) return false;
        if (strpos($this->authToken, 'xxxxx') !== false) return false;
        return (bool)($this->fromNumber || $this->whatsappNumber);
    }

    /** @return string|null */
    public function getFromNumber()     { return $this->fromNumber; }
    /** @return string|null */
    public function getWhatsAppNumber() { return $this->whatsappNumber; }
    /** @return string|null */
    public function getAccountSid()     { return $this->accountSid; }

    /**
     * Convenience: are the WhatsApp credentials present?
     */
    public function isWhatsAppConfigured(): bool
    {
        return $this->isConfigured() && (bool)$this->whatsappNumber;
    }

    /**
     * Send an SMS via Twilio Programmable Messaging.
     *
     * @param string $to      E.164 phone (e.g. +919876543210)
     * @param string $message UTF-8 body, <= 1600 chars
     * @param array  $options Optional: statusCallback, validityPeriod, maxPrice, etc.
     * @return array{success:bool,sid:?string,error:?string,cost:float,http_code?:int,to:string,mode:string}
     */
    public function sendSms($to, $message, array $options = [])
    {
        $to = $this->normalizePhone($to);
        if ($to === null) {
            return $this->fail('sms', 'Invalid phone number', null, $to, 0.0, 'invalid_phone');
        }
        if (mb_strlen($message) === 0) {
            return $this->fail('sms', 'Empty message', null, $to, 0.0, 'empty_message');
        }

        if (!$this->isConfigured() || !$this->fromNumber) {
            return $this->fail('sms', 'Twilio SMS not configured (set TWILIO_FROM_NUMBER)', null, $to, 0.0, 'not_configured');
        }

        $params = [
            'From' => $this->fromNumber,
            'To'   => $to,
            'Body' => $message,
        ];
        if (!empty($options['statusCallback'])) {
            $params['StatusCallback'] = $options['statusCallback'];
        }

        return $this->call('POST', '/Accounts/' . $this->accountSid . '/Messages.json', $params, [
            'gateway' => 'twilio', 'action' => 'send_sms', 'recipient' => $to,
        ]);
    }

    /**
     * Send a free-form WhatsApp message via the Twilio WhatsApp sender.
     *
     * @return array same shape as sendSms
     */
    public function sendWhatsApp($to, $message, array $options = [])
    {
        $to = $this->normalizePhone($to);
        if ($to === null) {
            return $this->fail('whatsapp', 'Invalid phone number', null, $to, 0.0, 'invalid_phone');
        }
        if (mb_strlen($message) === 0) {
            return $this->fail('whatsapp', 'Empty message', null, $to, 0.0, 'empty_message');
        }

        if (!$this->isWhatsAppConfigured()) {
            return $this->fail('whatsapp', 'Twilio WhatsApp not configured (set TWILIO_WHATSAPP_NUMBER)', null, $to, 0.0, 'not_configured');
        }

        $params = [
            'From' => 'whatsapp:' . $this->whatsappNumber,
            'To'   => 'whatsapp:' . $to,
            'Body' => $message,
        ];
        if (!empty($options['mediaUrl'])) {
            $params['MediaUrl'] = is_array($options['mediaUrl'])
                ? implode(',', $options['mediaUrl'])
                : (string)$options['mediaUrl'];
        }

        return $this->call('POST', '/Accounts/' . $this->accountSid . '/Messages.json', $params, [
            'gateway' => 'twilio', 'action' => 'send_whatsapp', 'recipient' => $to,
        ]);
    }

    /**
     * Send a WhatsApp template (Content API) via ContentSid + variables.
     *
     * @param string $to            E.164 phone
     * @param string $contentSid    Twilio ContentSid (HX...) or template name
     * @param array  $variables     Associative array of variable_name => value
     * @param string $language      BCP-47 (e.g. "en", "hi") - reserved for future use
     */
    public function sendWhatsAppTemplate($to, $contentSid, array $variables = [], $language = 'en')
    {
        $to = $this->normalizePhone($to);
        if ($to === null) {
            return $this->fail('whatsapp_template', 'Invalid phone number', null, $to, 0.0, 'invalid_phone');
        }
        if (!$this->isWhatsAppConfigured()) {
            return $this->fail('whatsapp_template', 'Twilio WhatsApp not configured', null, $to, 0.0, 'not_configured');
        }
        if (empty($contentSid)) {
            return $this->fail('whatsapp_template', 'Missing ContentSid', null, $to, 0.0, 'missing_template');
        }

        $params = [
            'From'          => 'whatsapp:' . $this->whatsappNumber,
            'To'            => 'whatsapp:' . $to,
            'ContentSid'    => $contentSid,
            'ContentVariables' => json_encode((object)$variables, JSON_UNESCAPED_UNICODE),
        ];

        return $this->call('POST', '/Accounts/' . $this->accountSid . '/Messages.json', $params, [
            'gateway' => 'twilio', 'action' => 'send_whatsapp_template', 'recipient' => $to,
        ]);
    }

    /**
     * Place an outbound voice call (used by OLN system).
     *
     * @param string $to        E.164 phone
     * @param string $twimlUrl  Public URL that returns TwiML XML
     * @param array  $options   Optional: from (overrides default), record, timeout, etc.
     */
    public function makeCall($to, $twimlUrl, array $options = [])
    {
        $to = $this->normalizePhone($to);
        if ($to === null) {
            return $this->fail('voice', 'Invalid phone number', null, $to, 0.0, 'invalid_phone');
        }
        if (empty($twimlUrl)) {
            return $this->fail('voice', 'Missing TwiML URL', null, $to, 0.0, 'missing_twiml');
        }
        if (!$this->isConfigured() || !$this->fromNumber) {
            return $this->fail('voice', 'Twilio not configured', null, $to, 0.0, 'not_configured');
        }

        $params = [
            'From'   => !empty($options['from']) ? $options['from'] : $this->fromNumber,
            'To'     => $to,
            'Url'    => $twimlUrl,
        ];
        if (!empty($options['timeout'])) $params['Timeout'] = (int)$options['timeout'];
        if (!empty($options['record']))  $params['Record']  = 'record-from-answer';
        if (!empty($options['statusCallback'])) $params['StatusCallback'] = $options['statusCallback'];

        return $this->call('POST', '/Accounts/' . $this->accountSid . '/Calls.json', $params, [
            'gateway' => 'twilio', 'action' => 'make_call', 'recipient' => $to,
        ]);
    }

    /**
     * Verify an OTP code that was delivered by sendOtp().
     *
     * @param string $to      E.164 phone
     * @param string $code    The 4-8 digit code the user entered
     * @param string $channel 'sms' or 'call'
     */
    public function verifyOtp($to, $code, $channel = 'sms')
    {
        $to = $this->normalizePhone($to);
        if ($to === null) {
            return ['success' => false, 'error' => 'Invalid phone', 'approved' => false, 'status' => 'invalid'];
        }
        $serviceSid = $this->env('TWILIO_VERIFY_SERVICE_SID');
        if (!$serviceSid) {
            return ['success' => false, 'error' => 'Verify service not configured (set TWILIO_VERIFY_SERVICE_SID)', 'approved' => false];
        }
        $params = ['To' => $to, 'Code' => $code];
        $resp = $this->call('POST', '/Services/' . $serviceSid . '/VerificationCheck.json', $params, [
            'gateway' => 'twilio', 'action' => 'verify_otp', 'recipient' => $to, 'verifyBase' => true,
        ]);
        if ($resp['success']) {
            $body = $resp['body'] ?? [];
            $resp['approved'] = (isset($body['status']) && strtolower($body['status']) === 'approved');
            $resp['status']   = $body['status'] ?? 'unknown';
            $resp['sid']      = $body['sid'] ?? $resp['sid'];
        } else {
            $resp['approved'] = false;
        }
        return $resp;
    }

    /**
     * Start an OTP delivery (sends the code to the user).
     */
    public function sendOtp($to, $channel = 'sms')
    {
        $to = $this->normalizePhone($to);
        if ($to === null) {
            return $this->fail('verify', 'Invalid phone', null, $to, 0.0, 'invalid_phone');
        }
        $serviceSid = $this->env('TWILIO_VERIFY_SERVICE_SID');
        if (!$serviceSid) {
            return $this->fail('verify', 'Verify service not configured (set TWILIO_VERIFY_SERVICE_SID)', null, $to, 0.0, 'not_configured');
        }
        $params = ['To' => $to, 'Channel' => $channel];
        return $this->call('POST', '/Services/' . $serviceSid . '/Verifications.json', $params, [
            'gateway' => 'twilio', 'action' => 'send_otp', 'recipient' => $to, 'verifyBase' => true,
        ]);
    }

    /**
     * Get current account balance (for cost monitoring).
     */
    public function getBalance()
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Not configured', 'balance' => null, 'currency' => null];
        }
        $resp = $this->call('GET', '/Accounts/' . $this->accountSid . '/Balance.json', [], [
            'gateway' => 'twilio', 'action' => 'get_balance', 'recipient' => null,
        ]);
        if ($resp['success'] && isset($resp['body'])) {
            $resp['balance']  = $resp['body']['balance']  ?? null;
            $resp['currency'] = $resp['body']['currency'] ?? 'USD';
        }
        return $resp;
    }

    /**
     * Look up a message by SID and return its delivery state.
     */
    public function getMessageStatus($sid)
    {
        if (empty($sid)) return ['success' => false, 'error' => 'Missing sid', 'status' => null];
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Not configured', 'status' => null];
        }
        $resp = $this->call('GET', '/Accounts/' . $this->accountSid . '/Messages/' . $sid . '.json', [], [
            'gateway' => 'twilio', 'action' => 'get_message_status', 'recipient' => $sid,
        ]);
        if ($resp['success'] && isset($resp['body'])) {
            $resp['status'] = $resp['body']['status']   ?? null;
            $resp['error_code']    = $resp['body']['error_code']    ?? null;
            $resp['error_message'] = $resp['body']['error_message'] ?? null;
        }
        return $resp;
    }

    /**
     * Process-local call stats (resets each request).
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Recent gateway log rows from DB.
     */
    public function getRecentLogs($limit = 50, $gateway = null)
    {
        if (!$this->pdo) return [];
        try {
            if ($gateway) {
                $stmt = $this->pdo->prepare(
                    'SELECT * FROM gateway_logs WHERE gateway = ? ORDER BY id DESC LIMIT ' . (int)$limit
                );
                $stmt->execute([$gateway]);
            } else {
                $stmt = $this->pdo->prepare(
                    'SELECT * FROM gateway_logs ORDER BY id DESC LIMIT ' . (int)$limit
                );
                $stmt->execute();
            }
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Aggregate stats per gateway (for admin dashboard cards).
     */
    public function getGatewayStats($hours = 24)
    {
        if (!$this->pdo) return [];
        try {
            $sql = "SELECT gateway,
                           COUNT(*) AS total,
                           SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) AS success_count,
                           SUM(CASE WHEN status='error' THEN 1 ELSE 0 END)   AS error_count,
                           COALESCE(SUM(cost), 0)                            AS total_cost,
                           MAX(created_at)                                   AS last_call_at
                      FROM gateway_logs
                     WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                     GROUP BY gateway";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$hours]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Generic Twilio API call. NEVER throws.
     *
     * @param string $method   'GET' | 'POST' | 'PUT' | 'DELETE'
     * @param string $path     e.g. '/Accounts/AC.../Messages.json'
     * @param array  $params   form-encoded for POST, query for GET
     * @param array  $context  ['gateway'=>string,'action'=>string,'recipient'=>?string,'verifyBase'=>bool]
     */
    private function call($method, $path, array $params, array $context)
    {
        $start = microtime(true);
        $context = array_merge(['gateway' => 'twilio', 'action' => 'unknown', 'recipient' => null, 'verifyBase' => false], $context);

        // Rate limiting (defense in depth, per-process)
        if (!$this->checkRateLimit()) {
            $this->stats['rate_limited']++;
            $this->logCall($context['gateway'], $context['action'], $params, null, 0, 0, 'rate_limited', 'Too many Twilio calls in 1 minute');
            return ['success' => false, 'error' => 'Rate limit exceeded', 'sid' => null, 'cost' => 0.0, 'rate_limited' => true];
        }

        $base = !empty($context['verifyBase']) ? $this->verifyBase : $this->apiBase;
        $url  = $base . $path . ($method === 'GET' && !empty($params) ? '?' . http_build_query($params) : '');

        // Test mode short-circuit
        if ($this->testMode) {
            $duration = (int)((microtime(true) - $start) * 1000);
            $sid = 'SM_TEST_' . strtoupper(bin2hex(random_bytes(8)));
            $body = [
                'sid'      => $sid,
                'status'   => 'queued',
                'to'       => $params['To']   ?? null,
                'from'     => $params['From'] ?? null,
                'body'     => $params['Body'] ?? null,
                'price'    => '-0.0075',
                'price_unit' => 'USD',
                'test_mode' => true,
            ];
            $this->stats['calls']++;
            $this->stats['successes']++;
            $this->logCall($context['gateway'], $context['action'], $params, $body, 201, $duration, 'success', null, 0.0075, $context['recipient'] ?? null);
            return [
                'success'   => true,
                'sid'       => $sid,
                'error'     => null,
                'cost'      => 0.0075,
                'http_code' => 201,
                'to'        => $context['recipient'] ?? null,
                'mode'      => 'test',
                'body'      => $body,
            ];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->accountSid . ':' . $this->authToken);

        $headers = ['Accept: application/json'];
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $duration = (int)((microtime(true) - $start) * 1000);
        $body     = $this->decode($raw);
        $ok       = ($httpCode >= 200 && $httpCode < 300);
        $errMsg   = $ok ? null : ($body['message'] ?? $body['error_message'] ?? $curlErr ?: "HTTP $httpCode");
        $sid      = $body['sid'] ?? null;
        $cost     = $this->extractCost($body);

        $this->stats['calls']++;
        if ($ok) { $this->stats['successes']++; $this->stats['cost_total'] += $cost; }
        else     { $this->stats['failures']++; }

        $this->logCall(
            $context['gateway'],
            $context['action'],
            $params,
            $body,
            $httpCode,
            $duration,
            $ok ? 'success' : 'error',
            $errMsg,
            $cost,
            $context['recipient'] ?? null
        );

        return [
            'success'   => $ok,
            'sid'       => $sid,
            'error'     => $errMsg,
            'cost'      => (float)$cost,
            'http_code' => $httpCode,
            'to'        => $context['recipient'] ?? null,
            'mode'      => 'live',
            'body'      => $body,
        ];
    }

    /**
     * Build a graceful failure response without contacting Twilio.
     */
    private function fail($action, $error, $sid, $recipient, $cost, $code)
    {
        $this->stats['calls']++;
        $this->stats['failures']++;
        $this->logCall('twilio', $action, [], null, 0, 0, 'error', $error . " (code: $code)", $cost);
        return [
            'success' => false,
            'sid'     => $sid,
            'error'   => $error,
            'cost'    => (float)$cost,
            'to'      => $recipient,
            'code'    => $code,
        ];
    }

    /**
     * Sliding-window rate limit check (process-local).
     */
    private function checkRateLimit()
    {
        $now = microtime(true);
        $cutoff = $now - 60.0;
        $this->callTimestamps = array_values(array_filter(
            $this->callTimestamps,
            function ($t) use ($cutoff) { return $t > $cutoff; }
        ));
        if (count($this->callTimestamps) >= $this->rateLimitPerMinute) {
            return false;
        }
        $this->callTimestamps[] = $now;
        return true;
    }

    /**
     * Persist a row to `gateway_logs`. NEVER throws.
     */
    private function logCall($gateway, $action, $request, $response, $httpCode, $durationMs, $status, $error = null, $cost = 0.0, $recipient = null)
    {
        if ($recipient === null) {
            $recipient = is_string($request['To'] ?? null) ? $request['To'] : null;
        }
        if (!$this->pdo) {
            // Fall back to file log so the dev sees the attempt
            $this->logToFile(compact('gateway', 'action', 'recipient', 'request', 'response', 'httpCode', 'durationMs', 'status', 'error', 'cost'));
            return;
        }
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO gateway_logs
                    (gateway, action, recipient, request_payload, response_payload, status, http_code, duration_ms, cost, error_message, created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,NOW())'
            );
            $stmt->execute([
                $gateway,
                $action,
                $recipient !== null ? (string)substr((string)$recipient, 0, 100) : null,
                $this->safeJson($request),
                $this->safeJson($response),
                $status,
                (int)$httpCode,
                (int)$durationMs,
                (float)$cost,
                $error !== null ? (string)substr($error, 0, 1000) : null,
            ]);
        } catch (\Throwable $e) {
            // DB might be down; fall back to file
            $this->logToFile([
                'gateway' => $gateway, 'action' => $action, 'recipient' => $recipient,
                'request' => $request, 'response' => $response, 'httpCode' => $httpCode,
                'durationMs' => $durationMs, 'status' => $status, 'error' => $error,
                'cost' => $cost, 'db_error' => $e->getMessage(),
            ]);
        }
    }

    private function logToFile(array $entry)
    {
        $dir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : __DIR__ . '/../../../storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $file = $dir . '/gateway_twilio.log';
        @file_put_contents($file, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function normalizePhone($phone)
    {
        if (!is_string($phone)) return null;
        $digits = preg_replace('/[^0-9+]/', '', $phone);
        if ($digits === '' || $digits === null) return null;
        if ($digits[0] !== '+') {
            // Strip any leading zeros
            $digits = ltrim($digits, '0');
            if ($digits === '' || $digits === null) return null;
            if (strlen($digits) === 10) $digits = '+91' . $digits;
            elseif (strlen($digits) === 12 && substr($digits, 0, 2) === '91') $digits = '+' . $digits;
            elseif (strlen($digits) >= 11 && strlen($digits) <= 15) $digits = '+' . $digits;
            else return null;
        }
        return preg_match('/^\+[1-9]\d{6,14}$/', $digits) ? $digits : null;
    }

    private function extractCost($body)
    {
        if (!is_array($body)) return 0.0;
        if (isset($body['price']) && is_string($body['price'])) {
            $v = (float)$body['price'];
            return abs($v);
        }
        if (isset($body['balance'])) {
            // Balance endpoint doesn't return per-call cost
            return 0.0;
        }
        return 0.0;
    }

    private function decode($raw)
    {
        if (!is_string($raw) || $raw === '') return null;
        $j = json_decode($raw, true);
        return is_array($j) ? $j : ['raw' => $raw];
    }

    private function safeJson($v)
    {
        if ($v === null) return null;
        $j = is_string($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
        return mb_substr((string)$j, 0, 65535);
    }

    protected function env($name)
    {
        $v = $_ENV[$name] ?? null;
        if ($v === null || $v === '') $v = getenv($name) ?: null;
        return $v;
    }
}
