<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use \App\Traits\TenantAwareTrait;

class KycController extends AdminController
{
    use TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        $statusFilter = $_GET['status'] ?? '';
        try {
            $sql = "
                SELECT k.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM kyc_requests k
                LEFT JOIN users u ON k.user_id = u.id
            ";
            $params = [];
            if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
                $sql .= " WHERE k.status = ?";
                $params[] = $statusFilter;
            }
            $sql .= " ORDER BY k.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $requests = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $requests = [];
        }

        $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        try {
            $stmt = $this->db->query("SELECT status, COUNT(*) as cnt FROM kyc_requests GROUP BY status");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $stats[$row['status']] = (int)$row['cnt'];
                $stats['total'] += (int)$row['cnt'];
            }
        } catch (\Exception $e) { error_log("KycController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $this->render('admin/kyc/index', [
            'page_title' => 'KYC Requests',
            'requests' => $requests,
            'stats' => $stats,
            'currentFilter' => $statusFilter,
            'nsdlConfigured' => !empty($_ENV['NSDL_API_KEY']),
            'nsdlTestMode' => ($_ENV['NSDL_TEST_MODE'] ?? 'true') === 'true',
            'uidaiConfigured' => !empty($_ENV['UIDAI_API_KEY']),
            'uidaiTestMode' => ($_ENV['UIDAI_TEST_MODE'] ?? 'true') === 'true',
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT k.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM kyc_requests k
                LEFT JOIN users u ON k.user_id = u.id
                WHERE k.id = ?
            ");
            $stmt->execute([(int)$id]);
            $request = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $request = null;
        }
        if (!$request) {
            $this->setFlash('error', 'KYC request not found');
            $this->redirect('/admin/kyc');
            return;
        }
        return $this->render('admin/kyc/show', [
            'page_title' => 'KYC Request #' . $id,
            'request' => $request
        ]);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        try {
            $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
            $stmt = $this->db->prepare("
                UPDATE kyc_requests 
                SET status = 'approved', verified_by = ?, verified_at = NOW(), rejection_reason = NULL 
                WHERE id = ? AND status != 'approved'
            ");
            $stmt->execute([$adminId, (int)$id]);
            if ($stmt->rowCount() > 0) {
                // Sync users.kyc_status
                try {
                    $row0 = $this->db->fetchOne("SELECT user_id FROM kyc_requests WHERE id = ?", [(int)$id]);
                    if (!empty($row0['user_id'])) {
                        $tid = (int)$this->tenantId();
                        $this->db->execute("UPDATE users SET kyc_status = 'verified' WHERE id = ? AND tenant_id = ?", [(int)$row0['user_id'], $tid]);
                    }
                } catch (\Throwable $e) {
                    error_log("[KycController] kyc_status sync failed: " . $e->getMessage());
                }
                $this->setFlash('success', 'KYC approved successfully');
                // Send approval email
                try {
                    $row = $this->db->fetchOne("SELECT user_id FROM kyc_requests WHERE id = ?", [(int)$id]);
                    if (!empty($row['user_id'])) {
                        $emailSvc = new \App\Services\EmailTemplateService();
                        $pan = $this->db->fetchOne("SELECT pan_number FROM kyc_requests WHERE id = ?", [(int)$id]);
                        $emailSvc->sendKycApproved((int)$row['user_id'], [
                            'pan_number' => $pan['pan_number'] ?? 'XXXXX1234X',
                        ]);
                    }
                } catch (\Throwable $e) {
                    error_log("[KycController] approve email failed: " . $e->getMessage());
                }
                // Send in-app notification
                try {
                    $row2 = $this->db->fetchOne("SELECT user_id FROM kyc_requests WHERE id = ?", [(int)$id]);
                    if (!empty($row2['user_id'])) {
                        $notifSvc = new \App\Services\Communication\NotificationService();
                        $notifSvc->sendNotification((int)$row2['user_id'], 'in_app', 'KYC Approved',
                            'Your KYC has been verified and approved. You can now access all features.',
                            ['event_type' => 'kyc', 'action_url' => '/user/dashboard']
                        );
                    }
                } catch (\Throwable $e) {
                    error_log("[KycController] approve notification failed: " . $e->getMessage());
                }
            } else {
                $this->setFlash('warning', 'KYC request was already processed');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to approve: ' . $e->getMessage());
        }
        $this->redirect('/admin/kyc');
    }

    public function reject($id)
    {
        $this->requireAdmin();
        $reason = trim($_POST['rejection_reason'] ?? '');
        if (empty($reason)) {
            $this->setFlash('error', 'Rejection reason is required');
            $this->redirect('/admin/kyc/' . (int)$id);
            return;
        }
        try {
            $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
            $stmt = $this->db->prepare("
                UPDATE kyc_requests 
                SET status = 'rejected', verified_by = ?, verified_at = NOW(), rejection_reason = ? 
                WHERE id = ? AND status != 'rejected'
            ");
            $stmt->execute([$adminId, $reason, (int)$id]);
            if ($stmt->rowCount() > 0) {
                // Sync users.kyc_status
                try {
                    $row0 = $this->db->fetchOne("SELECT user_id FROM kyc_requests WHERE id = ?", [(int)$id]);
                    if (!empty($row0['user_id'])) {
                        $tid = (int)$this->tenantId();
                        $this->db->execute("UPDATE users SET kyc_status = 'rejected' WHERE id = ? AND tenant_id = ?", [(int)$row0['user_id'], $tid]);
                    }
                } catch (\Throwable $e) {
                    error_log("[KycController] kyc_status sync failed: " . $e->getMessage());
                }
                $this->setFlash('success', 'KYC rejected');
                // Send rejection email
                try {
                    $row = $this->db->fetchOne("SELECT user_id FROM kyc_requests WHERE id = ?", [(int)$id]);
                    if (!empty($row['user_id'])) {
                        $emailSvc = new \App\Services\EmailTemplateService();
                        $emailSvc->sendKycRejected((int)$row['user_id'], [
                            'rejection_reason' => $reason,
                        ]);
                    }
                } catch (\Throwable $e) {
                    error_log("[KycController] reject email failed: " . $e->getMessage());
                }
                // Send in-app notification
                try {
                    $row2 = $this->db->fetchOne("SELECT user_id FROM kyc_requests WHERE id = ?", [(int)$id]);
                    if (!empty($row2['user_id'])) {
                        $notifSvc = new \App\Services\Communication\NotificationService();
                        $notifSvc->sendNotification((int)$row2['user_id'], 'in_app', 'KYC Rejected',
                            'Your KYC was not approved. Reason: ' . $reason . '. Please resubmit with correct documents.',
                            ['event_type' => 'kyc', 'action_url' => '/user/kyc']
                        );
                    }
        } catch (\Throwable $e) {
            error_log("[KycController] reject notification failed: " . $e->getMessage());
        }
            } else {
                $this->setFlash('warning', 'KYC request was already processed');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to reject: ' . $e->getMessage());
        }

        // Create department request for rejected KYC (LEGAL escalation for compliance review)
        try {
            $deptService = new \App\Services\DepartmentRequestService();
            $row = $this->db->fetchOne("SELECT user_id FROM kyc_requests WHERE id = ?", [(int)$id]);
            if (!empty($row['user_id'])) {
                $userStmt = $this->db->prepare("SELECT name, email, phone FROM users WHERE id = ?");
                $userStmt->execute([(int)$row['user_id']]);
                $user = $userStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
                $deptService->submitRequest([
                    'request_type' => 'escalation',
                    'department_code' => 'LEGAL',
                    'title' => 'KYC Rejected - Compliance Review Needed',
                    'description' => "KYC request #{$id} for user " . ($user['name'] ?? 'Unknown') . " was rejected. Reason: {$reason}. Requires legal compliance review.",
                    'priority' => $reason !== '' ? 'high' : 'medium',
                    'requester_id' => $adminId,
                    'requester_role' => 'admin',
                    'requester_name' => 'Admin',
                    'related_entity_type' => 'kyc_request',
                    'related_entity_id' => $id,
                ]);
            }
        } catch (\Exception $e) {
            error_log("[KycController] department request error: " . $e->getMessage());
        }

        $this->redirect('/admin/kyc');
    }

    public function pending()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT k.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM kyc_requests k
                LEFT JOIN users u ON k.user_id = u.id
                WHERE k.status = 'pending'
                ORDER BY k.created_at ASC
            ");
            $stmt->execute();
            $requests = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $requests = [];
        }
        return $this->render('admin/kyc/pending', [
            'page_title' => 'Pending KYC Verifications',
            'requests' => $requests
        ]);
    }

    /**
     * Verify KYC via API (NSDL PAN + UIDAI Aadhaar)
     * POST /admin/kyc/{id}/verify
     */
    public function verify($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT * FROM kyc_requests WHERE id = ?");
            $stmt->execute([(int)$id]);
            $request = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$request) {
                $this->setFlash('error', 'KYC request not found');
                $this->redirect('/admin/kyc');
                return;
            }

            $kycService = new \App\Services\KYCService();
            $results = [];

            // Verify PAN
            $panResult = $kycService->verifyPAN($request['pan_number'], $request['legal_name']);
            $results['pan'] = $panResult;

            // Verify Aadhaar (Verhoeff checksum)
            $aadhaarResult = $kycService->verifyAadhaar($request['aadhaar_number'], $request['legal_name'], $request['dob'] ?? null);
            $results['aadhaar'] = $aadhaarResult;

            // Determine overall status
            $panOk = $panResult['success'] ?? false;
            $aadhaarOk = $aadhaarResult['success'] ?? false;

            if ($panOk && $aadhaarOk) {
                $this->setFlash('success', 'PAN and Aadhaar both verified successfully');
            } else {
                $failed = [];
                if (!$panOk) $failed[] = 'PAN: ' . ($panResult['message'] ?? 'failed');
                if (!$aadhaarOk) $failed[] = 'Aadhaar: ' . ($aadhaarResult['message'] ?? 'failed');
                $this->setFlash('warning', 'Verification issues: ' . implode('; ', $failed));
            }

            // Store results in session for the show page to display
            $_SESSION['kyc_verify_results'] = $results;
        } catch (\Exception $e) {
            $this->setFlash('error', 'Verification error: ' . $e->getMessage());
        }
        $this->redirect('/admin/kyc/' . (int)$id);
    }

    /**
     * View KYC verification logs
     * GET /admin/kyc/logs
     */
    public function logs()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->query("
                SELECT * FROM kyc_verification_logs ORDER BY created_at DESC LIMIT 100
            ");
            $logs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $logs = [];
        }
        return $this->render('admin/kyc/logs', [
            'page_title' => 'KYC Verification Logs',
            'logs' => $logs
        ]);
    }
}
