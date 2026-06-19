<?php
namespace App\Services\Communication;

use App\Core\Database\Database;
use RuntimeException;

/**
 * Web Push Sender (RFC 8291 + RFC 8292).
 *
 * Sends encrypted push notifications to subscribed browsers using VAPID
 * authentication. Works without external libraries — uses OpenSSL for
 * ECDSA (ES256) signing, ECDH shared-secret derivation, and AES-128-GCM
 * payload encryption.
 *
 * Storage is in the `push_subscriptions` table (id, user_id, endpoint,
 * p256dh_key, auth_key, is_active, last_used_at, created_at).
 */
class PushSender
{
    /** @var Database */
    private $db;

    private string $vapidPublicKey;
    private string $vapidPrivateKey;
    private string $vapidSubject;

    private const TTL_DEFAULT = 86400;          // 24h
    private const PADDING_MAX = 4096;           // aes128gcm padding cap
    private const AES_TAG_LEN = 16;

    public function __construct()
    {
        $this->db = Database::getInstance();

        $privRaw = $_ENV['VAPID_PRIVATE_KEY'] ?? getenv('VAPID_PRIVATE_KEY') ?: '';
        $this->vapidPublicKey  = $this->decodeVapidKey($_ENV['VAPID_PUBLIC_KEY']  ?? getenv('VAPID_PUBLIC_KEY')  ?: '');
        $subject = $_ENV['VAPID_SUBJECT'] ?? getenv('VAPID_SUBJECT') ?: 'mailto:admin@apsdreamhome.com';

        // Handle PEM:file reference
        if (strpos($privRaw, 'PEM:') === 0) {
            $pemFile = dirname(__DIR__, 3) . '/' . substr($privRaw, 4);
            $pemContent = @file_get_contents($pemFile);
            $this->vapidPrivateKey = $pemContent ? $this->decodeVapidKey($pemContent) : '';
        } else {
            $this->vapidPrivateKey = $this->decodeVapidKey($privRaw);
        }
        $this->vapidSubject = $subject;
    }

    /**
     * Send a push notification to all active subscriptions of a user.
     *
     * @return array{success:int, failed:int, results:array<int,array<string,mixed>>}
     */
    public function sendToUser(int $userId, string $title, string $body = '', string $url = '/'): array
    {
        $subs = $this->getSubscriptionsForUser($userId);
        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'url'   => $url,
            'icon'  => '/assets/images/icons/icon-192x192.png',
            'badge' => '/assets/images/icons/badge-72x72.png',
            'tag'   => 'aps-' . substr(md5($title . '|' . $url), 0, 10),
        ], JSON_UNESCAPED_UNICODE);

        return $this->dispatchToSubscriptions($subs, $payload);
    }

    /**
     * Send a push to every active subscription across all users.
     *
     * @return array{success:int, failed:int, results:array<int,array<string,mixed>>}
     */
    public function sendToAll(string $title, string $body = '', string $url = '/'): array
    {
        $subs = $this->getAllActiveSubscriptions();
        $payload = json_encode([
            'title' => $title,
            'body'  => $body,
            'url'   => $url,
            'icon'  => '/assets/images/icons/icon-192x192.png',
            'badge' => '/assets/images/icons/badge-72x72.png',
            'tag'   => 'aps-broadcast-' . substr(md5($title), 0, 10),
        ], JSON_UNESCAPED_UNICODE);

        return $this->dispatchToSubscriptions($subs, $payload);
    }

    /**
     * Register or refresh a subscription. Returns the subscription id.
     */
    public function subscribe(int $userId, string $endpoint, string $p256dh, string $auth): int
    {
        // Idempotency: same endpoint for same user → update keys + last_used_at
        $stmt = $this->db->prepare(
            'SELECT id FROM push_subscriptions WHERE user_id = ? AND endpoint = ? LIMIT 1'
        );
        $stmt->execute([$userId, $endpoint]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $this->db->prepare(
                'UPDATE push_subscriptions
                    SET p256dh_key = ?, auth_key = ?, device_token = ?, is_active = 1, last_used_at = NOW()
                    WHERE id = ?'
            )->execute([$p256dh, $auth, $endpoint, (int)$existing]);
            return (int)$existing;
        }

        $this->db->prepare(
            'INSERT INTO push_subscriptions
                (user_id, device_type, device_token, endpoint, p256dh_key, auth_key, is_active, last_used_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())'
        )->execute([$userId, 'web', $endpoint, $endpoint, $p256dh, $auth]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Remove a subscription (unsub). Matches by user_id + endpoint.
     */
    public function unsubscribe(int $userId, string $endpoint): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM push_subscriptions WHERE user_id = ? AND endpoint = ?'
        );
        $stmt->execute([$userId, $endpoint]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Check that VAPID keys are configured and decodable.
     */
    public function isConfigured(): bool
    {
        return $this->vapidPublicKey !== '' && $this->vapidPrivateKey !== '';
    }

    public function getVapidPublicKey(): string
    {
        return $this->vapidPublicKey !== ''
            ? self::b64UrlEncode($this->vapidPublicKey)
            : '';
    }

    // -----------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------

    /**
     * @return array<int,array{endpoint:string,p256dh_key:string,auth_key:string,id:int}>
     */
    private function getSubscriptionsForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, endpoint, p256dh_key, auth_key
                FROM push_subscriptions
                WHERE user_id = ? AND is_active = 1
                  AND endpoint IS NOT NULL AND p256dh_key IS NOT NULL AND auth_key IS NOT NULL'
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int,array{endpoint:string,p256dh_key:string,auth_key:string,id:int,user_id:int}>
     */
    private function getAllActiveSubscriptions(): array
    {
        $stmt = $this->db->query(
            'SELECT id, user_id, endpoint, p256dh_key, auth_key
                FROM push_subscriptions
                WHERE is_active = 1
                  AND endpoint IS NOT NULL AND p256dh_key IS NOT NULL AND auth_key IS NOT NULL'
        );
        if (!$stmt) {
            return [];
        }
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    /**
     * @param array<int,array<string,mixed>> $subs
     * @return array{success:int,failed:int,results:array<int,array<string,mixed>>}
     */
    private function dispatchToSubscriptions(array $subs, string $payload): array
    {
        $results = [];
        $success = 0;
        $failed  = 0;

        if (!$this->isConfigured()) {
            return [
                'success' => 0,
                'failed'  => count($subs),
                'results' => [['error' => 'VAPID keys not configured']],
            ];
        }

        foreach ($subs as $sub) {
            $res = $this->deliverOne($sub, $payload);
            $results[] = $res;

            if (!empty($res['success'])) {
                $success++;
            } else {
                $failed++;
                // Auto-clean stale endpoint (404/410 from push service)
                if (!empty($res['stale']) && !empty($sub['id'])) {
                    $this->deactivateSubscription((int)$sub['id']);
                }
            }
        }

        return ['success' => $success, 'failed' => $failed, 'results' => $results];
    }

    /**
     * @param array<string,mixed> $sub
     * @return array<string,mixed>
     */
    private function deliverOne(array $sub, string $payload): array
    {
        $endpoint = (string)($sub['endpoint'] ?? '');
        if ($endpoint === '') {
            return ['success' => false, 'error' => 'Missing endpoint'];
        }

        try {
            $audience = $this->endpointAudience($endpoint);
            $jwt = $this->buildVapidJwt($audience);
            $encrypted = $this->encryptPayload($payload, (string)$sub['p256dh_key'], (string)$sub['auth_key']);

            $headers = [
                'Authorization: vapid t=' . $jwt . ', k=' . self::b64UrlEncode($this->vapidPublicKey),
                'Content-Type: application/octet-stream',
                'Content-Encoding: aes128gcm',
                'TTL: ' . self::TTL_DEFAULT,
                'Content-Length: ' . (function_exists('strlen') ? strlen($encrypted) : 0),
            ];

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $encrypted,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $body = curl_exec($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = curl_error($ch);
            curl_close($ch);

            if ($err !== '') {
                return ['success' => false, 'error' => 'curl: ' . $err, 'endpoint' => $endpoint];
            }

            if ($code >= 200 && $code < 300) {
                if (!empty($sub['id'])) {
                    $this->touchSubscription((int)$sub['id']);
                }
                return ['success' => true, 'http_code' => $code, 'endpoint' => $endpoint];
            }

            // 404/410 → endpoint is gone, mark stale for cleanup
            $stale = ($code === 404 || $code === 410);
            return [
                'success'  => false,
                'http_code'=> $code,
                'stale'    => $stale,
                'body'     => is_string($body) ? substr($body, 0, 200) : '',
                'endpoint' => $endpoint,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'endpoint' => $endpoint];
        }
    }

    private function touchSubscription(int $id): void
    {
        try {
            $this->db->prepare('UPDATE push_subscriptions SET last_used_at = NOW() WHERE id = ?')
                     ->execute([$id]);
        } catch (\Throwable $e) { /* best-effort */ }
    }

    private function deactivateSubscription(int $id): void
    {
        try {
            $this->db->prepare('UPDATE push_subscriptions SET is_active = 0 WHERE id = ?')
                     ->execute([$id]);
        } catch (\Throwable $e) { /* best-effort */ }
    }

    /**
     * Build the VAPID JWT (ES256 / P-256 ECDSA). RFC 8292 §2.
     */
    private function buildVapidJwt(string $audience): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'ES256'];
        $claims = [
            'aud' => $audience,
            'exp' => time() + 12 * 3600,
            'sub' => $this->vapidSubject,
        ];

        $signingInput =
            self::b64UrlEncode(json_encode($header)) . '.' .
            self::b64UrlEncode(json_encode($claims));

        $signature = $this->ecdsaSign($signingInput);
        if ($signature === null) {
            throw new RuntimeException('Failed to sign VAPID JWT (check VAPID_PRIVATE_KEY)');
        }
        // ES256 → raw R || S, 64 bytes total
        $rs = $this->derToRawSignature($signature);
        return $signingInput . '.' . self::b64UrlEncode($rs);
    }

    /**
     * Sign a message with the VAPID private key (P-256). Returns DER.
     */
    private function ecdsaSign(string $data): ?string
    {
        $keyPem = $this->buildEcPrivatePem($this->vapidPrivateKey);
        $res = openssl_pkey_get_private($keyPem);
        if (!$res) {
            return null;
        }
        $sig = '';
        if (!openssl_sign($data, $sig, $res, OPENSSL_ALGO_SHA256)) {
            return null;
        }
        return $sig;
    }

    /**
     * Convert DER ECDSA signature → raw R||S (64 bytes for P-256).
     */
    private function derToRawSignature(string $der): string
    {
        // DER: 30 44 02 20 [32 bytes R] 02 20 [32 bytes S]
        $r = '';
        $s = '';
        if (
            ord($der[0]) === 0x30 && strlen($der) >= 70 &&
            ord($der[2]) === 0x02 && ord($der[3]) === 0x20
        ) {
            $r = substr($der, 4, 32);
            $sPos = 4 + 32;
            if (ord($der[$sPos]) === 0x02 && ord($der[$sPos + 1]) === 0x20) {
                $s = substr($der, $sPos + 2, 32);
            }
        }
        if (strlen($r) !== 32 || strlen($s) !== 32) {
            // Fallback: pad/truncate
            $r = str_pad(substr($der, 4, 32), 32, "\0", STR_PAD_LEFT);
            $s = str_pad(substr($der, 38, 32), 32, "\0", STR_PAD_LEFT);
        }
        return $r . $s;
    }

    /**
     * Wrap a raw 32-byte P-256 scalar as a PEM EC private key.
     */
    private function buildEcPrivatePem(string $rawPriv): string
    {
        // SEC1 ECPrivateKey: 30 31 02 01 01 04 20 [32 bytes] a0 0a 06 08 2a 86 48 ce 3d 03 01 07
        $der = "\x30\x31\x02\x01\x01\x04\x20" . $rawPriv .
               "\xa0\x0a\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        $b64 = base64_encode($der);
        $pem  = "-----BEGIN EC PRIVATE KEY-----\n";
        $pem .= chunk_split($b64, 64, "\n");
        $pem .= "-----END EC PRIVATE KEY-----\n";
        return $pem;
    }

    /**
     * RFC 8291 §3.4 — encrypt payload with AES-128-GCM.
     *
     * Output layout (prepended to ciphertext):
     *   salt (16) || rs (4) || idlen (1) || keyid (p256dh) || ciphertext+tag
     *
     * The 16-byte salt is randomly generated; the actual encryption key
     * and nonce are derived via HKDF-SHA-256 from the salt + the
     * sender/receiver ECDH shared secret.
     */
    private function encryptPayload(string $payload, string $p256dhB64Url, string $authB64Url): string
    {
        $clientPubRaw = self::b64UrlDecode($p256dhB64Url);
        $authSecret   = self::b64UrlDecode($authB64Url);
        if (strlen($clientPubRaw) !== 65 || $clientPubRaw[0] !== "\x04") {
            throw new RuntimeException('Invalid p256dh key (expected 65-byte uncompressed point)');
        }
        if (strlen($authSecret) < 16) {
            throw new RuntimeException('Invalid auth secret (expected ≥16 bytes)');
        }

        $sharedSecret = $this->ecdhSharedSecret($clientPubRaw);
        if ($sharedSecret === null) {
            throw new RuntimeException('ECDH shared secret derivation failed');
        }

        // salt is 16 bytes random
        $salt = random_bytes(16);

        // HKDF(salt, ikm = sharedSecret, info = "WebPush: info\0" + ua_public + as_public, L = 32)
        // We can't use the receiver's UA_public inside the server (we ARE the UA),
        // so per RFC 8291 the info is: "WebPush: info\0" || ua_public || as_public
        $serverPub = $this->getServerPublicKeyRaw();
        $info = "WebPush: info\x00" . $serverPub . $clientPubRaw;
        $prk = $this->hkdf($authSecret, $sharedSecret, $info, 32);

        // IKM for content encryption: derived from PRK + "Content-Encoding: aes128gcm"
        $ikm = hash_hmac('sha256', "Content-Encoding: aes128gcm", $prk, true);
        $cek  = $this->hkdf($salt, $ikm, "Content-Encoding: aes01gcm\x00", 16);
        $nonce = $this->hkdf($salt, $ikm, "Content-Encoding: nonce\x00", 12);

        // Pad plaintext (record size 4096 → pad with 0x00..0x02 then 0x00..0x00 terminator)
        $padLen = self::PADDING_MAX - (strlen($payload) + 1 + 16);
        if ($padLen < 0) {
            $padLen = 0;
        }
        // header byte 0x02 indicates the padding delimiter version; we
        // append one 0x00 then $padLen zero bytes for simplicity (RFC 8291 §4)
        $plain = $payload . "\x00" . str_repeat("\x00", $padLen);

        $tag = '';
        $cipher = openssl_encrypt(
            $plain,
            'aes-128-gcm',
            $cek,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            16
        );
        if ($cipher === false) {
            throw new RuntimeException('AES-128-GCM encryption failed: ' . openssl_error_string());
        }
        $ciphertext = $cipher . $tag;

        // Header: salt(16) || rs(4) || idlen(1) || keyid(p256dh)
        $rs = pack('N', self::PADDING_MAX);
        $idLen = pack('C', strlen($clientPubRaw));

        return $salt . $rs . $idLen . $clientPubRaw . $ciphertext;
    }

    /**
     * Compute ECDH shared secret using VAPID private key + client p256dh public.
     */
    private function ecdhSharedSecret(string $clientPubRaw): ?string
    {
        // Wrap the server private + client public as a PEM key pair via
        // OpenSSL's EC_KEY_new; do a deriveBytes via openssl derivation
        $privPem = $this->buildEcPrivatePem($this->vapidPrivateKey);
        $privRes = openssl_pkey_get_private($privPem);
        if (!$privRes) {
            return null;
        }

        // Build client public key PEM from raw 65-byte uncompressed point
        // SubjectPublicKeyInfo wrapper: 30 59 30 13 06 07 ... 03 42 00 [65 bytes]
        $clientPubDer = "\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01" .
                        "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07" .
                        "\x03\x42\x00" . $clientPubRaw;
        $clientPubPem = "-----BEGIN PUBLIC KEY-----\n" .
                        chunk_split(base64_encode($clientPubDer), 64, "\n") .
                        "-----END PUBLIC KEY-----\n";

        $pubRes = openssl_pkey_get_public($clientPubPem);
        if (!$pubRes) {
            return null;
        }

        // Derive shared secret via openssl_pkey_derive (PHP 7.1+, requires OpenSSL 1.1.0+)
        if (function_exists('openssl_pkey_derive')) {
            $secret = null;
            if (@openssl_pkey_derive($pubRes, $secret, $privRes, OPENSSL_KEYTYPE_EC, 32)) {
                return $secret;
            }
        }

        // Fallback: use openssl dh_compute_key style via OpenSSL CLI (won't work
        // without a binary); just fail gracefully.
        return null;
    }

    /**
     * Cached server public key (raw 65 bytes, uncompressed P-256 point).
     */
    private ?string $serverPubCache = null;
    private function getServerPublicKeyRaw(): string
    {
        if ($this->serverPubCache !== null) {
            return $this->serverPubCache;
        }
        $privPem = $this->buildEcPrivatePem($this->vapidPrivateKey);
        $privRes = openssl_pkey_get_private($privPem);
        if (!$privRes) {
            throw new RuntimeException('Cannot load VAPID private key');
        }
        $details = openssl_pkey_get_details($privRes);
        $pubPem = $details['key'] ?? '';
        $marker = "\x03\x42\x00";
        $pos = strpos($pubPem, $marker);
        if ($pos === false) {
            throw new RuntimeException('Cannot extract server public key');
        }
        $this->serverPubCache = substr($pubPem, $pos + 3, 65);
        return $this->serverPubCache;
    }

    /**
     * HKDF-SHA-256 (RFC 5869). salt + ikm → L-byte OKM.
     */
    private function hkdf(string $salt, string $ikm, string $info, int $length): string
    {
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        $t = '';
        $okm = '';
        $counter = 1;
        while (strlen($okm) < $length) {
            $t = hash_hmac('sha256', $t . $info . chr($counter), $prk, true);
            $okm .= $t;
            $counter++;
        }
        return substr($okm, 0, $length);
    }

    /**
     * Extract the "audience" claim for a push endpoint (origin).
     */
    private function endpointAudience(string $endpoint): string
    {
        $parts = parse_url($endpoint);
        if (!isset($parts['scheme'], $parts['host'])) {
            return $endpoint;
        }
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $defaultPort = ($parts['scheme'] === 'https' && ($parts['port'] ?? 443) == 443) ||
                       ($parts['scheme'] === 'http'  && ($parts['port'] ?? 80)  == 80);
        return $parts['scheme'] . '://' . $parts['host'] . ($defaultPort ? '' : $port);
    }

    /**
     * Decode a VAPID key from environment (handles base64 or base64url).
     * Returns raw binary.
     */
    private function decodeVapidKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return '';
        }
        if (strpos($key, '-----BEGIN') !== false) {
            $lines = explode("\n", $key);
            $base64 = '';
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '-----') === 0) {
                    continue;
                }
                $base64 .= $line;
            }
            $der = base64_decode($base64);
            if ($der !== false) {
                $pos = strpos($der, "\x04\x20");
                if ($pos !== false) {
                    return substr($der, $pos + 2, 32);
                }
                return $der;
            }
        }
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key, 2);
            $xDec = self::b64UrlDecode($parts[0]);
            $yDec = self::b64UrlDecode($parts[1]);
            if (strlen($xDec) === 32 && strlen($yDec) === 32) {
                return "\x04" . $xDec . $yDec;
            }
        }
        $decoded = self::b64UrlDecode($key);
        if ($decoded === false || $decoded === '') {
            $decoded = base64_decode($key, true) ?: '';
        }
        return (string)$decoded;
    }

    public static function b64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function b64UrlDecode(string $data): string
    {
        $pad = strlen($data) % 4;
        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }
        $out = base64_decode(strtr($data, '-_', '+/'), true);
        return $out === false ? '' : $out;
    }
}
