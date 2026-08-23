<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\DocumentEsignService;

class DocumentEsignController extends AdminController
{
    protected $db;
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
        $this->service = new DocumentEsignService();
    }

    private function enrichNames(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }
        $ids = [];
        foreach ($rows as $r) {
            foreach (['created_by', 'signed_by', 'cancelled_by'] as $k) {
                if (!empty($r[$k])) {
                    $ids[(int)$r[$k]] = true;
                }
            }
        }
        $names = [];
        if (!empty($ids)) {
            try {
                $in = implode(',', array_keys($ids));
                foreach ($this->db->fetchAll("SELECT id, name FROM users WHERE id IN ($in)") ?: [] as $u) {
                    $names[(int)$u['id']] = $u['name'];
                }
            } catch (\Exception $e) {
                error_log('[DocumentEsignController::enrichNames] ' . $e->getMessage());
            }
        }
        foreach ($rows as &$r) {
            $r['created_by_name'] = $names[(int)($r['created_by'] ?? 0)] ?? null;
            $r['signed_by_name'] = $names[(int)($r['signed_by'] ?? 0)] ?? null;
        }
        unset($r);
        return $rows;
    }

    public function index()
    {
        $this->requireAdmin();
        $tid = (int)\App\Core\Middleware\TenantContext::getId();
        try {
            $documents = $this->service->getDocumentsByTenant($tid);
        } catch (\Exception $e) {
            error_log('[DocumentEsignController::index] ' . $e->getMessage());
            $documents = [];
        }

        return $this->render('admin/document_esign/index', [
            'page_title' => 'Document E-Sign',
            'documents' => $this->enrichNames($documents),
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        $document = $this->service->getDocumentById((int)$id);
        if (!$document) {
            http_response_code(404);
            return $this->render('errors/404', ['page_title' => 'Not Found']);
        }
        [$document] = $this->enrichNames([$document]);

        return $this->render('admin/document_esign/show', [
            'page_title' => 'Document E-Sign',
            'document' => $document,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/document-esign');
        }
        $result = $this->service->createDocument([
            'document_type' => trim($_POST['document_type'] ?? 'other'),
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'status' => 'pending',
            'created_by' => (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1),
        ]);

        return $result['success']
            ? $this->redirect('/admin/document-esign/' . (int)$result['id'])
            : $this->redirect('/admin/document-esign');
    }

    public function sign($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/document-esign');
        }
        $this->service->signDocument((int)$id, [
            'signature_data' => trim($_POST['signature_data'] ?? '') ?: 'admin-unsigned-placeholder',
            'signed_by' => (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1),
        ]);
        return $this->redirect('/admin/document-esign/' . (int)$id);
    }

    public function cancel($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/document-esign');
        }
        $this->service->cancelDocument((int)$id, (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 1));
        return $this->redirect('/admin/document-esign');
    }
}
