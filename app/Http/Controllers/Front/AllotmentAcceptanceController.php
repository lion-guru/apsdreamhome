<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\OTPService;

/**
 * Allotment Letter OTP Acceptance
 * Customer accepts allotment letter via OTP verification
 */
class AllotmentAcceptanceController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    protected $otpService;

    public function __construct()
    {
        parent::__construct();
        $this->otpService = new \App\Services\OTPService();
    }

    private function requireCustomer()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'customer') {
            $_SESSION['error'] = 'Please login as a customer to access this page';
            $this->redirect('/login');
        }
    }

    /**
     * Show allotment letter with OTP acceptance form
     * GET /customer/allotment/{id}/accept
     */
    public function show($id)
    {
        $this->requireCustomer();
        $userId = (int)$_SESSION['user_id'];
        $tid = $this->tenantId();
        $allotmentId = (int)$id;

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tSql = $tid > 1 ? " AND ad.tenant_id = ?" : "";
            $params = [$allotmentId];
            if ($tid > 1) $params[] = $tid;

            $allotment = $db->fetchOne("
                SELECT ad.*, c.name as colony_name, p.plot_number, u.name as customer_name
                FROM allotment_documents ad
                LEFT JOIN colonies c ON ad.colony_id = c.id
                LEFT JOIN plots p ON ad.plot_id = p.id
                LEFT JOIN users u ON ad.customer_id = u.id
                WHERE ad.id = ? AND ad.customer_id = ? " . ($tid > 1 ? " AND ad.tenant_id = ?" : "") . "
                LIMIT 1", array_merge([$allotmentId, $_SESSION['user_id']], $tid > 1 ? [$tid] : [])
            );

            if (!$allotment) {
                $_SESSION['error'] = 'Allotment letter not found';
                return $this->redirect('/customer/documents');
            }

            // Check if already accepted
            $acceptance = $this->db->fetchOne("
                SELECT * FROM allotment_acceptances 
                WHERE allotment_document_id = ? AND customer_id = ?" . ($this->tenantId() > 1 ? " AND tenant_id = ?" : ""),
                array_merge([$allotmentId, $_SESSION['user_id']], $this->tenantId() > 1 ? [$this->tenantId()] : [])
            );

            $base = defined('BASE_URL') ? BASE_URL : '';

            $this->render('customer/allotment_accept', [
                'page_title' => 'Accept Allotment Letter',
                'allotment' => $allotment,
                'acceptance' => $acceptance,
                'base' => $base,
            ], 'layouts/customer');
        } catch (\Throwable $e) {
            error_log('AllotmentAcceptanceController::show: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to load allotment letter';
            $this->redirect('/customer/documents');
        }
    }

    /**
     * Send OTP for allotment acceptance
     * POST /customer/allotment/{id}/send-otp
     */
    public function sendOtp($id)
    {
        $this->requireCustomer();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->jsonResponse(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $allotmentId = (int)$id;
        $userId = $_SESSION['user_id'];

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->tenantId();
            $tSql = $tid > 1 ? " AND ad.tenant_id = ?" : "";
            $params = [$id, $userId];
            if ($tid > 1) $params[] = $tid;

            $allotment = $db->fetchOne("
                SELECT ad.*, u.email, u.phone, u.name
                FROM allotment_documents ad
                LEFT JOIN users u ON ad.customer_id = u.id
                WHERE ad.id = ? AND ad.customer_id = ?" . $tSql . "
                LIMIT 1", $params
            );

            if (!$allotment) {
                $this->jsonResponse(['success' => false, 'error' => 'Allotment not found'], 404);
                return;
            }

            // Check if already accepted
            $acceptance = $db->fetchOne("
                SELECT id FROM allotment_acceptances 
                WHERE allotment_document_id = ? AND customer_id = ? AND status = 'accepted'
                " . ($this->tenantId() > 1 ? " AND tenant_id = ?" : ""),
                array_merge([$allotmentId, $this->tenantId()], $tid > 1 ? [$tid] : [])
            );
            if ($acceptance) {
                $this->jsonResponse(['success' => false, 'error' => 'Already accepted'], 400);
                return;
            }

            $phone = $allotment['phone'] ?? '';
            $email = $allotment['email'] ?? '';
            $name = $allotment['name'] ?? '';

            if (!$phone && !$email) {
                $this->jsonResponse(['success' => false, 'error' => 'No contact information available for OTP'], 400);
                return;
            }

            // Try SMS first, then email
            $sent = false;
            $channel = '';
            if ($phone) {
                $result = $this->otpService->sendOTP($phone, 'sms', 'allotment_acceptance');
                if ($result['success']) {
                    $sent = true;
                    $channel = 'sms';
                }
            }
            if (!$sent && $email) {
                $result = $this->otpService->sendOTP($email, 'email', 'allotment_acceptance');
                if ($result['success']) {
                    $sent = true;
                    $channel = 'email';
                }
            }

            if (!$sent) {
                $this->jsonResponse(['success' => false, 'error' => 'Failed to send OTP'], 500);
                return;
            }

            $this->jsonResponse([
                'success' => true,
                'message' => "OTP sent via {$channel}",
                'channel' => $channel
            ]);
        } catch (\Throwable $e) {
            error_log('AllotmentAcceptanceController::sendOtp: ' . $e->getMessage());
            $this->jsonResponse(['success' => false, 'error' => 'Failed to send OTP'], 500);
        }
    }

    /**
     * Verify OTP and accept allotment
     * POST /customer/allotment/{id}/accept
     */
    public function accept($id)
    {
        $this->requireCustomer();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $_SESSION['error'] = 'Invalid CSRF token';
            $this->redirect('/customer/allotment/' . $id . '/accept');
            return;
        }
        $allotmentId = (int)$id;
        $userId = $_SESSION['user_id'];
        $tid = $this->tenantId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$allotmentId, $tid];

            $allotment = $db->fetchOne("
                SELECT ad.*, u.email, u.phone, u.name
                FROM allotment_documents ad
                LEFT JOIN users u ON ad.customer_id = u.id
                WHERE ad.id = ? AND ad.customer_id = ? " . ($tid > 1 ? " AND ad.tenant_id = ?" : "") . "
                LIMIT 1", array_merge([$allotmentId, $_SESSION['user_id']], $tid > 1 ? [$tid] : [])
            );

            if (!$allotment) {
                $_SESSION['error'] = 'Allotment not found';
                return $this->redirect('/customer/documents');
            }

            // Check if already accepted
            $existing = $db->fetchOne("
                SELECT id FROM allotment_acceptances 
                WHERE allotment_document_id = ? AND customer_id = ? AND status = 'accepted'
                " . ($tid > 1 ? " AND tenant_id = ?" : ""),
                array_merge([$allotmentId, $userId], $tid > 1 ? [$tid] : [])
            );
            if ($existing) {
                $_SESSION['success'] = 'Allotment already accepted';
                return $this->redirect('/customer/documents');
            }

            $otpCode = trim((string)($_POST['otp_code'] ?? ''));
            if ($otpCode === '') {
                $_SESSION['error'] = 'OTP code is required';
                return $this->redirect('/customer/allotment/' . $allotmentId . '/accept');
            }

            // Verify OTP
            $phone = $allotment['phone'] ?? '';
            $email = $allotment['email'] ?? '';
            $verified = false;
            $channel = '';

            if ($phone) {
                $result = $this->otpService->verifyOTP($phone, $otpCode, 'allotment_acceptance');
                if ($result['success']) {
                    $verified = true;
                    $channel = 'sms';
                }
            }
            if (!$verified && $email) {
                $result = $this->otpService->verifyOTP($email, $otpCode, 'allotment_acceptance');
                if ($result['success']) {
                    $verified = true;
                    $channel = 'email';
                }
            }

            if (!$verified) {
                $_SESSION['error'] = 'Invalid or expired OTP';
                return $this->redirect('/customer/allotment/' . $allotmentId . '/accept');
            }

            // Record acceptance
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $tid = $this->tenantId();

            $db->execute(
                "INSERT INTO allotment_acceptances (tenant_id, allotment_document_id, customer_id, otp_code, status, accepted_at, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, 'accepted', NOW(), ?, ?, NOW())",
                [$tid, $allotmentId, $_SESSION['user_id'], $otpCode, $ip, $ua]
            );

            // Log communication
            try {
                $this->db->insert('customer_communication_log', [
                    'user_id' => $userId,
                    'channel' => 'email',
                    'direction' => 'inbound',
                    'subject' => 'Allotment Letter Accepted',
                    'message' => 'Allotment letter accepted via OTP (' . $channel . ')',
                    'status' => 'sent',
                    'related_entity_type' => 'allotment_acceptance',
                    'related_entity_id' => $allotmentId,
                    'sent_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $e) {
                error_log('AllotmentAcceptanceController::accept log failed: ' . $e->getMessage());
            }

            $_SESSION['success'] = 'Allotment letter accepted successfully';
            $this->redirect('/customer/documents');
        } catch (\Throwable $e) {
            error_log('AllotmentAcceptanceController::accept: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to accept allotment';
            $this->redirect('/customer/allotment/' . $allotmentId . '/accept');
        }
    }
}