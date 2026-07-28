<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Communication\PushSender;
use PDO;
use App\Traits\TenantAwareTrait;

/**
 * Web Push Notification Controller (RFC 8291 / RFC 8292 / VAPID).
 *
 * Endpoints:
 *   POST /push/subscribe    — register a browser subscription for the
 *                              current user (idempotent by endpoint)
 *   POST /push/unsubscribe  — remove the current user's subscription
 *   POST /push/test         — send a test push to the current user
 *   GET  /push/vapid-key    — return the VAPID public key for the client
 *                              to pass to PushManager.subscribe()
 */
class PushNotificationController extends BaseController
{
    use TenantAwareTrait;
    private PushSender $sender;

    public function __construct()
    {
        parent::__construct();
        $this->sender = new PushSender();
    }

    /**
     * Web Push endpoints are called by the service worker / browser's
     * PushManager, which has no CSRF token. Authentication is via the
     * session cookie (Origin-restricted by the browser).
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * POST /push/subscribe
     * Body: { endpoint, keys: { p256dh, auth } }
     */
    public function subscribe($request = null)
    {
        if (!$this->isLoggedIn()) {
            return $this->json(['success' => false, 'error' => 'login_required'], 401);
        }

        $payload = $this->readPayload();
        $endpoint = trim((string)($payload['endpoint'] ?? ''));
        $p256dh   = trim((string)($payload['keys']['p256dh'] ?? ($payload['p256dh'] ?? '')));
        $auth     = trim((string)($payload['keys']['auth']   ?? ($payload['auth']   ?? '')));

        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            return $this->json([
                'success' => false,
                'error'   => 'Missing endpoint/p256dh/auth',
            ], 400);
        }

        if (!preg_match('#^https?://#i', $endpoint)) {
            return $this->json(['success' => false, 'error' => 'Invalid endpoint URL'], 400);
        }

        try {
            $userId = $this->currentUserId();
            $id = $this->sender->subscribe($userId, $endpoint, $p256dh, $auth);
            return $this->json([
                'success'       => true,
                'id'            => $id,
                'vapid_key'     => $this->sender->getVapidPublicKey(),
            ]);
        } catch (\Throwable $e) {
            error_log('PushNotificationController::subscribe: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * POST /push/unsubscribe
     * Body: { endpoint }
     */
    public function unsubscribe($request = null)
    {
        if (!$this->isLoggedIn()) {
            return $this->json(['success' => false, 'error' => 'login_required'], 401);
        }

        $payload = $this->readPayload();
        $endpoint = trim((string)($payload['endpoint'] ?? ''));
        if ($endpoint === '') {
            return $this->json(['success' => false, 'error' => 'Missing endpoint'], 400);
        }

        try {
            $userId = $this->currentUserId();
            $ok = $this->sender->unsubscribe($userId, $endpoint);
            return $this->json(['success' => $ok]);
        } catch (\Throwable $e) {
            error_log('PushNotificationController::unsubscribe: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Server error'], 500);
        }
    }

    /**
     * POST /push/test
     * Sends a test push to all of the current user's subscriptions.
     */
    public function test($request = null)
    {
        if (!$this->isLoggedIn()) {
            return $this->json(['success' => false, 'error' => 'login_required'], 401);
        }
        if (!$this->sender->isConfigured()) {
            return $this->json(['success' => false, 'error' => 'VAPID not configured on server'], 503);
        }

        $payload = $this->readPayload();
        $title = trim((string)($payload['title'] ?? 'APS Dream Home'));
        $body  = trim((string)($payload['body']  ?? 'This is a test push notification.'));
        $url   = (string)($payload['url']   ?? '/');

        try {
            $userId = $this->currentUserId();
            $result = $this->sender->sendToUser($userId, $title, $body, $url);
            return $this->json([
                'success' => $result['failed'] === 0,
                'result'  => $result,
            ]);
        } catch (\Throwable $e) {
            error_log('PushNotificationController::test: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /push/vapid-key
     * Returns the server's VAPID public key in base64url form so the
     * client can call PushManager.subscribe({ userVisibleOnly: true,
     * applicationServerKey: <key> }).
     */
    public function vapidPublicKey($request = null)
    {
        $key = $this->sender->getVapidPublicKey();
        if ($key === '') {
            return $this->json(['success' => false, 'error' => 'VAPID not configured'], 503);
        }
        return $this->json(['success' => true, 'vapid_key' => $key]);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    /**
     * Read JSON or form-encoded request body. Returns associative array.
     */
    private function readPayload(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $raw = file_get_contents('php://input') ?: '';

        if (stripos($contentType, 'application/json') !== false && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        if (!empty($_POST) && is_array($_POST)) {
            return $_POST;
        }
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
            parse_str($raw, $parsed);
            if (is_array($parsed)) {
                return $parsed;
            }
        }
        return [];
    }

    protected function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']) ||
               !empty($_SESSION['admin_id']) ||
               !empty($_SESSION['associate_id']) ||
               !empty($_SESSION['agent_id']) ||
               !empty($_SESSION['employee_id']);
    }

    private function currentUserId(): int
    {
        // Prefer customer id, fall back to other role ids
        $candidates = ['user_id', 'admin_id', 'associate_id', 'agent_id', 'employee_id'];
        foreach ($candidates as $key) {
            if (!empty($_SESSION[$key])) {
                return (int)$_SESSION[$key];
            }
        }
        return 0;
    }
}
