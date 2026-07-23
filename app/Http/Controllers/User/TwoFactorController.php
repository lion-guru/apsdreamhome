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

        $userEmail = $_SESSION['user_email'] ?? '';
        if (empty($userEmail)) {
            // Fetch from DB if not in session
            try {
                $stmt = $this->db->prepare("SELECT email FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                $userEmail = $row['email'] ?? '';
            } catch (\Throwable $e) {
                // Use user ID as identifier if email unavailable
                $userEmail = 'user_' . $userId . '@apsdreamhome.com';
            }
        }
        $qrUrl = $totp->qrCodeUrl($secret, $userEmail, 200);
        $manualKey = $secret;
        $otp = $totp->getOtp($secret);
        $backupStats = $totp->getBackupCodeStats($userId);
        $this->data = [
            'page_title' => 'Two-Factor Authentication',
            'secret' => $secret,
            'qr_url' => $qrUrl,
            'manual_key' => chunk_split($manualKey, 4, ' '),
            'current_otp' => $otp,
            'is_enabled' => $isEnabled,
            'backup_stats' => $backupStats,
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
        $backupCodes = $totp->generateBackupCodes(8);
        $totp->saveBackupCodes($userId, $backupCodes);

        unset($_SESSION['2fa_temp_secret']);
        $_SESSION['flash_success'] = '2FA enabled! Save your backup codes now.';
        session_write_close();
        header('Location: ' . BASE_URL . '/user/two-factor/backup-codes');
        exit;
    }

    public function disable()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) return $this->jsonResponse(['error' => 'Not authenticated'], 401);

        $totp = new TotpService($this->db);
        $totp->disableForUser($userId);

        $disabledAt = date('Y-m-d H:i:s');
        $_SESSION['flash_success'] = '2FA has been disabled.';
        $_SESSION['2fa_disabled_at'] = $disabledAt;
        header('Location: ' . BASE_URL . '/user/two-factor/disabled');
        exit;
    }

    public function verify()
    {
        @session_start();
        $code = trim($_POST['code'] ?? $_GET['code'] ?? '');

        // Accept both the new array shape and the legacy scalar (for any
        // older browsers still holding a pre-fix session).
        $pending = $_SESSION['pending_2fa_user'] ?? null;
        if (is_array($pending)) {
            $userId      = (int)($pending['id'] ?? 0);
            $pendingRole = (string)($pending['role'] ?? 'customer');
        } else {
            $userId      = (int)$pending;
            $pendingRole = (string)($_SESSION['pending_2fa_role'] ?? 'customer');
        }

        // GET requests (no code) just render the OTP page by bouncing to /login,
        // where customer_login.php detects $_SESSION['pending_2fa_user'] and
        // shows the 2FA input. Only POST with a code is a true verify call.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (!$userId || !$code) {
            $_SESSION['flash_error'] = 'Invalid verification request';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Re-fetch the secret from the database on every verification attempt
        // so a stale session can't validate a rotated/changed 2FA secret.
        $totp = new TotpService($this->db);
        $secret = $totp->getSecret($userId);
        if (!$secret) {
            unset($_SESSION['pending_2fa_user'], $_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_role'], $_SESSION['pending_2fa_attempts']);
            $_SESSION['flash_error'] = '2FA is no longer enabled for this account.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if (!$totp->verify($secret, $code)) {
            // Increment attempts; lock out after 5 failed tries to thwart brute force.
            $_SESSION['pending_2fa_attempts'] = (int)($_SESSION['pending_2fa_attempts'] ?? 0) + 1;
            if ($_SESSION['pending_2fa_attempts'] >= 5) {
                unset($_SESSION['pending_2fa_user'], $_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_role'], $_SESSION['pending_2fa_attempts']);
                $_SESSION['flash_error'] = 'Too many failed attempts. Please log in again.';
            } else {
                $_SESSION['flash_error'] = 'Invalid code. ' . (5 - $_SESSION['pending_2fa_attempts']) . ' attempt(s) remaining.';
            }
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Success — set the regular session vars and clear the pending 2FA state.
        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role']    = $pendingRole;
        $_SESSION['logged_in'] = true;
        $_SESSION['2fa_verified'] = true;
        $_SESSION['last_regenerate'] = time();

        unset($_SESSION['pending_2fa_user'], $_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_role'], $_SESSION['pending_2fa_attempts']);

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

    public function backupCodes()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $totp = new TotpService($this->db);
        $isEnabled = $totp->isEnabled($userId);
        $codes = $totp->getBackupCodes($userId);
        $stats = $totp->getBackupCodeStats($userId);
        $userName = $_SESSION['user_name'] ?? 'User';
        $userEmail = $_SESSION['user_email'] ?? '';
        $csrf = $this->getCsrfToken();
        include __DIR__ . '/../../../views/user/two_factor_backup_codes.php';
    }

    public function recovery()
    {
        @session_start();
        // Accept both the new array shape and the legacy scalar.
        $pending = $_SESSION['pending_2fa_user'] ?? null;
        $userId = is_array($pending) ? (int)($pending['id'] ?? 0) : (int)$pending;
        $hasPending = (bool)$userId;

        $this->data = [
            'page_title' => 'Use Backup Code',
            'has_pending' => $hasPending,
        ];
        return $this->render('user/two_factor_recovery', $this->data);
    }

    public function verifyBackupCode()
    {
        @session_start();
        $code = trim($_POST['code'] ?? $_GET['code'] ?? '');

        // Accept both the new array shape and the legacy scalar.
        $pending = $_SESSION['pending_2fa_user'] ?? null;
        if (is_array($pending)) {
            $userId      = (int)($pending['id'] ?? 0);
            $pendingRole = (string)($pending['role'] ?? 'customer');
        } else {
            $userId      = (int)$pending;
            $pendingRole = (string)($_SESSION['pending_2fa_role'] ?? 'customer');
        }

        if (!$userId || !$code) {
            $_SESSION['flash_error'] = 'Missing user session or backup code. Please login again.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $totp = new TotpService($this->db);
        if (!$totp->verifyBackupCode($userId, $code)) {
            $_SESSION['flash_error'] = 'Invalid or already-used backup code. Please try another.';
            header('Location: ' . BASE_URL . '/user/two-factor/recovery');
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $pendingRole;
        $_SESSION['logged_in'] = true;
        $_SESSION['2fa_verified'] = true;
        $_SESSION['2fa_used_backup'] = true;
        $_SESSION['last_regenerate'] = time();

        unset($_SESSION['pending_2fa_user'], $_SESSION['pending_2fa_secret'], $_SESSION['pending_2fa_role'], $_SESSION['pending_2fa_attempts']);

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

    public function disabled()
    {
        @session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $this->data = [
            'page_title' => '2FA Disabled',
            'disabled_at' => $_SESSION['2fa_disabled_at'] ?? date('Y-m-d H:i:s'),
        ];
        return $this->render('user/two_factor_disabled', $this->data);
    }
}
