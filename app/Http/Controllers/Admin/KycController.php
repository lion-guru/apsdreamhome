<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class KycController extends AdminController
{
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
        } catch (\Exception $e) {}

        return $this->render('admin/kyc/index', [
            'page_title' => 'KYC Requests',
            'requests' => $requests,
            'stats' => $stats,
            'currentFilter' => $statusFilter
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
            } else {
                $this->setFlash('warning', 'KYC request was already processed');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to reject: ' . $e->getMessage());
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
}
