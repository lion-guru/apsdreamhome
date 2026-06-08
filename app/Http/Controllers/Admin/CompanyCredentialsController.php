<?php

namespace App\Http\Controllers\Admin;

use App\Services\CompanyCredentialsService;

class CompanyCredentialsController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new CompanyCredentialsService($this->db);
    }

    public function index()
    {
        $this->requireAdmin();
        $credentials = $this->service->getAll();
        $stats = $this->service->getDashboardStats();
        $expiring = $this->service->getExpiringSoon(30);

        $grouped = [];
        foreach ($credentials as $cred) {
            $grouped[$cred['credential_type']][] = $cred;
        }

        return $this->render('admin/company-credentials/index', [
            'page_title'   => 'Company Credentials',
            'page_heading' => 'Company Credentials',
            'credentials'  => $credentials,
            'grouped'      => $grouped,
            'stats'        => $stats,
            'expiring'     => $expiring,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/company-credentials/form', [
            'page_title'   => 'Add Credential',
            'page_heading' => 'Add New Credential',
            'credential'   => null,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/company-credentials/create');
            return;
        }

        $data = $this->getPostData();
        if (empty($data['credential_type']) || empty($data['credential_label']) || empty($data['credential_value'])) {
            $this->setFlash('error', 'Type, label, and value are required.');
            $this->redirect('/admin/company-credentials/create');
            return;
        }

        $id = $this->service->create($data);
        if ($id > 0) {
            $this->setFlash('success', 'Credential created successfully.');
            $this->redirect('/admin/company-credentials');
        } else {
            $this->setFlash('error', 'Failed to create credential.');
            $this->redirect('/admin/company-credentials/create');
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        $credential = $this->service->getById((int)$id);
        if (!$credential) {
            $this->setFlash('error', 'Credential not found.');
            $this->redirect('/admin/company-credentials');
            return;
        }
        return $this->render('admin/company-credentials/show', [
            'page_title'   => $credential['credential_label'],
            'page_heading' => 'Credential Detail',
            'credential'   => $credential,
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $credential = $this->service->getById((int)$id);
        if (!$credential) {
            $this->setFlash('error', 'Credential not found.');
            $this->redirect('/admin/company-credentials');
            return;
        }
        return $this->render('admin/company-credentials/form', [
            'page_title'   => 'Edit: ' . $credential['credential_label'],
            'page_heading' => 'Edit Credential',
            'credential'   => $credential,
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/company-credentials');
            return;
        }

        $data = $this->getPostData();
        if (empty($data['credential_type']) || empty($data['credential_label']) || empty($data['credential_value'])) {
            $this->setFlash('error', 'Type, label, and value are required.');
            $this->redirect('/admin/company-credentials/' . $id . '/edit');
            return;
        }

        $ok = $this->service->update((int)$id, $data);
        if ($ok) {
            $this->setFlash('success', 'Credential updated successfully.');
        } else {
            $this->setFlash('error', 'Failed to update credential.');
        }
        $this->redirect('/admin/company-credentials');
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/company-credentials');
            return;
        }

        $ok = $this->service->delete((int)$id);
        if ($ok) {
            $this->setFlash('success', 'Credential deleted.');
        } else {
            $this->setFlash('error', 'Failed to delete credential.');
        }
        $this->redirect('/admin/company-credentials');
    }

    public function expiring()
    {
        $this->requireAdmin();
        $expiring = $this->service->getExpiringSoon(90);
        return $this->render('admin/company-credentials/index', [
            'page_title'   => 'Expiring Credentials',
            'page_heading' => 'Credentials Expiring Within 90 Days',
            'credentials'  => $expiring,
            'grouped'      => [],
            'stats'        => $this->service->getDashboardStats(),
            'expiring'     => $expiring,
            'filter_expiring' => true,
        ]);
    }

    private function getPostData(): array
    {
        return [
            'credential_type'  => trim($_POST['credential_type'] ?? ''),
            'credential_label' => trim($_POST['credential_label'] ?? ''),
            'credential_value' => trim($_POST['credential_value'] ?? ''),
            'issuer'           => trim($_POST['issuer'] ?? ''),
            'issue_date'       => trim($_POST['issue_date'] ?? ''),
            'expiry_date'      => trim($_POST['expiry_date'] ?? ''),
            'document_path'    => trim($_POST['document_path'] ?? ''),
            'is_primary'       => isset($_POST['is_primary']) ? 1 : 0,
            'status'           => trim($_POST['status'] ?? 'active'),
            'notes'            => trim($_POST['notes'] ?? ''),
        ];
    }
}
