<?php
namespace App\Services;

class PushNotificationService
{
    private $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        try {
            $this->db = new \PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]
            );
        } catch (\PDOException $e) {
            $this->db = null;
        }
    }

    public function subscribe(int $userId, string $endpoint, string $p256dh, string $auth): array
    {
        if (!$this->db) return ['success' => false, 'error' => 'Database unavailable'];
        try {
            $stmt = $this->db->prepare("SELECT id FROM push_subscriptions WHERE endpoint = ? LIMIT 1");
            $stmt->execute([$endpoint]);
            $existing = $stmt->fetch();

            if ($existing) {
                $stmt = $this->db->prepare("UPDATE push_subscriptions SET p256dh_key = ?, auth_key = ?, user_id = ?, is_active = 1, last_used_at = NOW() WHERE id = ?");
                $stmt->execute([$p256dh, $auth, $userId, $existing['id']]);
            } else {
                $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $stmt = $this->db->prepare("INSERT INTO push_subscriptions (user_id, endpoint, p256dh_key, auth_key, user_agent, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->execute([$userId, $endpoint, $p256dh, $auth, $ua]);
            }
            return ['success' => true];
        } catch (\PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function unsubscribe(string $endpoint): bool
    {
        if (!$this->db) return false;
        try {
            $stmt = $this->db->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
            $stmt->execute([$endpoint]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function getSubscriptions(?int $userId = null): array
    {
        if (!$this->db) return [];
        try {
            if ($userId) {
                $stmt = $this->db->prepare("SELECT * FROM push_subscriptions WHERE user_id = ? AND is_active = 1");
                $stmt->execute([$userId]);
            } else {
                $stmt = $this->db->query("SELECT * FROM push_subscriptions WHERE is_active = 1");
            }
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function send(int $userId, string $title, string $body, string $url = '/', array $data = []): array
    {
        if (!$this->db) return ['sent' => 0, 'failed' => 0];
        $subs = $this->getSubscriptions($userId);
        $sent = 0;
        $failed = 0;

        $payload = json_encode(array_merge(['title' => $title, 'body' => $body, 'url' => $url], $data));

        foreach ($subs as $sub) {
            $result = $this->sendWebPush($sub['endpoint'], $sub['p256dh_key'], $sub['auth_key'], $payload);
            if ($result['success']) {
                $sent++;
                $this->logEntry($userId, $title, $body, $url, 'sent');
            } else {
                $failed++;
                $this->logEntry($userId, $title, $body, $url, $result['status'] ?? 'failed', $result['error'] ?? '');
                if (in_array($result['status'] ?? '', ['expired', 'invalid'])) {
                    $this->deactivateSubscription($sub['endpoint']);
                }
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    public function broadcast(string $title, string $body, string $url = '/'): array
    {
        if (!$this->db) return ['sent' => 0, 'failed' => 0];
        $subs = $this->getSubscriptions();
        $sent = 0;
        $failed = 0;

        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url]);

        foreach ($subs as $sub) {
            $result = $this->sendWebPush($sub['endpoint'], $sub['p256dh_key'], $sub['auth_key'], $payload);
            if ($result['success']) {
                $sent++;
                $this->logEntry((int)$sub['user_id'], $title, $body, $url, 'sent');
            } else {
                $failed++;
                $this->logEntry((int)$sub['user_id'], $title, $body, $url, $result['status'] ?? 'failed', $result['error'] ?? '');
                if (in_array($result['status'] ?? '', ['expired', 'invalid'])) {
                    $this->deactivateSubscription($sub['endpoint']);
                }
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    public function getLog(int $limit = 50): array
    {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare("SELECT * FROM push_notification_log ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (\PDOException $e) {
            return [];
        }
    }

    public function getStats(): array
    {
        if (!$this->db) return ['total_subscribers' => 0, 'sent_today' => 0, 'success_rate' => 0];
        try {
            $total = $this->db->query("SELECT COUNT(*) FROM push_subscriptions WHERE is_active = 1")->fetchColumn();
            $sentToday = $this->db->query("SELECT COUNT(*) FROM push_notification_log WHERE status = 'sent' AND DATE(created_at) = CURDATE()")->fetchColumn();
            $totalToday = $this->db->query("SELECT COUNT(*) FROM push_notification_log WHERE DATE(created_at) = CURDATE()")->fetchColumn();
            $successRate = $totalToday > 0 ? round(($sentToday / $totalToday) * 100, 1) : 0;

            return [
                'total_subscribers' => (int)$total,
                'sent_today' => (int)$sentToday,
                'success_rate' => $successRate,
            ];
        } catch (\PDOException $e) {
            return ['total_subscribers' => 0, 'sent_today' => 0, 'success_rate' => 0];
        }
    }

    private function sendWebPush(string $endpoint, string $p256dh, string $auth, string $payload): array
    {
        $privateKeyRaw = $_ENV['VAPID_PRIVATE_KEY'] ?? '';
        $publicKey = $_ENV['VAPID_PUBLIC_KEY'] ?? '';
        $subject = $_ENV['VAPID_SUBJECT'] ?? 'mailto:admin@apsdreamhome.com';

        if (!$privateKeyRaw || !$publicKey) {
            return ['success' => false, 'status' => 'config_error', 'error' => 'VAPID keys not configured'];
        }

        // Handle PEM:file reference or raw base64url key
        if (strpos($privateKeyRaw, 'PEM:') === 0) {
            $pemFile = dirname(__DIR__, 2) . '/' . substr($privateKeyRaw, 4);
            $privateKey = file_get_contents($pemFile);
            if (!$privateKey) {
                return ['success' => false, 'status' => 'config_error', 'error' => 'VAPID private key file not found: ' . $pemFile];
            }
        } else {
            $privateKey = $privateKeyRaw;
        }

        $authKey = base64_decode(strtr($auth, '-_', '+/'));
        $userKey = base64_decode(strtr($p256dh, '-_', '+/'));

        $salt = random_bytes(16);
        $serverPublicKey = base64_decode(strtr($publicKey, '-_', '+/'));

        $ikm = hash_hmac('sha256', $userKey, $authKey, true);
        $prk = hash_hmac('sha256', "\0\0\0\1Info for Web Push:{$serverPublicKey}{$salt}", $ikm, true);
        $key = hash_hmac('sha256', "WebPush:{$endpoint}{$salt}", $prk, true);

        $iv = substr($salt, 0, 12);
        $tag = '';
        $encrypted = openssl_encrypt($payload, 'aes-128-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        $localPublicKey = base64_decode(strtr($publicKey, '-_', '+/'));
        $body = $localPublicKey . $iv . $encrypted . $tag;

        $timestamp = time();
        $urlParts = parse_url($endpoint);
        $ttl = 86400;

        $headerPayload = self::b64url(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $bodyPayload = self::b64url(json_encode(['aud' => $urlParts['scheme'] . '://' . $urlParts['host'], 'exp' => $timestamp + 43200, 'sub' => $subject]));

        $signingInput = "{$headerPayload}.{$bodyPayload}";

        $signCmd = openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$signCmd || !$signature) {
            return ['success' => false, 'status' => 'sign_error', 'error' => 'VAPID signing failed'];
        }

        $r = substr($signature, 0, 32);
        $s = substr($signature, 32, 32);
        $sig = self::b64url($r . $s);

        $jwt = "{$signingInput}.{$sig}";

        $headers = [
            "Content-Type: application/octet-stream",
            "Content-Encoding: aes128gcm",
            "Content-Length: " . strlen($body),
            "TTL: {$ttl}",
            "Authorization: WebPush {$jwt}",
            "Crypto-Key: p256dh=" . self::b64url($localPublicKey),
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'status' => 'network_error', 'error' => $error];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true];
        }

        $statusMap = [
            404 => 'expired',
            410 => 'expired',
            401 => 'invalid',
            403 => 'invalid',
        ];

        return [
            'success' => false,
            'status' => $statusMap[$httpCode] ?? 'failed',
            'error' => "HTTP {$httpCode}: {$response}",
        ];
    }

    private static function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function logEntry(?int $userId, string $title, string $body, string $url, string $status, string $error = ''): void
    {
        if (!$this->db) return;
        try {
            $stmt = $this->db->prepare("INSERT INTO push_notification_log (user_id, title, body, url, status, error_message) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $title, $body, $url, $status, $error]);
        } catch (\PDOException $e) {
        }
    }

    private function deactivateSubscription(string $endpoint): void
    {
        if (!$this->db) return;
        try {
            $stmt = $this->db->prepare("UPDATE push_subscriptions SET is_active = 0 WHERE endpoint = ?");
            $stmt->execute([$endpoint]);
        } catch (\PDOException $e) {
        }
    }
}
