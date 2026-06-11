<?php
/**
 * Unified Razorpay Service
 *
 * Single source of truth for all Razorpay API interactions.
 * - Real HTTP calls via cURL (no simulated responses)
 * - HMAC-SHA256 signature verification with timing-safe compare
 * - PCI-aware logging: amount + status + transaction_id, NEVER card details
 * - Test mode: RAZORPAY_TEST_MODE=true short-circuits real network calls
 * - Auto-retry on 5xx (max 3 attempts), no retry on 4xx
 * - Every call logged to gateway_logs table
 *
 * All public methods return: ['success' => bool, 'data' => ?, 'error' => ?]
 * No method throws an exception; failures are returned as data.
 */

namespace App\Services\Gateway;

use App\Core\Database\Database;
use App\Services\ServiceConfigService;
use PDO;

class RazorpayService
{
    public const VERSION = '1.0.0';
    private const API_BASE = 'https://api.razorpay.com/v1';
    private const MAX_RETRIES = 3;
    private const DEFAULT_TIMEOUT = 30;

    private $db;
    private $keyId;
    private $keySecret;
    private $webhookSecret;
    private bool $testMode;
    private int $timeout;
    private $logger;

    public function __construct(?PDO $pdo = null, ?\Closure $logger = null)
    {
        try {
            $this->db = $pdo ?? Database::getInstance()->getConnection();
        } catch (\Throwable $e) {
            $this->db = $pdo;
        }

        // Fallback chain: DB (service_configs) → env → hardcoded default
        $dbCfg = self::getDbConfig();
        $this->keyId         = $dbCfg['key_id']         ?? ($_ENV['RAZORPAY_KEY_ID']         ?? getenv('RAZORPAY_KEY_ID')         ?: 'rzp_test_default');
        $this->keySecret     = $dbCfg['key_secret']     ?? ($_ENV['RAZORPAY_KEY_SECRET']     ?? getenv('RAZORPAY_KEY_SECRET')     ?: 'secret_default');
        $this->webhookSecret = $dbCfg['webhook_secret'] ?? ($_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? getenv('RAZORPAY_WEBHOOK_SECRET') ?: 'whsec_default');
        $this->testMode      = filter_var(
            $dbCfg['test_mode'] ?? ($_ENV['RAZORPAY_TEST_MODE'] ?? getenv('RAZORPAY_TEST_MODE') ?: 'false'),
            FILTER_VALIDATE_BOOLEAN
        );
        $this->timeout       = (int)($_ENV['RAZORPAY_TIMEOUT'] ?? self::DEFAULT_TIMEOUT);
        $this->logger        = $logger;
    }

    private static function getDbConfig(): array
    {
        try {
            return ServiceConfigService::getApiConfig('razorpay');
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function isConfigured(): bool
    {
        $defaults = ['rzp_test_default', 'rzp_test_xxxxxxxxxxxx', 'secret_default', ''];
        if (in_array($this->keyId, $defaults, true) || in_array($this->keySecret, $defaults, true)) {
            return false;
        }
        return str_starts_with($this->keyId, 'rzp_') && strlen($this->keySecret) >= 10;
    }

    public function isTestMode(): bool
    {
        return $this->testMode;
    }

    public function getKeyId(): string
    {
        return $this->keyId;
    }

    /* ------------------------------------------------------------------ *
     *  ORDERS
     * ------------------------------------------------------------------ */

    public function createOrder($amount, $currency = 'INR', $receipt = null, $notes = []): array
    {
        if (!is_numeric($amount) || (float)$amount <= 0) {
            return ['success' => false, 'data' => null, 'error' => 'Invalid amount: must be positive number'];
        }
        $receipt = $receipt ?: ('RCPT_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 6));
        $payload = [
            'amount'          => (int)round(((float)$amount) * 100),
            'currency'        => strtoupper($currency),
            'receipt'         => substr((string)$receipt, 0, 40),
            'payment_capture' => 1,
            'notes'           => $this->sanitizeNotes($notes),
        ];
        $resp = $this->call('POST', '/orders', $payload, (int)round(((float)$amount) * 100), null);
        if ($resp['success'] && isset($resp['data']['id'])) {
            try {
                $stmt = $this->db?->prepare("INSERT INTO payment_orders
                    (order_id, gateway, booking_id, user_id, customer_name, customer_email, customer_phone, amount, currency, status, description, notes, receipt, expires_at)
                    VALUES (?, 'razorpay', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $expires = date('Y-m-d H:i:s', time() + 30 * 60);
                $stmt?->execute([
                    $resp['data']['id'],
                    $notes['booking_id'] ?? null,
                    $notes['user_id'] ?? null,
                    $notes['customer_name'] ?? null,
                    $notes['customer_email'] ?? null,
                    $notes['customer_phone'] ?? null,
                    $amount,
                    strtoupper($currency),
                    'created',
                    $notes['description'] ?? null,
                    json_encode($this->sanitizeNotes($notes)),
                    $receipt,
                    $expires,
                ]);
            } catch (\Throwable $e) {
                $this->info("createOrder: db log failed: " . $e->getMessage());
            }
        }
        return $resp;
    }

    public function fetchOrder($orderId): array
    {
        if (!$orderId) {
            return ['success' => false, 'data' => null, 'error' => 'orderId required'];
        }
        return $this->call('GET', '/orders/' . urlencode($orderId));
    }

    /* ------------------------------------------------------------------ *
     *  SIGNATURE VERIFICATION (HMAC-SHA256, timing-safe)
     * ------------------------------------------------------------------ */

    public function verifyPaymentSignature($orderId, $paymentId, $signature): bool
    {
        if (!$orderId || !$paymentId || !$signature) {
            return false;
        }
        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expected, (string)$signature);
    }

    public function verifyWebhookSignature($payload, $signature): bool
    {
        if (!$signature) {
            return false;
        }
        $body = is_string($payload) ? $payload : json_encode($payload);
        $expected = hash_hmac('sha256', $body, $this->webhookSecret);
        return hash_equals($expected, (string)$signature);
    }

    /* ------------------------------------------------------------------ *
     *  PAYMENTS
     * ------------------------------------------------------------------ */

    public function capturePayment($paymentId, $amount): array
    {
        if (!$paymentId || !is_numeric($amount) || (float)$amount <= 0) {
            return ['success' => false, 'data' => null, 'error' => 'paymentId and positive amount required'];
        }
        return $this->call('POST', '/payments/' . urlencode($paymentId) . '/capture', [
            'amount'   => (int)round(((float)$amount) * 100),
            'currency' => 'INR',
        ], (int)round(((float)$amount) * 100), $paymentId);
    }

    public function fetchPayment($paymentId): array
    {
        if (!$paymentId) {
            return ['success' => false, 'data' => null, 'error' => 'paymentId required'];
        }
        return $this->call('GET', '/payments/' . urlencode($paymentId));
    }

    /* ------------------------------------------------------------------ *
     *  REFUNDS
     * ------------------------------------------------------------------ */

    public function createRefund($paymentId, $amount = null, $speed = 'normal', $notes = []): array
    {
        if (!$paymentId) {
            return ['success' => false, 'data' => null, 'error' => 'paymentId required'];
        }
        $body = [];
        if ($amount !== null && is_numeric($amount) && (float)$amount > 0) {
            $body['amount'] = (int)round(((float)$amount) * 100);
        }
        if (in_array($speed, ['normal', 'optimum'], true)) {
            $body['speed'] = $speed;
        }
        if (!empty($notes)) {
            $body['notes'] = $this->sanitizeNotes($notes);
        }
        return $this->call('POST', '/payments/' . urlencode($paymentId) . '/refund', $body,
            isset($body['amount']) ? $body['amount'] : null, $paymentId);
    }

    public function fetchRefund($refundId): array
    {
        if (!$refundId) {
            return ['success' => false, 'data' => null, 'error' => 'refundId required'];
        }
        return $this->call('GET', '/refunds/' . urlencode($refundId));
    }

    /* ------------------------------------------------------------------ *
     *  CUSTOMERS
     * ------------------------------------------------------------------ */

    public function createCustomer($name, $email, $contact, $notes = []): array
    {
        if (!$name || !$email) {
            return ['success' => false, 'data' => null, 'error' => 'name and email required'];
        }
        $payload = [
            'name'    => substr((string)$name, 0, 50),
            'email'   => substr((string)$email, 0, 100),
            'contact' => preg_replace('/[^0-9+]/', '', (string)$contact),
            'notes'   => $this->sanitizeNotes($notes),
        ];
        return $this->call('POST', '/customers', $payload);
    }

    public function fetchCustomer($customerId): array
    {
        if (!$customerId) {
            return ['success' => false, 'data' => null, 'error' => 'customerId required'];
        }
        return $this->call('GET', '/customers/' . urlencode($customerId));
    }

    /* ------------------------------------------------------------------ *
     *  PLANS & SUBSCRIPTIONS
     * ------------------------------------------------------------------ */

    public function createPlan($item, $period, $interval, $notes = []): array
    {
        if (!is_array($item) || !isset($item['name'], $item['amount'], $item['currency'])) {
            return ['success' => false, 'data' => null, 'error' => 'item must include name, amount, currency'];
        }
        if (!in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            return ['success' => false, 'data' => null, 'error' => 'period must be daily|weekly|monthly|yearly'];
        }
        $interval = max(1, (int)$interval);
        $payload = [
            'period'   => $period,
            'interval' => $interval,
            'item'     => [
                'name'     => substr($item['name'], 0, 100),
                'amount'   => (int)round(((float)$item['amount']) * 100),
                'currency' => strtoupper($item['currency']),
            ],
            'notes'    => $this->sanitizeNotes($notes),
        ];
        return $this->call('POST', '/plans', $payload, $payload['item']['amount']);
    }

    public function createSubscription($planId, $customerId, $startAt = null, $notes = []): array
    {
        if (!$planId || !$customerId) {
            return ['success' => false, 'data' => null, 'error' => 'planId and customerId required'];
        }
        $payload = [
            'plan_id'     => $planId,
            'customer_id' => $customerId,
            'notes'       => $this->sanitizeNotes($notes),
        ];
        if ($startAt !== null) {
            $payload['start_at'] = (int)$startAt;
        }
        return $this->call('POST', '/subscriptions', $payload);
    }

    public function cancelSubscription($subscriptionId, $cancelAtCycleEnd = false): array
    {
        if (!$subscriptionId) {
            return ['success' => false, 'data' => null, 'error' => 'subscriptionId required'];
        }
        $payload = $cancelAtCycleEnd ? ['cancel_at_cycle_end' => 1] : ['cancel_at_cycle_end' => 0];
        return $this->call('POST', '/subscriptions/' . urlencode($subscriptionId) . '/cancel', $payload);
    }

    /* ------------------------------------------------------------------ *
     *  PAYMENT LINKS & QR CODES
     * ------------------------------------------------------------------ */

    public function createPaymentLink($amount, $description, $customer, $options = []): array
    {
        if (!is_numeric($amount) || (float)$amount <= 0) {
            return ['success' => false, 'data' => null, 'error' => 'amount must be positive'];
        }
        $payload = [
            'amount'      => (int)round(((float)$amount) * 100),
            'currency'    => strtoupper($options['currency'] ?? 'INR'),
            'description' => substr((string)$description, 0, 250),
            'customer'    => $customer,
        ];
        foreach (['expire_by', 'reference_id', 'callback_url', 'callback_method', 'reminder_enable', 'notes'] as $k) {
            if (isset($options[$k])) {
                $payload[$k] = $options[$k];
            }
        }
        return $this->call('POST', '/payment_links', $payload, $payload['amount']);
    }

    public function createQrCode($amount, $description, $options = []): array
    {
        if (!is_numeric($amount) || (float)$amount <= 0) {
            return ['success' => false, 'data' => null, 'error' => 'amount must be positive'];
        }
        $payload = [
            'type'        => 'upi_qr',
            'name'        => substr((string)($options['name'] ?? 'APS Dream Home'), 0, 50),
            'usage'       => $options['usage'] ?? 'single_use',
            'fixed_amount'=> 1,
            'payment_amount' => (int)round(((float)$amount) * 100),
            'description' => substr((string)$description, 0, 250),
            'customer'    => $options['customer'] ?? null,
            'notes'       => $this->sanitizeNotes($options['notes'] ?? []),
        ];
        return $this->call('POST', '/qr_codes', $payload, $payload['payment_amount']);
    }

    /* ------------------------------------------------------------------ *
     *  UPI / PAYOUTS / VALIDATION
     * ------------------------------------------------------------------ */

    public function validateVpa($vpa): bool
    {
        $vpa = trim((string)$vpa);
        if ($vpa === '' || strlen($vpa) > 100) {
            return false;
        }
        return (bool)preg_match('/^[a-zA-Z0-9._-]{3,50}@[a-zA-Z]{2,20}$/', $vpa);
    }

    public function transferToBankAccount($account, $amount, $currency = 'INR'): array
    {
        if (!is_array($account) || !isset($account['account_number'], $account['ifsc'])) {
            return ['success' => false, 'data' => null, 'error' => 'account requires account_number + ifsc'];
        }
        if (!is_numeric($amount) || (float)$amount <= 0) {
            return ['success' => false, 'data' => null, 'error' => 'amount must be positive'];
        }
        $payload = [
            'account_number' => preg_replace('/[^0-9]/', '', $account['account_number']),
            'ifsc'           => strtoupper(preg_replace('/[^A-Z0-9]/', '', $account['ifsc'])),
            'amount'         => (int)round(((float)$amount) * 100),
            'currency'       => strtoupper($currency),
            'mode'           => $account['mode'] ?? 'IMPS',
            'purpose'        => $account['purpose'] ?? 'payout',
        ];
        return $this->call('POST', '/payouts', $payload, $payload['amount']);
    }

    /* ------------------------------------------------------------------ *
     *  CORE HTTP CALL — retry on 5xx, no retry on 4xx
     * ------------------------------------------------------------------ */

    private function call(string $method, string $path, array $data = [], ?int $amountPaise = null, ?string $txnId = null): array
    {
        $method = strtoupper($method);
        $url = self::API_BASE . $path;
        $start = microtime(true);
        $attempt = 0;
        $lastError = null;
        $lastResp = null;
        $lastCode = 0;

        while ($attempt < self::MAX_RETRIES) {
            $attempt++;
            $resp = $this->executeRequest($method, $url, $data);
            $code = (int)($resp['code'] ?? 0);
            $lastResp = $resp['body'];
            $lastCode = $code;
            if ($resp['error']) {
                $lastError = $resp['error'];
                if ($attempt >= self::MAX_RETRIES) break;
                usleep(200000 * $attempt);
                continue;
            }
            if ($code >= 500 && $code < 600) {
                $lastError = "Server error $code";
                if ($attempt >= self::MAX_RETRIES) break;
                usleep(200000 * $attempt);
                continue;
            }
            $duration = (int)round((microtime(true) - $start) * 1000);
            $this->logCall($method, $path, $data, $resp['body'], $code, $duration, $attempt, $amountPaise, $txnId, null);
            if ($code >= 200 && $code < 300) {
                return ['success' => true, 'data' => $resp['body'], 'error' => null, 'status' => $code];
            }
            $err = $resp['body']['error'] ?? null;
            $msg = is_array($err) ? ($err['description'] ?? $err['code'] ?? 'API error') : (string)($err ?? 'API error');
            return ['success' => false, 'data' => $resp['body'], 'error' => $msg, 'status' => $code];
        }
        $duration = (int)round((microtime(true) - $start) * 1000);
        $this->logCall($method, $path, $data, $lastResp, $lastCode, $duration, $attempt, $amountPaise, $txnId, $lastError);
        return ['success' => false, 'data' => $lastResp, 'error' => $lastError ?? 'Unknown error', 'status' => $lastCode];
    }

    protected function executeRequest(string $method, string $url, array $data = []): array
    {
        if ($this->testMode) {
            return $this->mockResponse($method, $url, $data);
        }
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERPWD        => $this->keyId . ':' . $this->keySecret,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json', 'User-Agent: APS-DreamHome/' . self::VERSION],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $decoded = $body !== '' ? json_decode($body, true) : null;
        return ['code' => $code, 'body' => is_array($decoded) ? $decoded : ['raw' => $body], 'error' => $err ?: null];
    }

    private function mockResponse(string $method, string $url, array $data): array
    {
        $code = 200;
        $body = [];
        $rand = function ($prefix, $len = 14) { return $prefix . bin2hex(random_bytes((int)ceil($len / 2))); };

        if (str_contains($url, '/orders') && $method === 'POST') {
            $body = [
                'id'              => $rand('order_'),
                'entity'          => 'order',
                'amount'          => $data['amount'] ?? 0,
                'amount_paid'     => 0,
                'amount_due'      => $data['amount'] ?? 0,
                'currency'        => $data['currency'] ?? 'INR',
                'receipt'         => $data['receipt'] ?? null,
                'status'          => 'created',
                'attempts'        => 0,
                'notes'           => $data['notes'] ?? [],
                'created_at'      => time(),
            ];
        } elseif (preg_match('#/orders/([^/]+)$#', $url, $m) && $method === 'GET') {
            $body = ['id' => $m[1], 'entity' => 'order', 'amount' => $data['amount'] ?? 10000, 'amount_paid' => 0, 'amount_due' => $data['amount'] ?? 10000, 'currency' => 'INR', 'status' => 'created', 'created_at' => time()];
        } elseif (str_contains($url, '/payments/') && str_contains($url, '/capture')) {
            $body = ['id' => $rand('pay_'), 'entity' => 'payment', 'amount' => $data['amount'] ?? 0, 'currency' => 'INR', 'status' => 'captured'];
        } elseif (preg_match('#/payments/([^/]+)$#', $url, $m) && $method === 'GET') {
            $body = ['id' => $m[1], 'entity' => 'payment', 'amount' => $data['amount'] ?? 10000, 'currency' => 'INR', 'status' => 'authorized', 'method' => 'card', 'email' => 'test@example.com', 'contact' => '+919999999999'];
        } elseif (str_contains($url, '/refund')) {
            $body = ['id' => $rand('rfnd_'), 'entity' => 'refund', 'amount' => $data['amount'] ?? 10000, 'currency' => 'INR', 'payment_id' => 'pay_mock', 'status' => 'processed', 'speed_requested' => $data['speed'] ?? 'normal', 'created_at' => time()];
        } elseif (preg_match('#/refunds/([^/]+)$#', $url, $m)) {
            $body = ['id' => $m[1], 'entity' => 'refund', 'amount' => 10000, 'currency' => 'INR', 'status' => 'processed'];
        } elseif (str_ends_with($url, '/customers') && $method === 'POST') {
            $body = ['id' => $rand('cust_'), 'entity' => 'customer', 'name' => $data['name'] ?? '', 'email' => $data['email'] ?? '', 'contact' => $data['contact'] ?? ''];
        } elseif (preg_match('#/customers/([^/]+)$#', $url, $m)) {
            $body = ['id' => $m[1], 'entity' => 'customer', 'name' => 'Test', 'email' => 'test@example.com'];
        } elseif (str_ends_with($url, '/plans') && $method === 'POST') {
            $body = ['id' => $rand('plan_'), 'entity' => 'plan', 'period' => $data['period'], 'interval' => $data['interval'], 'item' => $data['item'], 'notes' => $data['notes'] ?? []];
        } elseif (str_ends_with($url, '/subscriptions') && $method === 'POST') {
            $body = ['id' => $rand('sub_'), 'entity' => 'subscription', 'plan_id' => $data['plan_id'], 'customer_id' => $data['customer_id'], 'status' => 'created', 'current_start' => time(), 'current_end' => time() + 30 * 86400];
        } elseif (str_contains($url, '/subscriptions/') && str_contains($url, '/cancel')) {
            $body = ['id' => 'sub_mock', 'entity' => 'subscription', 'status' => 'cancelled', 'cancel_at_cycle_end' => $data['cancel_at_cycle_end'] ?? 0];
        } elseif (str_ends_with($url, '/payment_links') && $method === 'POST') {
            $body = ['id' => $rand('plink_'), 'entity' => 'payment_link', 'amount' => $data['amount'], 'currency' => $data['currency'], 'status' => 'created', 'short_url' => 'https://rzp.io/i/' . substr(bin2hex(random_bytes(5)), 0, 9)];
        } elseif (str_ends_with($url, '/qr_codes') && $method === 'POST') {
            $body = ['id' => $rand('qr_'), 'entity' => 'qr_code', 'image_url' => 'https://rzp.io/i/' . substr(bin2hex(random_bytes(5)), 0, 9) . '.png', 'payment_amount' => $data['payment_amount'], 'status' => 'active'];
        } elseif (str_ends_with($url, '/payouts')) {
            $body = ['id' => $rand('pout_'), 'entity' => 'payout', 'amount' => $data['amount'], 'currency' => $data['currency'], 'status' => 'processing', 'mode' => $data['mode']];
        } else {
            $code = 404;
            $body = ['error' => ['code' => 'NOT_FOUND', 'description' => 'Mocked route not implemented: ' . $url]];
        }
        return ['code' => $code, 'body' => $body, 'error' => null];
    }

    /* ------------------------------------------------------------------ *
     *  LOGGING — PCI-aware: amount + status + txn, no card data
     * ------------------------------------------------------------------ */

    private function logCall(string $method, string $path, array $req, $resp, int $code, int $duration, int $attempt, ?int $amountPaise, ?string $txnId, ?string $err): void
    {
        $reqSafe = $this->redact($req);
        $respSafe = is_array($resp) ? $this->redact($resp) : ['raw' => 'non-array'];
        $status = $code >= 200 && $code < 300 ? 'success' : 'failed';
        try {
            $stmt = $this->db?->prepare("INSERT INTO gateway_logs
                (gateway, method, endpoint, request_payload, response_payload, response_code, status, amount_paise, transaction_id, duration_ms, retry_count, error_message)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt?->execute([
                'razorpay',
                $method,
                $path,
                json_encode($reqSafe),
                json_encode($respSafe),
                $code,
                $status,
                $amountPaise,
                $txnId,
                $duration,
                max(0, $attempt - 1),
                $err,
            ]);
        } catch (\Throwable $e) {
            $this->info("logCall: " . $e->getMessage());
        }
        if ($this->logger) {
            try { ($this->logger)($method, $path, $status, $code, $duration); } catch (\Throwable $e) {}
        }
    }

    private function redact(array $data): array
    {
        $blocked = ['card', 'card_number', 'cvv', 'expiry', 'name_on_card', 'number', 'token'];
        $clean = [];
        foreach ($data as $k => $v) {
            $kl = strtolower((string)$k);
            $isBlocked = false;
            foreach ($blocked as $b) {
                if (str_contains($kl, $b)) { $isBlocked = true; break; }
            }
            if ($isBlocked) {
                $clean[$k] = '[REDACTED]';
            } elseif (is_array($v)) {
                $clean[$k] = $this->redact($v);
            } else {
                $clean[$k] = $v;
            }
        }
        return $clean;
    }

    private function sanitizeNotes(array $notes): array
    {
        $clean = [];
        foreach ($notes as $k => $v) {
            $kl = preg_replace('/[^a-zA-Z0-9_]/', '_', (string)$k);
            if (strlen($kl) > 30) $kl = substr($kl, 0, 30);
            if (is_scalar($v)) {
                $clean[$kl] = is_string($v) ? substr($v, 0, 200) : $v;
            }
        }
        return $clean;
    }

    private function info(string $msg): void
    {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log('[RazorpayService] ' . $msg);
        }
    }
}
