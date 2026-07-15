<?php
/**
 * Login & Registration Notification Service
 *
 * Sends multi-channel notifications (Email + SMS + Push + WhatsApp) on:
 *  1. Registration — Welcome message across all channels
 *  2. Login — Login alert with device/IP info
 *  3. Mobile login — Special welcome with in-app feel
 *
 * Uses existing infrastructure:
 *  - EmailService (PHPMailer SMTP)
 *  - SMSService (MSG91)
 *  - PushNotificationService (FCM v1)
 *  - WhatsAppIntegrationService (Meta/Twilio/Web)
 */

namespace App\Services\Communication;

use App\Core\Database\Database;
use App\Services\AI\AIGateway;

class LoginNotificationService
{
    private $db;
    private $emailService;
    private $smsService;
    private $pushService;
    private $whatsappService;
    private $gateway;

    public function __construct()
    {
        $this->db = Database::getInstance();

        try { $this->emailService = new EmailService(); } catch (\Throwable $e) { $this->emailService = null; }
        try { $this->smsService = new SMSService(); } catch (\Throwable $e) { $this->smsService = null; }
        try { $this->pushService = new PushNotificationService(); } catch (\Throwable $e) { $this->pushService = null; }
        try { $this->whatsappService = new WhatsAppIntegration(); } catch (\Throwable $e) { $this->whatsappService = null; }
        try { $this->gateway = AIGateway::getInstance(); } catch (\Throwable $e) { $this->gateway = null; }
    }

    // ─── Registration Welcome ───────────────────────────────

    /**
     * Send welcome notifications across all channels after registration.
     *
     * @param int    $userId
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param string $role       customer|associate|agent
     * @param bool   $isMobile   true if registered from mobile app
     */
    public function sendWelcomeNotifications(
        int $userId,
        string $name,
        string $email,
        string $phone,
        string $role = 'customer',
        bool $isMobile = false
    ): array {
        $results = [];

        // ── 1. Email: Welcome ──
        $results['email'] = $this->sendWelcomeEmail($userId, $name, $email, $role);

        // ── 2. SMS: Welcome ──
        $results['sms'] = $this->sendWelcomeSms($phone, $name, $role);

        // ── 3. Push: Welcome (mobile only) ──
        $results['push'] = $this->sendWelcomePush($userId, $name, $role, $isMobile);

        // ── 4. WhatsApp: Welcome ──
        $results['whatsapp'] = $this->sendWelcomeWhatsApp($phone, $name, $role);

        // ── Log everything ──
        $this->logNotificationBatch($userId, 'registration_welcome', $results);

        return $results;
    }

    // ─── Login Alert ────────────────────────────────────────

    /**
     * Send login alert notifications across all channels.
     *
     * @param int    $userId
     * @param string $role
     * @param string $ip         Client IP
     * @param string $userAgent  Browser/device UA
     * @param bool   $isMobile   true if login from mobile app
     * @param string $loginMethod email|phone|google|otp
     */
    public function sendLoginAlerts(
        int $userId,
        string $role,
        string $ip = '',
        string $userAgent = '',
        bool $isMobile = false,
        string $loginMethod = 'email'
    ): array {
        $results = [];

        // Fetch user details
        $user = $this->db->fetchOne("SELECT name, email, phone FROM users WHERE id = ?", [$userId]);
        if (!$user) return ['error' => 'User not found'];

        $deviceInfo = $this->parseDevice($userAgent);
        $location = $this->getIPLocation($ip);
        $time = date('d M Y, h:i A');

        // ── 1. Email: Login Alert ──
        $results['email'] = $this->sendLoginAlertEmail(
            $user['email'], $user['name'], $ip, $deviceInfo, $location, $time, $loginMethod
        );

        // ── 2. SMS: Login Alert (only for suspicious or new device) ──
        $isNewDevice = $this->isNewDevice($userId, $userAgent);
        if ($isNewDevice) {
            $results['sms'] = $this->sendLoginAlertSms($user['phone'], $user['name'], $ip, $deviceInfo, $time);
        }

        // ── 3. Push: Login Alert ──
        $results['push'] = $this->sendLoginAlertPush($userId, $ip, $deviceInfo, $location, $isMobile);

        // ── 4. WhatsApp: Login Alert (new device only) ──
        if ($isNewDevice) {
            $results['whatsapp'] = $this->sendLoginAlertWhatsApp(
                $user['phone'], $user['name'], $ip, $deviceInfo, $location, $time
            );
        }

        // ── Log everything ──
        $this->logNotificationBatch($userId, 'login_alert', $results);

        return $results;
    }

    // ─── Email Methods ──────────────────────────────────────

    private function sendWelcomeEmail(int $userId, string $name, string $email, string $role): array
    {
        if (!$this->emailService) return ['sent' => false, 'error' => 'Email service unavailable'];

        try {
            $subject = "Welcome to APS Dream Home, {$name}! 🏠";

            $dashboardUrl = match($role) {
                'associate' => BASE_URL . '/associate/dashboard',
                'agent'     => BASE_URL . '/agent/dashboard',
                default     => BASE_URL . '/user/dashboard',
            };

            $body = $this->renderEmailTemplate('welcome_enhanced', [
                'name'          => $name,
                'role'          => ucfirst($role),
                'dashboard_url' => $dashboardUrl,
                'login_url'     => BASE_URL . '/login',
                'properties_url'=> BASE_URL . '/properties',
                'support_email' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'support@apsdreamhome.com',
                'logo_url'      => BASE_URL . '/assets/images/logo.png',
            ]);

            $sent = $this->emailService->send($email, $subject, $body);
            return ['sent' => $sent, 'channel' => 'email'];
        } catch (\Throwable $e) {
            error_log("[LoginNotification] Welcome email failed: " . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendLoginAlertEmail(
        string $email, string $name, string $ip, string $device,
        string $location, string $time, string $method
    ): array {
        if (!$this->emailService) return ['sent' => false, 'error' => 'Email service unavailable'];

        try {
            $subject = "New Login to Your APS Dream Home Account";

            $body = $this->renderEmailTemplate('login_alert', [
                'name'       => $name,
                'ip'         => $ip,
                'device'     => $device,
                'location'   => $location ?: 'Unknown',
                'time'       => $time,
                'method'     => ucfirst($method),
                'support_url'=> BASE_URL . '/support',
                'logo_url'   => BASE_URL . '/assets/images/logo.png',
            ]);

            $sent = $this->emailService->send($email, $subject, $body);
            return ['sent' => $sent, 'channel' => 'email'];
        } catch (\Throwable $e) {
            error_log("[LoginNotification] Login alert email failed: " . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── SMS Methods ────────────────────────────────────────

    private function sendWelcomeSms(string $phone, string $name, string $role): array
    {
        if (!$this->smsService || empty($phone)) return ['sent' => false, 'error' => 'SMS service unavailable or no phone'];

        try {
            $result = $this->smsService->sendWelcomeSMS($phone, $name, $role);
            return ['sent' => !empty($result['success']), 'channel' => 'sms'];
        } catch (\Throwable $e) {
            error_log("[LoginNotification] Welcome SMS failed: " . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendLoginAlertSms(string $phone, string $name, string $ip, string $device, string $time): array
    {
        if (!$this->smsService || empty($phone)) return ['sent' => false, 'error' => 'SMS service unavailable'];

        try {
            $result = $this->smsService->sendLoginAlertSMS($phone, $name, $ip, $device, $time);
            return ['sent' => !empty($result['success']), 'channel' => 'sms'];
        } catch (\Throwable $e) {
            error_log("[LoginNotification] Login alert SMS failed: " . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── Push Methods ───────────────────────────────────────

    private function sendWelcomePush(int $userId, string $name, string $role, bool $isMobile): array
    {
        if (!$this->pushService) return ['sent' => false, 'error' => 'Push service unavailable'];

        try {
            $dashboardUrl = match($role) {
                'associate' => '/associate/dashboard',
                'agent'     => '/agent/dashboard',
                default     => '/user/dashboard',
            };

            $notification = [
                'title' => 'Welcome to APS Dream Home! 🏠',
                'body'  => "Hi {$name}! Your account is ready. Explore properties, track your bookings, and more.",
                'data'  => [
                    'type'              => 'welcome',
                    'action_url'        => $dashboardUrl,
                    'is_mobile_login'   => $isMobile ? '1' : '0',
                    'notification_type' => 'registration_welcome',
                ],
                'sound' => 'default',
                'icon'  => 'ic_notification',
            ];

            $result = $this->pushService->sendToUser($userId, $notification);
            return ['sent' => !empty($result['success']), 'channel' => 'push'];
        } catch (\Throwable $e) {
            error_log("[LoginNotification] Welcome push failed: " . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendLoginAlertPush(
        int $userId, string $ip, string $device, string $location, bool $isMobile
    ): array {
        if (!$this->pushService) return ['sent' => false, 'error' => 'Push service unavailable'];

        try {
            $title = "New Login Alert 🔐";
            $body = "Logged in from {$device}";
            if ($location) $body .= " ({$location})";
            $body .= ". If this wasn't you, secure your account now.";

            $notification = [
                'title' => $title,
                'body'  => $body,
                'data'  => [
                    'type'              => 'login_alert',
                    'action_url'        => '/notifications-center',
                    'ip'                => $ip,
                    'device'            => $device,
                    'location'          => $location,
                    'is_mobile_login'   => $isMobile ? '1' : '0',
                    'notification_type' => 'security_alert',
                ],
                'sound' => 'default',
                'icon'  => 'ic_notification',
            ];

            $result = $this->pushService->sendToUser($userId, $notification);
            return ['sent' => !empty($result['success']), 'channel' => 'push'];
        } catch (\Throwable $e) {
            error_log("[LoginNotification] Login alert push failed: " . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── WhatsApp Methods ───────────────────────────────────

    private function sendWelcomeWhatsApp(string $phone, string $name, string $role): array
    {
        if (!$this->whatsappService || empty($phone)) return ['sent' => false, 'error' => 'WhatsApp service unavailable'];

        try {
            $result = $this->whatsappService->sendWelcomeMessage($phone, $name);
            return ['sent' => !empty($result['success']), 'channel' => 'whatsapp'];
        } catch (\Throwable $e) {
            error_log("[LoginNotification] Welcome WhatsApp failed: " . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendLoginAlertWhatsApp(
        string $phone, string $name, string $ip, string $device, string $location, string $time
    ): array {
        if (!$this->whatsappService || empty($phone)) return ['sent' => false, 'error' => 'WhatsApp service unavailable'];

        try {
            $message = "🔐 APS Dream Home - New Login Alert\n\n";
            $message .= "Hi {$name}, a new login was detected:\n\n";
            $message .= "⏰ Time: {$time}\n";
            $message .= "📱 Device: {$device}\n";
            $message .= "🌐 IP: {$ip}\n";
            if ($location) $message .= "📍 Location: {$location}\n";
            $message .= "\n⚠️ If this wasn't you, please change your password immediately.";
            $message .= "\n\n📞 Support: " . BASE_URL . "/support";

            // AI-personalize the copy (warmer, name-first) when the local LLM is available
            $message = $this->aiPersonalize($message, $name, 'login_alert');

            $result = $this->whatsappService->sendMessage($phone, $message);
            return ['sent' => !empty($result['success']), 'channel' => 'whatsapp'];
        } catch (\Throwable $e) {
            error_log("[LoginNotification] Login alert WhatsApp failed: " . $e->getMessage());
            return ['sent' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── Email Template Rendering ───────────────────────────

    private function renderEmailTemplate(string $code, array $vars): string
    {
        // Try modern template service first
        try {
            $svc = new TemplateService();
            $result = $svc->renderHtmlTemplate($code, $vars);
            if ($result['ok'] ?? false) return $result['html'] ?? '';
        } catch (\Throwable $e) {}

        // Fallback: inline template
        return $this->getInlineTemplate($code, $vars);
    }

    private function getInlineTemplate(string $code, array $vars): string
    {
        $name = htmlspecialchars($vars['name'] ?? 'User');

        if ($code === 'welcome_enhanced') {
            $role = htmlspecialchars($vars['role'] ?? 'Customer');
            $dashboardUrl = $vars['dashboard_url'] ?? '#';
            $propertiesUrl = $vars['properties_url'] ?? '#';
            $supportEmail = $vars['support_email'] ?? 'support@apsdreamhome.com';
            $logoUrl = $vars['logo_url'] ?? '';

            return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:'Segoe UI',Arial,sans-serif;margin:0;padding:0;background:#f4f6f9;color:#333}
.container{max-width:600px;margin:0 auto;background:#fff}
.header{background:linear-gradient(135deg,#0d9488,#0f766e);padding:40px 30px;text-align:center}
.header h1{color:#fff;font-size:28px;margin:0}
.header p{color:rgba(255,255,255,0.85);font-size:14px;margin-top:8px}
.body{padding:30px}
.body h2{color:#0d9488;font-size:22px;margin-top:0}
.body p{line-height:1.7;color:#555;font-size:15px}
.btn{display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff;text-decoration:none;border-radius:8px;font-weight:600;margin:20px 0}
.features{background:#f8fafc;border-radius:12px;padding:20px;margin:20px 0}
.features li{padding:8px 0;color:#555;font-size:14px}
.footer{background:#f4f6f9;padding:20px 30px;text-align:center;font-size:12px;color:#999}
</style></head><body>
<div class="container">
<div class="header">
<h1>Welcome to APS Dream Home!</h1>
<p>Your {$role} account is now active</p>
</div>
<div class="body">
<h2>Hello {$name}! 🎉</h2>
<p>Congratulations! Your account has been created successfully. You're now part of the APS Dream Home family — trusted by thousands to find their perfect property.</p>
<a href="{$dashboardUrl}" class="btn">Go to Dashboard →</a>
<h3>What You Can Do:</h3>
<ul class="features">
<li>🏠 Browse verified properties across premium colonies</li>
<li>📊 Track your bookings and EMI schedule</li>
<li>💰 Earn commissions through our MLM program</li>
<li>📄 Access all legal documents and agreements</li>
<li>🔔 Get real-time notifications for updates</li>
</ul>
<p><a href="{$propertiesUrl}">Explore Properties →</a></p>
<p>Need help? Contact us at <a href="mailto:{$supportEmail}">{$supportEmail}</a></p>
</div>
<div class="footer">
<p>© {date('Y')} APS Dream Home. All rights reserved.</p>
<p>This email was sent to you because you registered at APS Dream Home.</p>
</div>
</div></body></html>
HTML;
        }

        if ($code === 'login_alert') {
            $ip = htmlspecialchars($vars['ip'] ?? '');
            $device = htmlspecialchars($vars['device'] ?? '');
            $location = htmlspecialchars($vars['location'] ?? 'Unknown');
            $time = htmlspecialchars($vars['time'] ?? '');
            $method = htmlspecialchars($vars['method'] ?? 'Email');
            $supportUrl = $vars['support_url'] ?? '#';
            $logoUrl = $vars['logo_url'] ?? '';

            return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
body{font-family:'Segoe UI',Arial,sans-serif;margin:0;padding:0;background:#f4f6f9;color:#333}
.container{max-width:600px;margin:0 auto;background:#fff}
.header{background:linear-gradient(135deg,#1e40af,#3b82f6);padding:40px 30px;text-align:center}
.header h1{color:#fff;font-size:24px;margin:0}
.body{padding:30px}
.body h2{color:#1e40af;font-size:20px;margin-top:0}
.body p{line-height:1.7;color:#555;font-size:15px}
.alert-box{background:#fef3c7;border-left:4px solid #f59e0b;padding:16px;border-radius:8px;margin:20px 0}
.alert-box strong{color:#92400e}
.details{background:#f8fafc;border-radius:12px;padding:20px;margin:20px 0}
.details table{width:100%;border-collapse:collapse}
.details td{padding:8px 12px;font-size:14px;color:#555;border-bottom:1px solid #e5e7eb}
.details td:first-child{font-weight:600;color:#333;width:120px}
.btn{display:inline-block;padding:14px 32px;background:#dc2626;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;margin:20px 0}
.footer{background:#f4f6f9;padding:20px 30px;text-align:center;font-size:12px;color:#999}
</style></head><body>
<div class="container">
<div class="header">
<h1>🔐 New Login Detected</h1>
</div>
<div class="body">
<h2>Hello {$name}</h2>
<p>We detected a new login to your APS Dream Home account.</p>
<div class="details">
<table>
<tr><td>⏰ Time</td><td>{$time}</td></tr>
<tr><td>📱 Device</td><td>{$device}</td></tr>
<tr><td>🌐 IP Address</td><td>{$ip}</td></tr>
<tr><td>📍 Location</td><td>{$location}</td></tr>
<tr><td>🔑 Method</td><td>{$method}</td></tr>
</table>
</div>
<div class="alert-box">
<strong>⚠️ Wasn't you?</strong><br>
If you don't recognize this login, please change your password immediately and contact our support team.
</div>
<a href="{$supportUrl}" class="btn">Report Unauthorized Login</a>
<p style="color:#999;font-size:13px">For your security, we monitor all login activity on your account.</p>
</div>
<div class="footer">
<p>© {date('Y')} APS Dream Home. All rights reserved.</p>
<p>This is a security notification for your account.</p>
</div>
</div></body></html>
HTML;
        }

        return '';
    }

    // ─── Helpers ────────────────────────────────────────────

    /**
     * AI-personalize a notification message using the local LLM (Ollama) when
     * available. Falls back to the original copy on any failure so delivery is
     * never blocked. Keeps all critical details (time, device, warning, link).
     */
    private function aiPersonalize(string $message, string $name, string $type): string
    {
        if (!$this->gateway) return $message;

        try {
            $prompt = "Rewrite this WhatsApp {$type} message to sound warmer and personal for customer '{$name}'. "
                . "Keep ALL details (time, device, IP, location, security warning, support link) intact. "
                . "Hindi+English mix, friendly tone, under 320 characters:\n\n" . $message;
            $res = $this->gateway->process('chat', ['message' => $prompt], ['user_role' => 'customer']);
            $out = trim($res['response'] ?? '');
            if (!empty($out) && mb_strlen($out) <= 400 && stripos($out, $name) !== false) {
                return $out;
            }
        } catch (\Throwable $e) {
            // keep original message
        }
        return $message;
    }

    private function parseDevice(string $ua): string
    {
        if (empty($ua)) return 'Unknown Device';

        if (preg_match('/Android/i', $ua)) {
            preg_match('/Android\s+([\d.]+)/i', $ua, $m);
            $ver = $m[1] ?? '';
            preg_match('/(?:;)\s*([^;]+)\s*Build/i', $ua, $model);
            $device = $model[1] ?? 'Android Device';
            return trim($device) . ($ver ? " (Android {$ver})" : '');
        }

        if (preg_match('/iPhone|iPad/i', $ua)) {
            preg_match('/OS\s+([\d_]+)/i', $ua, $m);
            $ver = str_replace('_', '.', $m[1] ?? '');
            return (preg_match('/iPad/i', $ua) ? 'iPad' : 'iPhone') . ($ver ? " (iOS {$ver})" : '');
        }

        if (preg_match('/Windows/i', $ua)) {
            preg_match('/Windows NT\s+([\d.]+)/i', $ua, $m);
            $ver = $m[1] ?? '';
            $os = match(true) {
                str_contains($ver, '10.0') => 'Windows 10/11',
                str_contains($ver, '6.3')  => 'Windows 8.1',
                str_contains($ver, '6.1')  => 'Windows 7',
                default => "Windows {$ver}",
            };
            preg_match('/(Chrome|Firefox|Edge|Safari|MSIE|Trident)\/([\d.]+)/i', $ua, $b);
            $browser = ($b[1] ?? 'Browser') . ' ' . ($b[2] ?? '');
            return "{$os} — {$browser}";
        }

        if (preg_match('/Mac/i', $ua)) {
            preg_match('/Mac OS X\s+([\d._]+)/i', $ua, $m);
            $ver = str_replace('_', '.', $m[1] ?? '');
            $macVer = $ver ? " {$ver}" : '';
            return "macOS{$macVer}";
        }

        if (preg_match('/Linux/i', $ua)) return 'Linux';

        // Fallback: extract first meaningful segment
        $parts = explode(' ', $ua);
        return $parts[0] ?? 'Unknown Device';
    }

    private function getIPLocation(string $ip): string
    {
        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1') return 'Localhost';

        try {
            // Free IP geolocation (no API key needed)
            $ctx = stream_context_create(['http' => ['timeout' => 2]]);
            $json = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city", false, $ctx);
            if ($json) {
                $data = json_decode($json, true);
                if ($data['status'] === 'success') {
                    $parts = array_filter([$data['city'] ?? '', $data['regionName'] ?? '', $data['country'] ?? '']);
                    return implode(', ', $parts) ?: '';
                }
            }
        } catch (\Throwable $e) {}

        return '';
    }

    private function isNewDevice(int $userId, string $ua): bool
    {
        if (empty($ua)) return false;

        try {
            $hash = md5($ua);
            $row = $this->db->fetchOne(
                "SELECT id FROM login_attempts WHERE identifier = (SELECT email FROM users WHERE id = ? LIMIT 1) AND user_agent LIKE ? AND success = 1 LIMIT 1",
                [$userId, '%' . substr($hash, 0, 8) . '%']
            );
            return empty($row); // new if no previous successful login with similar UA
        } catch (\Throwable $e) {
            return true; // assume new on error
        }
    }

    private function logNotificationBatch(int $userId, string $type, array $results): void
    {
        // Log each channel separately for per-channel stats on the dashboard
        foreach ($results as $channel => $result) {
            if (!is_array($result)) continue;
            try {
                $sent = $result['sent'] ?? false;
                $this->db->execute(
                    "INSERT INTO notification_logs (user_id, type, channel, recipient_token, title, body, payload, status, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $userId,
                        $type,
                        $channel,
                        "user_{$userId}",
                        ucfirst($type) . " — " . ucfirst($channel),
                        $sent ? "Delivered via {$channel}" : ($result['error'] ?? "Failed via {$channel}"),
                        json_encode($result),
                        $sent ? 'sent' : 'failed',
                    ]
                );
            } catch (\Throwable $e) {}
        }
    }
}
