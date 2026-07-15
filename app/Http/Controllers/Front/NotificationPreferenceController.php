<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use PDO;

/**
 * NotificationPreferenceController
 *
 * Customer-facing notification preferences UI + API.
 * Allows customers to choose which channels (email / SMS / WhatsApp / push)
 * should deliver which notification types (booking / payment / marketing / etc).
 *
 * The user_notification_preferences table stores one row per (user_id, notification_type)
 * with toggle columns for each channel. If no row exists yet, all channels are
 * treated as enabled (the existing schema defaults are applied when the row is
 * first created).
 */
class NotificationPreferenceController extends BaseController
{
    public const NOTIFICATION_TYPES = [
        'booking'     => ['Booking Updates',     'Plot/property booking confirmations and status changes'],
        'payment'     => ['Payment Confirmations', 'Receipts, reminders, payment confirmations'],
        'agreement'   => ['Agreement Updates',   'When an agreement is generated, signed, or updated'],
        'registry'    => ['Registry Alerts',     'Registry scheduling and completion notifications'],
        'possession'  => ['Possession Updates',  'Possession, handover, and key collection alerts'],
        'property'    => ['Property Alerts',     'New properties, price drops, recommendations'],
        'marketing'   => ['Marketing & Offers',  'New projects, offers, newsletters, promotions'],
        'welcome'     => ['Welcome Messages',    'Account creation and onboarding notifications'],
        'login_alert' => ['Login Alerts',        'Security alerts for new device/logins (always on)'],
    ];

    public const CHANNELS = ['email', 'sms', 'whatsapp', 'push'];

    /**
     * Default toggle map used when no preference row exists yet.
     * Channels are enabled by default; customers can opt out individually.
     */
    public const DEFAULT_PREFERENCES = [
        'email'    => 1,
        'sms'      => 1,
        'whatsapp' => 1,
        'push'     => 1,
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * GET /user/notification-preferences
     * Render the preferences form for the logged-in customer.
     */
    public function index()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $prefs = $this->loadPreferences((int) $user['id']);

        $this->setFlash('page_title', 'Notification Preferences - APS Dream Home');

        $this->layout = 'layouts/customer';
        $this->render('pages/user/notification_preferences', [
            'page_title'       => 'Notification Preferences - APS Dream Home',
            'current_page'     => 'settings',
            'user'             => $user,
            'prefs'            => $prefs,
            'types'            => self::NOTIFICATION_TYPES,
            'channels'         => self::CHANNELS,
            'flash_success'    => $this->getFlash('flash_success', ''),
            'flash_error'      => $this->getFlash('flash_error', ''),
            'csrf_token'       => $this->getCsrfToken(),
        ]);
    }

    /**
     * POST /user/notification-preferences
     * Persist the submitted preferences.
     */
    public function update()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $submitted = $_POST['channels'] ?? [];
        $frequency = $_POST['frequency'] ?? 'immediate';
        $allowedFrequencies = ['immediate', 'hourly', 'daily', 'weekly', 'never'];
        if (!in_array($frequency, $allowedFrequencies, true)) {
            $frequency = 'immediate';
        }

        try {
            $this->savePreferences((int) $user['id'], $submitted, $frequency);
            $_SESSION['flash_success'] = 'Notification preferences updated successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Failed to save preferences: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/user/notification-preferences');
        exit;
    }

    /**
     * GET /api/user/notification-preferences
     * JSON endpoint returning the current preferences for the logged-in user.
     * Useful for the user dashboard widget, mobile clients, etc.
     */
    public function getPreferences()
    {
        @session_start();
        if (empty($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode(['ok' => false, 'error' => 'Authentication required']);
            return;
        }

        $userId = (int) $_SESSION['user_id'];
        $prefs = $this->loadPreferences($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'ok'      => true,
            'user_id' => $userId,
            'prefs'   => $prefs,
            'types'   => self::NOTIFICATION_TYPES,
        ]);
    }

    /**
     * Load all preferences for a user, keyed by notification type.
     * If no row exists for a given type, the default map is returned.
     *
     * @return array<string, array{email:bool,sms:bool,whatsapp:bool,push:bool,frequency:string}>
     */
    private function loadPreferences(int $userId): array
    {
        $prefs = [];
        foreach (array_keys(self::NOTIFICATION_TYPES) as $type) {
            $prefs[$type] = [
                'email'    => (bool) self::DEFAULT_PREFERENCES['email'],
                'sms'      => (bool) self::DEFAULT_PREFERENCES['sms'],
                'whatsapp' => (bool) self::DEFAULT_PREFERENCES['whatsapp'],
                'push'     => (bool) self::DEFAULT_PREFERENCES['push'],
                'frequency'=> 'immediate',
            ];
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, frequency
                 FROM user_notification_preferences
                 WHERE user_id = ?"
            );
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $type = $row['notification_type'];
                if (!isset(self::NOTIFICATION_TYPES[$type])) {
                    continue;
                }
                $prefs[$type] = [
                    'email'    => (bool) ($row['email_enabled']    ?? 1),
                    'sms'      => (bool) ($row['sms_enabled']      ?? 0),
                    'whatsapp' => (bool) ($row['whatsapp_enabled'] ?? 0),
                    'push'     => (bool) ($row['push_enabled']     ?? 1),
                    'frequency'=> $row['frequency'] ?? 'immediate',
                ];
            }
        } catch (\Throwable $e) {
            // Table missing or query error - fall back to defaults silently
            error_log('NotificationPreferenceController::loadPreferences error: ' . $e->getMessage());
        }

        return $prefs;
    }

    /**
     * Upsert preference rows for every known notification type.
     *
     * @param array<string, string[]> $submitted channels[type] = [email, sms, ...]
     */
    private function savePreferences(int $userId, array $submitted, string $frequency): void
    {
        $sql = "INSERT INTO user_notification_preferences
                    (user_id, user_type, notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, frequency, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    email_enabled    = VALUES(email_enabled),
                    sms_enabled      = VALUES(sms_enabled),
                    whatsapp_enabled = VALUES(whatsapp_enabled),
                    push_enabled     = VALUES(push_enabled),
                    frequency        = VALUES(frequency),
                    updated_at       = NOW()";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException('Failed to prepare preferences upsert statement');
        }

        foreach (array_keys(self::NOTIFICATION_TYPES) as $type) {
            $channels = $submitted[$type] ?? [];
            if (!is_array($channels)) {
                $channels = [];
            }

            $email    = in_array('email',    $channels, true) ? 1 : 0;
            $sms      = in_array('sms',      $channels, true) ? 1 : 0;
            $whatsapp = in_array('whatsapp', $channels, true) ? 1 : 0;
            $push     = in_array('push',     $channels, true) ? 1 : 0;

            $stmt->execute([
                $userId,
                'customer',
                $type,
                $email,
                $sms,
                $whatsapp,
                $push,
                $frequency,
            ]);
        }
    }

    private function requireCustomerLogin(): void
    {
        @session_start();

        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $role = $_SESSION['role'] ?? '';
        if ($role !== '' && $role !== 'customer') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    private function getUser(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: ' . BASE_URL . '/user/logout');
            exit;
        }

        return $user;
    }
}
