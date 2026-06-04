<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Services\TotpService;

class TwoFactorController extends BaseController
{
    public function __construct() { parent::__construct(); }

    public function setup()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $totp = new TotpService($this->db);
        $secret = $totp->getSecret($userId);
        $isEnabled = $totp->isEnabled($userId);

        if (!$secret || !$isEnabled) {
            if (empty($_SESSION['2fa_temp_secret'])) {
                $secret = $totp->generateSecret(20);
                $_SESSION['2fa_temp_secret'] = $secret;
            } else {
                $secret = $_SESSION['2fa_temp_secret'];
            }
        }

        $userEmail = $_SESSION['user_email'] ?? 'user@example.com';
        $qrUrl = $totp->qrCodeUrl($secret, $userEmail, 200);
        $manualKey = $secret;
        $otp = $totp->getOtp($secret);
        $this->data = [
            'page_title' => 'Two-Factor Authentication',
            'secret' => $secret,
            'qr_url' => $qrUrl,
            'manual_key' => chunk_split($manualKey, 4, ' '),
            'current_otp' => $otp,
            'is_enabled' => $isEnabled,
        ];
        return $this->render('user/two_factor', $this->data);
    }

    public function enable()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) return $this->jsonResponse(['error' => 'Not authenticated'], 401);

        $secret = $_SESSION['2fa_temp_secret'] ?? '';
        $code = trim($_POST['code'] ?? '');

        if (!$secret || !$code) {
            $_SESSION['flash_error'] = 'Missing secret or code';
            header('Location: ' . BASE_URL . '/user/two-factor');
            exit;
        }

        $totp = new TotpService($this->db);
        if (!$totp->verify($secret, $code)) {
            $_SESSION['flash_error'] = 'Invalid code. Please try again.';
            header('Location: ' . BASE_URL . '/user/two-factor');
            exit;
        }

        $totp->enableForUser($userId, $secret);
        $backupCodes = $this->generateBackupCodes(8);
        $this->saveBackupCodes($userId, $backupCodes);

        unset($_SESSION['2fa_temp_secret']);
        $_SESSION['flash_success'] = '2FA enabled! Save your backup codes: ' . implode(', ', $backupCodes);
        header('Location: ' . BASE_URL . '/user/two-factor');
        exit;
    }

    public function disable()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) return $this->jsonResponse(['error' => 'Not authenticated'], 401);

        $totp = new TotpService($this->db);
        $totp->disableForUser($userId);
        $_SESSION['flash_success'] = '2FA disabled';
        header('Location: ' . BASE_URL . '/user/two-factor');
        exit;
    }

    public function verify()
    {
        @session_start();
        $userId = (int)($_SESSION['pending_2fa_user'] ?? 0);
        $secret = $_SESSION['pending_2fa_secret'] ?? '';
        $code = trim($_POST['code'] ?? $_GET['code'] ?? '');

        if (!$userId || !$secret || !$code) {
            $_SESSION['flash_error'] = 'Invalid verification request';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $totp = new TotpService($this->db);
        if (!$totp->verify($secret, $code)) {
            $_SESSION['flash_error'] = 'Invalid code';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $pendingRole = $_SESSION['pending_2fa_role'] ?? 'customer';
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $pendingRole;
        $_SESSION['logged_in'] = true;
        $_SESSION['2fa_verified'] = true;

        unset($_SESSION['pending_2fa_user'], $_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_role']);

        $redirectMap = [
            'admin' => '/admin/dashboard',
            'employee' => '/admin/dashboard',
            'agent' => '/admin/agent-dashboard',
            'associate' => '/associate/dashboard',
            'customer' => '/user/dashboard'
        ];
        $redirect = $redirectMap[$pendingRole] ?? '/user/dashboard';
        header('Location: ' . BASE_URL . $redirect);
        exit;
    }

    private function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    private function saveBackupCodes(int $userId, array $codes): void
    {
        try {
            $st = $this->db->prepare("UPDATE users SET two_factor_backup_codes = :c WHERE id = :id");
            $st->execute([':c' => json_encode($codes), ':id' => $userId]);
        } catch (\Throwable $e) {}
    }
}
