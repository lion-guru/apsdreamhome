<?php
/**
 * ClaimBookingController — let offline-booked customers claim their account.
 *
 * Flow: phone -> OTP -> verify + set password -> link bookings by phone ->
 * referral backfill from booking associate -> login.
 * All referral payouts stay prospective (future recordPayment calls pay the
 * 2%; nothing is back-paid for past installments).
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Services\OTPService;

class ClaimBookingController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function showClaimForm()
    {
        @session_start();
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/user/dashboard');
            exit;
        }
        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['claim_error'] ?? null;
        $success = $_SESSION['claim_success'] ?? null;
        unset($_SESSION['claim_error'], $_SESSION['claim_success']);
        $base = BASE_URL;
        extract(compact('csrf_token', 'error', 'success', 'base'));
        include __DIR__ . '/../../../views/auth/claim_booking.php';
    }

    public function sendClaimOtp()
    {
        @session_start();
        $phone = preg_replace('/\D/', '', (string)($_POST['phone'] ?? ''));
        if (!preg_match('/^[0-9]{10}$/', $phone)) {
            $_SESSION['claim_error'] = 'Please enter a valid 10-digit phone number.';
            header('Location: ' . BASE_URL . '/auth/claim-booking');
            exit;
        }

        try {
            $db = Database::getInstance();
            // Candidate bookings: same phone on booking, or owned by a same-phone user.
            $user = $db->fetchOne("SELECT id FROM users WHERE phone = ? LIMIT 1", [$phone]);
            $byPhone = (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM plot_bookings WHERE customer_phone = ? AND status NOT IN ('cancelled')",
                [$phone]
            );
            $byUser = $user ? (int)$db->fetchColumn(
                "SELECT COUNT(*) FROM plot_bookings WHERE customer_id = ? AND status NOT IN ('cancelled')",
                [(int)$user['id']]
            ) : 0;

            if ($byPhone === 0 && $byUser === 0) {
                $_SESSION['claim_error'] = 'No booking found for this phone number. Please check the number or contact our office.';
                header('Location: ' . BASE_URL . '/auth/claim-booking');
                exit;
            }

            $otpService = new OTPService();
            $result = $otpService->sendOTP($phone, 'sms', 'claim');
            if (empty($result['success'])) {
                $_SESSION['claim_error'] = $result['message'] ?? 'Failed to send OTP. Please try again.';
                header('Location: ' . BASE_URL . '/auth/claim-booking');
                exit;
            }

            $_SESSION['claim_phone'] = $phone;
            $_SESSION['claim_success'] = 'OTP sent to your phone. It expires in 10 minutes.';
            header('Location: ' . BASE_URL . '/auth/claim-booking/verify');
            exit;
        } catch (\Throwable $e) {
            error_log('ClaimBookingController::sendClaimOtp: ' . $e->getMessage());
            $_SESSION['claim_error'] = 'Something went wrong. Please try again.';
            header('Location: ' . BASE_URL . '/auth/claim-booking');
            exit;
        }
    }

    public function showVerifyForm()
    {
        @session_start();
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/user/dashboard');
            exit;
        }
        if (empty($_SESSION['claim_phone'])) {
            header('Location: ' . BASE_URL . '/auth/claim-booking');
            exit;
        }
        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['claim_error'] ?? null;
        $success = $_SESSION['claim_success'] ?? null;
        unset($_SESSION['claim_error'], $_SESSION['claim_success']);
        $phone = $_SESSION['claim_phone'];
        $base = BASE_URL;
        extract(compact('csrf_token', 'error', 'success', 'phone', 'base'));
        include __DIR__ . '/../../../views/auth/claim_verify.php';
    }

    public function verifyAndClaim()
    {
        @session_start();
        $phone = $_SESSION['claim_phone'] ?? '';
        $otp = trim((string)($_POST['otp'] ?? ''));
        $name = trim((string)($_POST['name'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');

        $fail = function ($msg) {
            $_SESSION['claim_error'] = $msg;
            header('Location: ' . BASE_URL . '/auth/claim-booking/verify');
            exit;
        };

        if (empty($phone) || empty($otp)) {
            $fail('Please enter the OTP.');
        }
        if (strlen($password) < 6) {
            $fail('Password must be at least 6 characters.');
        }
        if ($password !== $confirm) {
            $fail('Passwords do not match.');
        }

        try {
            $otpService = new OTPService();
            $check = $otpService->verifyOTP($phone, $otp, 'claim');
            if (empty($check['success'])) {
                $fail($check['message'] ?? 'Invalid OTP.');
            }

            $db = Database::getInstance();
            $tid = 1;
            try { $tid = \App\Core\Middleware\TenantContext::getId(); } catch (\Throwable $e) { error_log('ClaimBooking tenant: ' . $e->getMessage()); }
            $tSql = $tid > 1 ? ' AND tenant_id = ?' : '';
            $tParams = $tid > 1 ? [$tid] : [];

            // 1. Find or create the user by phone.
            $user = $db->fetchOne("SELECT * FROM users WHERE phone = ?{$tSql} LIMIT 1", array_merge([$phone], $tParams));
            if (!$user) {
                $finalName = $name !== '' ? $name : 'Customer';
                $refCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $finalName), 0, 3) ?: 'USR') . date('ymd') . random_int(100, 999);
                $userId = $db->insert('users', array_merge([
                    'customer_id' => 'CUS' . date('Y') . str_pad((string)random_int(1, 9999), 4, '0', STR_PAD_LEFT),
                    'name' => $finalName,
                    'email' => $phone . '@claim.apsdreamhome.com',
                    'phone' => $phone,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'referral_code' => $refCode,
                    'role' => 'customer',
                    'status' => 'active',
                    'registration_status' => 'approved',
                    'registration_method' => 'claim_booking',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ], $tid > 1 ? ['tenant_id' => $tid] : []));
                try {
                    (new \App\Services\WalletService())->ensureWallet((int)$userId);
                } catch (\Throwable $e) { error_log('ClaimBooking wallet: ' . $e->getMessage()); }
                $user = $db->fetchOne("SELECT * FROM users WHERE id = ? LIMIT 1", [(int)$userId]);
            } else {
                // Existing (possibly stub) user: set/refresh password + name if provided.
                $upd = ['password' => password_hash($password, PASSWORD_DEFAULT)];
                if ($name !== '') $upd['name'] = $name;
                $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($upd)));
                $db->query("UPDATE users SET {$set}, updated_at = NOW() WHERE id = ?{$tSql}", array_merge(array_values($upd), [(int)$user['id']], $tParams));
                $user = $db->fetchOne("SELECT * FROM users WHERE id = ? LIMIT 1", [(int)$user['id']]);
                try {
                    (new \App\Services\WalletService())->ensureWallet((int)$user['id']);
                } catch (\Throwable $e) { error_log('ClaimBooking wallet: ' . $e->getMessage()); }
            }
            $userId = (int)$user['id'];

            // 2. Link bookings: same phone, whose current owner is missing or same-phone.
            $linked = 0;
            $bookings = $db->fetchAll(
                "SELECT pb.id, pb.customer_id, pb.associate_id, u.phone AS owner_phone
                 FROM plot_bookings pb LEFT JOIN users u ON u.id = pb.customer_id
                 WHERE pb.customer_phone = ? AND pb.status NOT IN ('cancelled'){$tSql}",
                array_merge([$phone], $tParams)
            ) ?: [];
            $claimedAssociateIds = [];
            foreach ($bookings as $b) {
                $ownerPhone = preg_replace('/\D/', '', (string)($b['owner_phone'] ?? ''));
                if ($b['customer_id'] && $ownerPhone !== '' && $ownerPhone !== $phone) {
                    continue; // belongs to someone else; leave it
                }
                $db->query("UPDATE plot_bookings SET customer_id = ? WHERE id = ?", [$userId, (int)$b['id']]);
                $linked++;
                if (!empty($b['associate_id'])) $claimedAssociateIds[(int)$b['associate_id']] = true;
            }

            // 3. Referral backfill: booking associate -> user sponsor (only if empty).
            // Future recordPayment() calls then pay the 2% via the normal pipeline.
            if (empty($user['referred_by']) && !empty($claimedAssociateIds)) {
                $assocId = (int)array_key_first($claimedAssociateIds);
                $assoc = $db->fetchOne("SELECT user_id FROM associates WHERE id = ? LIMIT 1", [$assocId]);
                $sponsorUserId = (int)($assoc['user_id'] ?? 0);
                if ($sponsorUserId > 0 && $sponsorUserId !== $userId) {
                    $db->query("UPDATE users SET referred_by = ? WHERE id = ? AND (referred_by IS NULL OR referred_by = 0)", [$sponsorUserId, $userId]);
                }
            }

            // 4. Log in.
            unset($_SESSION['claim_phone']);
            $_SESSION['user_id'] = $userId;
            $_SESSION['customer_id'] = $user['customer_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? 'customer';
            $_SESSION['logged_in'] = true;
            $_SESSION['success'] = $linked > 0
                ? "Welcome! {$linked} booking(s) linked to your account."
                : 'Welcome! Your account is ready.';

            header('Location: ' . BASE_URL . '/user/dashboard');
            exit;
        } catch (\Throwable $e) {
            error_log('ClaimBookingController::verifyAndClaim: ' . $e->getMessage());
            $fail('Something went wrong. Please try again.');
        }
    }
}
