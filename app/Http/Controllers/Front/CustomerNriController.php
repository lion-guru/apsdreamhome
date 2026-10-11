<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

/**
 * NRI buyer self-service: FIRC + passport KYC upload per booking (PRO-5).
 * Routes: GET /customer/nri-desk, POST /customer/nri-desk/save
 */
class CustomerNriController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    /**
     * Ensure customer is logged in (mirrors CustomerPassbookController —
     * the Front chain has no shared customer guard).
     */
    private function requireCustomer(): void
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['', 'customer', 'user'], true)) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    public function index(): void
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        $tid = (int)$this->tenantId();

        try {
            $bookings = $this->db->fetchAll(
                "SELECT b.id, b.booking_number, p.plot_number, c.name AS colony_name
                 FROM plot_bookings b
                 LEFT JOIN plots p ON p.id = b.plot_id
                 LEFT JOIN colonies c ON c.id = p.colony_id
                 WHERE b.customer_id = ? AND b.tenant_id = ? ORDER BY b.id DESC",
                [$userId, $tid]
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[CustomerNriController::index bookings] ' . $e->getMessage());
            $bookings = [];
        }
        try {
            $records = $this->db->fetchAll(
                "SELECT n.*, b.booking_number FROM nri_investments n
                 LEFT JOIN plot_bookings b ON b.id = n.booking_id
                 WHERE n.customer_id = ? AND n.tenant_id = ? ORDER BY n.id DESC",
                [$userId, $tid]
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('[CustomerNriController::index records] ' . $e->getMessage());
            $records = [];
        }

        $this->layout = 'layouts/customer';
        $this->render('customer/nri_desk', [
            'page_title' => 'NRI Investment Desk - APS Dream Home',
            'bookings' => $bookings,
            'records' => $records,
        ]);
    }

    public function save()
    {
        $this->requireCustomer();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $this->redirect('/customer/nri-desk');
        }
        $userId = (int)$_SESSION['user_id'];
        $tid = (int)$this->tenantId();

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $country = trim((string)($_POST['country_of_residence'] ?? ''));
        $passport = trim((string)($_POST['passport_number'] ?? ''));
        $acct = strtoupper(trim((string)($_POST['bank_account_type'] ?? 'NRE')));
        if ($bookingId <= 0 || $country === '' || $passport === '') {
            $_SESSION['flash_error'] = 'Booking, country of residence and passport number are required.';
            return $this->redirect('/customer/nri-desk');
        }
        if (!in_array($acct, ['NRE', 'NRO', 'FCNR'], true)) {
            $acct = 'NRE';
        }
        // Booking must belong to this customer.
        try {
            $own = $this->db->fetchOne(
                "SELECT id FROM plot_bookings WHERE id = ? AND customer_id = ? AND tenant_id = ? LIMIT 1",
                [$bookingId, $userId, $tid]
            );
            if (!$own) {
                $_SESSION['flash_error'] = 'Invalid booking selected.';
                return $this->redirect('/customer/nri-desk');
            }
        } catch (\Throwable $e) {
            error_log('[CustomerNriController::save own-check] ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Could not verify booking. Try again.';
            return $this->redirect('/customer/nri-desk');
        }

        // Optional docs (FIRC + passport scan): validated, 5MB, stored by path.
        // __DIR__ is app/Http/Controllers/Front → 4 levels up is webroot.
        $uploadDir = dirname(__DIR__, 4) . '/assets/uploads/nri/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $paths = [];
        foreach (['firc_doc' => 'firc_doc_path', 'passport_doc' => 'passport_doc_path'] as $field => $col) {
            if (!isset($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                continue;
            }
            $v = \UploadValidator::validate($_FILES[$field], ['types' => 'documents', 'max_size' => 5]);
            if (empty($v['valid'])) {
                $_SESSION['flash_error'] = ucfirst(str_replace('_', ' ', $field)) . ': ' . ($v['error'] ?? 'invalid file');
                return $this->redirect('/customer/nri-desk');
            }
            $safe = \UploadValidator::safeFilename($_FILES[$field]['name']);
            $ext = pathinfo($safe, PATHINFO_EXTENSION);
            $filename = $field . '_' . $userId . '_' . $bookingId . '_' . time() . '.' . $ext;
            if (!move_uploaded_file($_FILES[$field]['tmp_name'], $uploadDir . $filename)) {
                $_SESSION['flash_error'] = 'Failed to store ' . $field . '. Try again.';
                return $this->redirect('/customer/nri-desk');
            }
            $paths[$col] = 'assets/uploads/nri/' . $filename;
        }

        try {
            $existing = $this->db->fetchOne(
                "SELECT id, firc_doc_path, passport_doc_path FROM nri_investments WHERE booking_id = ? AND customer_id = ? AND tenant_id = ? LIMIT 1",
                [$bookingId, $userId, $tid]
            );
            $row = [
                'country_of_residence' => mb_substr($country, 0, 100),
                'passport_number' => mb_substr($passport, 0, 50),
                'visa_type' => mb_substr(trim((string)($_POST['visa_type'] ?? '')), 0, 50) ?: null,
                'bank_account_type' => $acct,
                'firc_number' => mb_substr(trim((string)($_POST['firc_number'] ?? '')), 0, 100) ?: null,
                'fema_declaration_signed' => !empty($_POST['fema_declaration_signed']) ? 1 : 0,
                'notes' => mb_substr(trim((string)($_POST['notes'] ?? '')), 0, 1000) ?: null,
                'verification_status' => 'pending',
                'verified_by' => null,
                'verified_at' => null,
            ];
            foreach ($paths as $col => $p) {
                $row[$col] = $p;
            }
            if ($existing) {
                // Resubmission resets to pending; keep old docs when not re-uploaded.
                foreach (['firc_doc_path', 'passport_doc_path'] as $col) {
                    if (!isset($row[$col])) {
                        unset($row[$col]);
                    }
                }
                $this->db->update('nri_investments', $row, 'id = :wid', ['wid' => (int)$existing['id']]);
            } else {
                $row['tenant_id'] = $tid;
                $row['booking_id'] = $bookingId;
                $row['customer_id'] = $userId;
                $this->db->insert('nri_investments', $row);
            }
            $_SESSION['flash_success'] = 'Submitted for verification. The compliance desk will review your FIRC.';
        } catch (\Throwable $e) {
            error_log('[CustomerNriController::save] ' . $e->getMessage());
            $_SESSION['flash_error'] = 'Submission failed. Try again.';
        }
        return $this->redirect('/customer/nri-desk');
    }
}
