<?php

namespace App\Services\Gateway;

use App\Core\Database\Database;
use \App\Traits\ServiceTenantTrait;

/**
 * APS Dream Home - Unified Communication Gateway
 *
 * Single facade for ALL communication channels:
 *   - Email       (SMTP/PHPMailer via EmailSenderService)
 *   - SMS         (Twilio preferred, MSG91 fallback)
 *   - WhatsApp    (Twilio, Cloud API, or Web gateway)
 *   - Push        (Web Push via PushSender, FCM via PushNotificationService)
 *   - In-App      (MessagingService)
 *
 * Goals:
 *   1. ONE entry point for "send X" regardless of provider.
 *   2. Auto-failover (Twilio -> MSG91 for SMS, etc.).
 *   3. Unified logging to `gateway_logs` table.
 *   4. Per-channel enabled/disabled config.
 *   5. NEVER throws. Every call returns an envelope {success, data, error}.
 *
 * All existing high-level services (NotificationService, SmsSenderService,
 * EmailSenderService, PushNotificationService, etc.) can keep their public
 * API and delegate to this gateway in their send methods.
 */
class CommunicationGateway
{
    use ServiceTenantTrait;

    /** @var string */
    protected $configPath;

    /** @var array */
    protected $config;

    /** @var \PDO|null */
    protected $pdo;

    /** @var array stats since process start */
    protected $stats = [
        'calls' => 0, 'successes' => 0, 'failures' => 0,
        'by_channel' => ['email' => 0, 'sms' => 0, 'whatsapp' => 0, 'push' => 0, 'in_app' => 0],
    ];

    public function __construct()
    {
        $this->pdo = $this->resolvePdo();
        $this->loadConfig();
    }

    /* ------------------------------------------------------------------ */
    /*  Config / DI helpers                                                */
    /* ------------------------------------------------------------------ */

    /**
     * Load channel config. Each channel is enabled/disabled individually
     * with a driver and optional fallback list.
     */
    public function loadConfig()
    {
        $this->configPath = defined('APP_ROOT') ? APP_ROOT . '/config/communication.php' : __DIR__ . '/../../../config/communication.php';
        $defaults = [
            'default_channels' => ['email', 'sms', 'whatsapp', 'push', 'in_app'],
            'email' => [
                'enabled' => true,
                'driver'  => 'smtp',
                'from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? 'notifications@apsdreamhome.com',
                'from_name'  => $_ENV['SMTP_FROM_NAME']  ?? 'APS Dream Home',
            ],
            'sms' => [
                'enabled'  => true,
                'driver'   => 'twilio',         // primary
                'fallback' => 'msg91',          // automatic failover
            ],
            'whatsapp' => [
                'enabled' => true,
                'driver'  => 'twilio',
                'fallback' => 'cloud_api',
            ],
            'push' => [
                'enabled' => true,
                'driver'  => 'webpush',         // VAPID via PushSender
                'fallback' => 'fcm',            // PushNotificationService
            ],
            'in_app' => [
                'enabled' => true,
                'driver'  => 'messaging',
            ],
            'retry' => [
                'attempts' => 3,
                'backoff'  => 'exponential',  // or 'linear'
                'base_ms'  => 200,
            ],
        ];
        if (file_exists($this->configPath)) {
            $loaded = @include $this->configPath;
            if (is_array($loaded)) {
                $defaults = array_replace_recursive($defaults, $loaded);
            }
        }
        $this->config = $defaults;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function isChannelEnabled($channel)
    {
        return !empty($this->config[$channel]['enabled']);
    }

    /**
     * Allow runtime override (e.g. from admin panel).
     */
    public function setChannelEnabled($channel, $enabled)
    {
        if (!isset($this->config[$channel])) return false;
        $this->config[$channel]['enabled'] = (bool)$enabled;
        return true;
    }

    public function resolvePdo()
    {
        try {
            $db = Database::getInstance();
            return method_exists($db, 'getPdo') ? $db->getPdo() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Public API                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Send email via the configured email driver.
     * Delegates to EmailSenderService for SMTP/PHPMailer, then logs.
     *
     * @param string|array $to      Email address OR ['email' => ..., 'name' => ...]
     * @param string       $subject
     * @param string       $body    HTML body
     * @param array        $options attachments, cc, bcc, isHtml, etc.
     */
    public function sendEmail($to, $subject, $body, array $options = [])
    {
        $start = microtime(true);
        $this->stats['calls']++;
        $this->stats['by_channel']['email']++;

        if (!$this->isChannelEnabled('email')) {
            return $this->fail('email', 'email_channel_disabled', $to, $start);
        }

        try {
            // Use lazy reflection to avoid hard dependency on PHPMailer / EmailSenderService
            $service = $this->safeMake(\App\Services\Communication\EmailSenderService::class);
            if ($service === null) {
                $service = $this->safeMake(\App\Services\Communication\EmailService::class);
            }
            if ($service === null) {
                return $this->fail('email', 'no_email_service_available', $to, $start);
            }

            $isHtml = $options['isHtml'] ?? true;
            if (is_array($to)) {
                $email = $to['email'] ?? '';
                $name  = $to['name']  ?? '';
            } else {
                $email = (string)$to;
                $name  = '';
            }

            // EmailSenderService::send signature: ($to, $subject, $body, $isHtml, $fromEmail, $fromName)
            $args = [$email, $subject, $body, $isHtml,
                     $options['from']      ?? $this->config['email']['from_email'],
                     $options['from_name'] ?? $this->config['email']['from_name']];

            $ok = false;
            if (method_exists($service, 'send')) {
                $result = $service->send(...$args);
                $ok = $result === true || $result === null || (is_array($result) && ($result['success'] ?? false));
            } elseif (method_exists($service, 'sendEmail')) {
                $result = $service->sendEmail(...$args);
                $ok = $result === true || (is_array($result) && ($result['success'] ?? false));
            } else {
                return $this->fail('email', 'no_send_method', $to, $start);
            }

            if ($ok) {
                $this->stats['successes']++;
                $this->logCall('email', 'send', ['to' => $email, 'subject' => $subject], 'success', null, $start);
                return ['success' => true, 'data' => ['to' => $email, 'subject' => $subject], 'error' => null];
            }
            $this->stats['failures']++;
            $this->logCall('email', 'send', ['to' => $email, 'subject' => $subject], 'failed', 'service returned false', $start);
            return ['success' => false, 'data' => null, 'error' => 'Email send returned false'];
        } catch (\Throwable $e) {
            $this->stats['failures']++;
            $this->logCall('email', 'send', ['to' => is_array($to) ? ($to['email'] ?? '') : (string)$to], 'error', $e->getMessage(), $start);
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send SMS via TwilioService (primary) with MSG91 fallback on failure.
     */
    public function sendSms($to, $body, array $options = [])
    {
        $start = microtime(true);
        $this->stats['calls']++;
        $this->stats['by_channel']['sms']++;

        if (!$this->isChannelEnabled('sms')) {
            return $this->fail('sms', 'sms_channel_disabled', $to, $start);
        }

        $primary = $this->config['sms']['driver']   ?? 'twilio';
        $fallback = $this->config['sms']['fallback'] ?? 'msg91';

        $result = $this->dispatchSms($primary, $to, $body, $options);

        if (!$result['success'] && $fallback && $fallback !== $primary) {
            $result = $this->dispatchSms($fallback, $to, $body, $options);
            $result['used_fallback'] = true;
        }

        if ($result['success']) {
            $this->stats['successes']++;
        } else {
            $this->stats['failures']++;
        }
        $this->logCall('sms', $result['driver'] ?? $primary, ['to' => $to, 'body_preview' => mb_substr($body, 0, 100)], $result['success'] ? 'success' : 'error', $result['error'] ?? null, $start, $result);
        return $result;
    }

    /**
     * Send WhatsApp via TwilioService (preferred) with Cloud API fallback.
     */
    public function sendWhatsApp($to, $body, array $options = [])
    {
        $start = microtime(true);
        $this->stats['calls']++;
        $this->stats['by_channel']['whatsapp']++;

        if (!$this->isChannelEnabled('whatsapp')) {
            return $this->fail('whatsapp', 'whatsapp_channel_disabled', $to, $start);
        }

        $primary  = $this->config['whatsapp']['driver']   ?? 'twilio';
        $fallback = $this->config['whatsapp']['fallback'] ?? 'cloud_api';

        $result = $this->dispatchWhatsApp($primary, $to, $body, $options);

        if (!$result['success'] && $fallback && $fallback !== $primary) {
            $result = $this->dispatchWhatsApp($fallback, $to, $body, $options);
            $result['used_fallback'] = true;
        }

        if ($result['success']) {
            $this->stats['successes']++;
        } else {
            $this->stats['failures']++;
        }
        $this->logCall('whatsapp', $result['driver'] ?? $primary, ['to' => $to, 'body_preview' => mb_substr($body, 0, 100)], $result['success'] ? 'success' : 'error', $result['error'] ?? null, $start, $result);
        return $result;
    }

    /**
     * Send a WhatsApp template (ContentSid + variables).
     */
    public function sendWhatsAppTemplate($to, $template, array $vars = [], $lang = 'en')
    {
        $start = microtime(true);
        $this->stats['calls']++;
        $this->stats['by_channel']['whatsapp']++;

        if (!$this->isChannelEnabled('whatsapp')) {
            return $this->fail('whatsapp', 'whatsapp_channel_disabled', $to, $start);
        }

        try {
            $twilio = $this->safeMake(\App\Services\Gateway\TwilioService::class);
            if ($twilio && method_exists($twilio, 'sendWhatsAppTemplate')) {
                $resp = $twilio->sendWhatsAppTemplate($to, $template, $vars, $lang);
                $this->stats[$resp['success'] ? 'successes' : 'failures']++;
                $this->logCall('whatsapp', 'template', ['to' => $to, 'template' => $template], $resp['success'] ? 'success' : 'error', $resp['error'] ?? null, $start, $resp);
                return [
                    'success' => (bool)($resp['success'] ?? false),
                    'data'    => $resp,
                    'error'   => $resp['error'] ?? null,
                ];
            }
            return $this->fail('whatsapp', 'no_twilio_service', $to, $start);
        } catch (\Throwable $e) {
            $this->stats['failures']++;
            $this->logCall('whatsapp', 'template', ['to' => $to, 'template' => $template], 'error', $e->getMessage(), $start);
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a push notification via PushSender (Web Push) with FCM fallback.
     */
    public function sendPush($userId, $title, $body, array $options = [])
    {
        $start = microtime(true);
        $this->stats['calls']++;
        $this->stats['by_channel']['push']++;

        if (!$this->isChannelEnabled('push')) {
            return $this->fail('push', 'push_channel_disabled', $userId, $start);
        }

        $primary  = $this->config['push']['driver']   ?? 'webpush';
        $fallback = $this->config['push']['fallback'] ?? 'fcm';

        $result = $this->dispatchPush($primary, $userId, $title, $body, $options);

        if (!$result['success'] && $fallback && $fallback !== $primary) {
            $result = $this->dispatchPush($fallback, $userId, $title, $body, $options);
            $result['used_fallback'] = true;
        }

        if ($result['success']) {
            $this->stats['successes']++;
        } else {
            $this->stats['failures']++;
        }
        $this->logCall('push', $result['driver'] ?? $primary, ['user_id' => $userId, 'title' => $title], $result['success'] ? 'success' : 'error', $result['error'] ?? null, $start, $result);
        return $result;
    }

    /**
     * Send an in-app message via MessagingService.
     */
    public function sendInApp($senderId, $receiverId, $message, array $options = [])
    {
        $start = microtime(true);
        $this->stats['calls']++;
        $this->stats['by_channel']['in_app']++;

        if (!$this->isChannelEnabled('in_app')) {
            return $this->fail('in_app', 'in_app_channel_disabled', $receiverId, $start);
        }

        try {
            $service = $this->safeMake(\App\Services\Communication\MessagingService::class);
            if ($service === null || !method_exists($service, 'sendMessage')) {
                return $this->fail('in_app', 'messaging_service_unavailable', $receiverId, $start);
            }
            $data = array_merge([
                'message' => $message,
                'type'    => $options['type']    ?? 'text',
                'meta'    => $options['meta']    ?? [],
            ], $options);
            $result = $service->sendMessage((int)$senderId, (int)$receiverId, $data);
            $ok = is_array($result) ? ($result['success'] ?? false) : (bool)$result;
            if ($ok) {
                $this->stats['successes']++;
                $this->logCall('in_app', 'send', ['sender' => $senderId, 'receiver' => $receiverId], 'success', null, $start);
            } else {
                $this->stats['failures']++;
                $this->logCall('in_app', 'send', ['sender' => $senderId, 'receiver' => $receiverId], 'failed', 'returned false', $start);
            }
            return ['success' => $ok, 'data' => $result, 'error' => $ok ? null : 'sendMessage returned false'];
        } catch (\Throwable $e) {
            $this->stats['failures']++;
            $this->logCall('in_app', 'send', ['sender' => $senderId, 'receiver' => $receiverId], 'error', $e->getMessage(), $start);
            return ['success' => false, 'data' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Multi-channel fan-out: send the same message via multiple channels.
     * Used by NotificationService for user-facing alerts.
     *
     * @param array $channels  e.g. ['email', 'sms', 'push']
     */
    public function sendMulti($user, array $channels, $subject, $body, array $options = [])
    {
        $results = [];
        foreach ($channels as $ch) {
            $results[$ch] = match ($ch) {
                'email'    => $this->sendEmail($user['email']    ?? '', $subject, $body, $options),
                'sms'      => $this->sendSms  ($user['phone']    ?? '', $body, $options),
                'whatsapp' => $this->sendWhatsApp($user['phone'] ?? '', $body, $options),
                'push'     => $this->sendPush ($user['id']       ?? null, $subject, $body, $options),
                'in_app'   => isset($user['id']) && isset($options['sender_id'])
                                ? $this->sendInApp($options['sender_id'], $user['id'], $body, $options)
                                : ['success' => false, 'error' => 'in_app requires sender_id + receiver_id'],
                default    => ['success' => false, 'error' => "unknown channel: $ch"],
            };
        }
        return $results;
    }

    /* ------------------------------------------------------------------ */
    /*  Internals — driver dispatchers                                     */
    /* ------------------------------------------------------------------ */

    protected function dispatchSms($driver, $to, $body, array $options)
    {
        try {
            if ($driver === 'twilio') {
                $twilio = $this->safeMake(\App\Services\Gateway\TwilioService::class);
                if ($twilio && method_exists($twilio, 'sendSms')) {
                    $resp = $twilio->sendSms($to, $body, $options);
                    return [
                        'success' => (bool)($resp['success'] ?? false),
                        'data'    => $resp,
                        'error'   => $resp['error'] ?? null,
                        'driver'  => 'twilio',
                    ];
                }
            }
            if ($driver === 'msg91' || $driver === 'log') {
                $service = $this->safeMake(\App\Services\Communication\SMSService::class)
                        ?? $this->safeMake(\App\Services\Communication\SmsService::class)
                        ?? $this->safeMake(\App\Services\Communication\SmsSenderService::class);
                if ($service && method_exists($service, 'send')) {
                    $resp = $service->send($to, $body);
                    $ok = is_array($resp) ? ($resp['success'] ?? ($resp['status'] ?? 'sent') === 'sent') : (bool)$resp;
                    return [
                        'success' => (bool)$ok,
                        'data'    => is_array($resp) ? $resp : ['raw' => $resp],
                        'error'   => is_array($resp) ? ($resp['error'] ?? null) : null,
                        'driver'  => $driver,
                    ];
                }
            }
            return ['success' => false, 'error' => "sms driver '$driver' not available", 'driver' => $driver];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'driver' => $driver];
        }
    }

    protected function dispatchWhatsApp($driver, $to, $body, array $options)
    {
        try {
            if ($driver === 'twilio') {
                $twilio = $this->safeMake(\App\Services\Gateway\TwilioService::class);
                if ($twilio && method_exists($twilio, 'sendWhatsApp')) {
                    $resp = $twilio->sendWhatsApp($to, $body, $options);
                    return [
                        'success' => (bool)($resp['success'] ?? false),
                        'data'    => $resp,
                        'error'   => $resp['error'] ?? null,
                        'driver'  => 'twilio',
                    ];
                }
            }
            if ($driver === 'cloud_api' || $driver === 'cloud') {
                $service = $this->safeMake(\App\Services\Communication\WhatsAppSenderService::class)
                        ?? $this->safeMake(\App\Services\Communication\WhatsAppService::class)
                        ?? $this->safeMake(\App\Services\Communication\WhatsAppManagerService::class);
                if ($service && method_exists($service, 'send')) {
                    $resp = $service->send($to, $body, $options);
                    $ok = is_array($resp) ? ($resp['success'] ?? false) : (bool)$resp;
                    return [
                        'success' => (bool)$ok,
                        'data'    => is_array($resp) ? $resp : ['raw' => $resp],
                        'error'   => is_array($resp) ? ($resp['error'] ?? null) : null,
                        'driver'  => $driver,
                    ];
                }
            }
            if ($driver === 'web') {
                $service = $this->safeMake(\App\Services\Communication\WhatsAppWebService::class);
                if ($service && method_exists($service, 'sendMessage')) {
                    $resp = $service->sendMessage($to, $body, $options['media'] ?? null);
                    return [
                        'success' => (bool)($resp['success'] ?? false),
                        'data'    => $resp,
                        'error'   => $resp['error'] ?? ($resp['message'] ?? null),
                        'driver'  => 'web',
                    ];
                }
            }
            return ['success' => false, 'error' => "whatsapp driver '$driver' not available", 'driver' => $driver];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'driver' => $driver];
        }
    }

    protected function dispatchPush($driver, $userId, $title, $body, array $options)
    {
        try {
            if ($driver === 'webpush') {
                $service = $this->safeMake(\App\Services\Communication\PushSender::class);
                if ($service && method_exists($service, 'sendToUser')) {
                    $resp = $service->sendToUser($userId, $title, $body, $options);
                    return [
                        'success' => (bool)($resp['success'] ?? false),
                        'data'    => $resp,
                        'error'   => $resp['error'] ?? null,
                        'driver'  => 'webpush',
                    ];
                }
            }
            if ($driver === 'fcm') {
                $service = $this->safeMake(\App\Services\Communication\PushNotificationService::class);
                if ($service && method_exists($service, 'sendToUser')) {
                    $resp = $service->sendToUser($userId, $title, $body, $options);
                    $ok = is_array($resp) ? ($resp['success'] ?? false) : (bool)$resp;
                    return [
                        'success' => (bool)$ok,
                        'data'    => is_array($resp) ? $resp : ['raw' => $resp],
                        'error'   => is_array($resp) ? ($resp['error'] ?? null) : null,
                        'driver'  => 'fcm',
                    ];
                }
            }
            return ['success' => false, 'error' => "push driver '$driver' not available", 'driver' => $driver];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'driver' => $driver];
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Observability                                                      */
    /* ------------------------------------------------------------------ */

    /**
     * Get aggregated stats since process start.
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * Fetch recent logs from the gateway_logs table.
     */
    public function getRecentLogs($limit = 50, $channel = null)
    {
        if (!$this->pdo) return [];
        try {
            if ($channel) {
                $stmt = $this->pdo->prepare(
                    'SELECT * FROM gateway_logs WHERE gateway = ?' . $this->tenantSql() . ' ORDER BY id DESC LIMIT ' . (int)$limit
                );
                $params = [$channel];
                if ($this->tenantId() > 1) $params[] = $this->tenantId();
                $stmt->execute($params);
            } else {
                $stmt = $this->pdo->prepare(
                    'SELECT * FROM gateway_logs' . $this->tenantSql() . ' ORDER BY id DESC LIMIT ' . (int)$limit
                );
                $params = [];
                if ($this->tenantId() > 1) $params[] = $this->tenantId();
                $stmt->execute($params);
            }
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Aggregate stats per channel for admin dashboard cards.
     */
    public function getChannelStats($hours = 24)
    {
        if (!$this->pdo) return [];
        try {
            $sql = "SELECT gateway,
                           COUNT(*) AS total,
                           SUM(CASE WHEN status='success' THEN 1 ELSE 0 END) AS success_count,
                           SUM(CASE WHEN status='error' THEN 1 ELSE 0 END)   AS error_count,
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
     * Lazily instantiate a service class, returning null on any failure
     * (class not found, constructor fatal, etc.). Lets us support
     * configurations where some services are unavailable.
     */
    protected function safeMake($class)
    {
        if (!class_exists($class)) {
            // Try to autoload via the framework
            if (defined('APP_ROOT') && file_exists(APP_ROOT . '/app/Core/Autoloader.php')) {
                require_once APP_ROOT . '/app/Core/Autoloader.php';
            }
        }
        if (!class_exists($class)) {
            return null;
        }
        try {
            return new $class();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function fail($channel, $code, $recipient, $start)
    {
        $this->stats['failures']++;
        $this->logCall($channel, 'send', ['recipient' => $recipient], 'error', $code, $start);
        return ['success' => false, 'data' => null, 'error' => $code];
    }

    protected function logCall($gateway, $action, $request, $status, $error = null, $start = null, $extra = null)
    {
        $duration = $start ? (int)((microtime(true) - $start) * 1000) : 0;
        $recipient = is_scalar($request['to'] ?? $request['recipient'] ?? null) ? (string)($request['to'] ?? $request['recipient']) : null;

        if (!$this->pdo) {
            $this->logToFile(compact('gateway', 'action', 'recipient', 'request', 'status', 'error', 'duration', 'extra'));
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
                $recipient !== null ? mb_substr($recipient, 0, 100) : null,
                $this->safeJson($request),
                $this->safeJson($extra),
                $status,
                0,
                (int)$duration,
                0.0,
                $error !== null ? mb_substr((string)$error, 0, 1000) : null,
            ]);
        } catch (\Throwable $e) {
            $this->logToFile([
                'gateway' => $gateway, 'action' => $action, 'recipient' => $recipient,
                'request' => $request, 'status' => $status, 'error' => $error,
                'duration' => $duration, 'db_error' => $e->getMessage(),
            ]);
        }
    }

    protected function logToFile(array $entry)
    {
        $dir = defined('STORAGE_PATH') ? STORAGE_PATH . '/logs' : __DIR__ . '/../../../storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        $file = $dir . '/gateway_communication.log';
        @file_put_contents($file, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    protected function safeJson($v)
    {
        if ($v === null) return null;
        $j = is_string($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
        return mb_substr((string)$j, 0, 65535);
    }
}
