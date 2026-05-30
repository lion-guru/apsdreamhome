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
                SELECT d.*, u.name as user_name, u.email as user_email
                FROM documents d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.is_kyc = 1 OR d.document_type IN ('aadhaar', 'pan', 'voter_id', 'driving_license', 'passport')
                ORDER BY d.created_at DESC
            ");
            $stmt->execute();
            $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $documents = [];
        }
        return $this->render('admin/kyc/index', [
            'page_title' => 'KYC Documents',
            'documents' => $documents
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT d.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM documents d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.id = ?
            ");
            $stmt->execute([$id]);
            $document = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $document = null;
        }
        if (!$document) {
            $this->setFlash('error', 'Document not found');
            $this->redirect('/admin/kyc');
        }
        return $this->render('admin/kyc/show', [
            'page_title' => 'KYC Document: ' . ($document['document_number'] ?? ''),
            'document' => $document
        ]);
    }

    public function verify($id)
    {
        $this->requireAdmin();
        $status = $_POST['status'] ?? 'verified';
        $notes = $_POST['notes'] ?? '';
        try {
            $stmt = $this->db->prepare("UPDATE documents SET verification_status = ?, verified_by = ?, verified_at = NOW() WHERE id = ?");
            $stmt->execute([$status, $_SESSION['admin_id'] ?? 0, $id]);
            $this->setFlash('success', 'KYC document ' . $status . ' successfully');
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
                SELECT d.*, u.name as user_name, u.email as user_email
                FROM documents d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE (d.verification_status IS NULL OR d.verification_status = 'pending')
                    AND (d.is_kyc = 1 OR d.document_type IN ('aadhaar', 'pan', 'voter_id', 'driving_license', 'passport'))
                ORDER BY d.created_at ASC
            ");
            $stmt->execute();
            $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $documents = [];
        }
        return $this->render('admin/kyc/pending', [
            'page_title' => 'Pending KYC Verifications',
            'documents' => $documents
        ]);
    }
}
