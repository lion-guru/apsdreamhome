<?php

namespace App\Http\Controllers\Admin;

use App\Services\Gateway\TwilioService;
use App\Traits\TenantAwareTrait;

/**
 * Admin Gateway Manager
 *
 * Lets admins:
 *   - See configured status + last 5 calls + cost for every gateway
 *   - Send a test SMS / WhatsApp to their own phone
 *   - Drill into per-gateway logs
 *
 * Routes:
 *   GET  /admin/gateways                 -> index()
 *   POST /admin/gateways/test-twilio     -> testTwilio()
 *   POST /admin/gateways/test-whatsapp   -> testWhatsApp()
 *   GET  /admin/gateways/logs/{gateway}  -> logs()
 */
class GatewayTestController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    /**
     * Show the gateway dashboard.
     */
    public function index()
    {
        $this->requireAdmin();
        $twilio = new TwilioService();
        $stats  = $twilio->getGatewayStats(24);

        // Build a per-gateway summary that the view can render as cards
        $cards = [
            [
                'key'         => 'twilio_sms',
                'name'        => 'Twilio SMS',
                'icon'        => 'fa-comment-sms',
                'configured'  => (bool)$twilio->getFromNumber(),
                'detail'      => 'From: ' . ($twilio->getFromNumber() ?? 'not set'),
                'last_5'      => $this->lastN('send_sms', 5),
                'total'       => $this->findStat($stats, 'twilio'),
                'error_count' => $this->countErrors('twilio', 24),
                'can_test'    => true,
                'test_action' => 'test-twilio',
            ],
            [
                'key'         => 'twilio_whatsapp',
                'name'        => 'Twilio WhatsApp',
                'icon'        => 'fa-brands fa-whatsapp',
                'configured'  => $twilio->isWhatsAppConfigured(),
                'detail'      => 'From: whatsapp:' . ($twilio->getWhatsAppNumber() ?? 'not set'),
                'last_5'      => $this->lastN('send_whatsapp', 5),
                'total'       => $this->findStat($stats, 'twilio'),
                'error_count' => $this->countErrors('twilio', 24),
                'can_test'    => true,
                'test_action' => 'test-whatsapp',
            ],
            [
                'key'         => 'razorpay',
                'name'        => 'Razorpay',
                'icon'        => 'fa-credit-card',
                'configured'  => !empty($_ENV['RAZORPAY_KEY_ID']) && strpos($_ENV['RAZORPAY_KEY_ID'] ?? '', 'xxxxx') === false,
                'detail'      => 'Key: ' . $this->mask($_ENV['RAZORPAY_KEY_ID'] ?? ''),
                'last_5'      => $this->lastN(null, 5, 'razorpay'),
                'total'       => $this->findStat($stats, 'razorpay'),
                'error_count' => $this->countErrors('razorpay', 24),
                'can_test'    => false,
            ],
            [
                'key'         => 'stripe',
                'name'        => 'Stripe',
                'icon'        => 'fa-brands fa-stripe',
                'configured'  => !empty($_ENV['STRIPE_SECRET_KEY']) && strpos($_ENV['STRIPE_SECRET_KEY'] ?? '', 'xxxxx') === false,
                'detail'      => 'Key: ' . $this->mask($_ENV['STRIPE_SECRET_KEY'] ?? ''),
                'last_5'      => $this->lastN(null, 5, 'stripe'),
                'total'       => $this->findStat($stats, 'stripe'),
                'error_count' => $this->countErrors('stripe', 24),
                'can_test'    => false,
            ],
            [
                'key'         => 'phonepe',
                'name'        => 'PhonePe',
                'icon'        => 'fa-mobile-screen',
                'configured'  => !empty($_ENV['PHONEPE_MERCHANT_ID']) && ($_ENV['PHONEPE_MERCHANT_ID'] ?? '') !== 'MERCHANTUAT',
                'detail'      => 'Merchant: ' . ($_ENV['PHONEPE_MERCHANT_ID'] ?? 'not set'),
                'last_5'      => $this->lastN(null, 5, 'phonepe'),
                'total'       => $this->findStat($stats, 'phonepe'),
                'error_count' => $this->countErrors('phonepe', 24),
                'can_test'    => false,
            ],
            [
                'key'         => 'aws_s3',
                'name'        => 'AWS S3',
                'icon'        => 'fa-cloud',
                'configured'  => !empty($_ENV['AWS_ACCESS_KEY_ID']) && !empty($_ENV['AWS_SECRET_ACCESS_KEY']),
                'detail'      => 'Bucket: ' . ($_ENV['AWS_BUCKET'] ?? 'not set'),
                'last_5'      => [],
                'total'       => null,
                'error_count' => 0,
                'can_test'    => false,
            ],
        ];

        return $this->render('admin/gateways', [
            'page_title'   => 'Gateway Manager',
            'page_heading' => 'Gateway Manager',
            'cards'        => $cards,
            'stats'        => $stats,
            'admin_phone'  => $this->resolveAdminPhone(),
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    /**
     * Send a test SMS to the phone provided in the POST.
     */
    public function testTwilio()
    {
        $this->requireAdmin();
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid CSRF token.');
            $this->doRedirect('/admin/gateways');
        }
        $phone = trim((string)($_POST['phone'] ?? ''));
        $msg   = trim((string)($_POST['message'] ?? 'Hello from APS Dream Home Gateway Manager. This is a test SMS.'));

        if ($phone === '') {
            $this->setFlash('error', 'Phone number is required.');
            $this->doRedirect('/admin/gateways');
        }

        $twilio = new TwilioService();
        $result = $twilio->sendSms($phone, $msg);
        $this->setFlash(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? 'Test SMS sent. SID: ' . ($result['sid'] ?? 'n/a')
                : 'SMS failed: ' . ($result['error'] ?? 'unknown')
        );
        $this->doRedirect('/admin/gateways');
    }

    /**
     * Send a test WhatsApp to the phone provided in the POST.
     */
    public function testWhatsApp()
    {
        $this->requireAdmin();
        if (!$this->validateCsrf()) {
            $this->setFlash('error', 'Invalid CSRF token.');
            $this->doRedirect('/admin/gateways');
        }
        $phone = trim((string)($_POST['phone'] ?? ''));
        $msg   = trim((string)($_POST['message'] ?? 'Hello from APS Dream Home Gateway Manager. This is a test WhatsApp.'));

        if ($phone === '') {
            $this->setFlash('error', 'Phone number is required.');
            $this->doRedirect('/admin/gateways');
        }

        $twilio = new TwilioService();
        $result = $twilio->sendWhatsApp($phone, $msg);
        $this->setFlash(
            $result['success'] ? 'success' : 'error',
            $result['success']
                ? 'Test WhatsApp sent. SID: ' . ($result['sid'] ?? 'n/a')
                : 'WhatsApp failed: ' . ($result['error'] ?? 'unknown')
        );
        $this->doRedirect('/admin/gateways');
    }

    /**
     * Show recent log rows for a single gateway.
     */
    public function logs($gateway = null)
    {
        $this->requireAdmin();
        $gateway = $gateway ?: 'twilio';
        $gateway = preg_replace('/[^a-z0-9_]/', '', (string)$gateway);

        $twilio = new TwilioService();
        $logs   = $twilio->getRecentLogs(100, $gateway);

        return $this->render('admin/gateways', [
            'page_title'   => 'Gateway Logs: ' . $gateway,
            'page_heading' => 'Gateway Logs: ' . $gateway,
            'logs_only'    => true,
            'gateway'      => $gateway,
            'logs'         => $logs,
            'csrf_token'   => $this->csrfToken(),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  Internals                                                          */
    /* ------------------------------------------------------------------ */

    private function lastN($action, $n, $gateway = 'twilio')
    {
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getPdo();
            $sql = 'SELECT id, action, recipient, status, cost, duration_ms, http_code, created_at, error_message
                      FROM gateway_logs
                     WHERE gateway = ?';
            $params = [$gateway];
            if ($action !== null) {
                $sql .= ' AND action = ?';
                $params[] = $action;
            }
            $sql .= ' ORDER BY id DESC LIMIT ' . (int)$n;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function findStat(array $stats, $gateway)
    {
        foreach ($stats as $row) {
            if (($row['gateway'] ?? '') === $gateway) return $row;
        }
        return null;
    }

    private function countErrors($gateway, $hours)
    {
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getPdo();
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) FROM gateway_logs
                  WHERE gateway = ? AND status = 'error'
                    AND created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)"
            );
            $stmt->execute([$gateway, (int)$hours]);
            return (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function mask($key)
    {
        $key = (string)$key;
        if ($key === '') return 'not set';
        if (strlen($key) <= 8) return str_repeat('*', strlen($key));
        return substr($key, 0, 4) . str_repeat('*', max(4, strlen($key) - 8)) . substr($key, -4);
    }

    private function resolveAdminPhone()
    {
        try {
            $pdo = \App\Core\Database\Database::getInstance()->getPdo();
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $pdo->prepare("SELECT phone FROM users WHERE role IN ('admin','superadmin') AND phone IS NOT NULL AND phone != ''{$tidSql} ORDER BY id LIMIT 1");
            $stmt->execute($tidParams);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row['phone'] ?? '';
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function csrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf_token'];
    }

    private function validateCsrf()
    {
        if (session_status() === PHP_SESSION_NONE) @session_start();
        $token = $_POST['csrf_token'] ?? '';
        return !empty($_SESSION['csrf_token']) && hash_equals((string)$_SESSION['csrf_token'], (string)$token);
    }

    private function doRedirect($path)
    {
        $url = (defined('BASE_URL') ? BASE_URL : '') . $path;
        header('Location: ' . $url);
        exit;
    }
}
