<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class KycController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT k.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM kyc_requests k
                LEFT JOIN users u ON k.user_id = u.id
                ORDER BY k.created_at DESC
            ");
            $stmt->execute();
            $requests = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $requests = [];
        }
        return $this->render('admin/kyc/index', [
            'page_title' => 'KYC Requests',
            'requests' => $requests
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
            $stmt->execute([$id]);
            $request = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $request = null;
        }
        if (!$request) {
            $this->setFlash('error', 'KYC request not found');
            $this->redirect('/admin/kyc');
        }
        return $this->render('admin/kyc/show', [
            'page_title' => 'KYC Request #' . $id,
            'request' => $request
        ]);
    }

    public function verify($id)
    {
        $this->requireAdmin();
        $status = $_POST['status'] ?? 'approved';
        $reason = $_POST['reason'] ?? '';
        try {
            $stmt = $this->db->prepare("UPDATE kyc_requests SET status = ?, verified_by = ?, verified_at = NOW(), rejection_reason = ? WHERE id = ?");
            $stmt->execute([$status, $_SESSION['admin_id'] ?? 0, $reason, $id]);
            $this->setFlash('success', 'KYC request ' . $status . ' successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to update verification: ' . $e->getMessage());
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
