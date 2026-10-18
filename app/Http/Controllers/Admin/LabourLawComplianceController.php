<?php
namespace App\Http\Controllers\Admin;

use App\Services\LabourLawComplianceService;

class LabourLawComplianceController extends AdminController
{
    private LabourLawComplianceService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new LabourLawComplianceService();
    }

    public function index()
    {
        $this->requireAdmin();
        $items = $this->service->getAll($_GET);
        $stats = $this->service->getStats();
        return $this->render('admin/labour_law_compliance/index', [
            'page_title' => 'Labour Law Compliance',
            'items' => $items,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/labour_law_compliance/create', [
            'page_title' => 'Add Labour Law Compliance',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/labour-law-compliance');
        }
        $this->validateCsrfOrFail();
        $id = $this->service->create($_POST);
        $this->setFlash('success', 'Labour law compliance saved');
        return $this->redirect('/admin/labour-law-compliance/show/' . $id);
    }

    public function show(int $id)
    {
        $this->requireAdmin();
        $item = $this->service->getById($id);
        if (!$item) {
            $this->setFlash('error', 'Record not found');
            return $this->redirect('/admin/labour-law-compliance');
        }
        return $this->render('admin/labour_law_compliance/show', [
            'page_title' => 'Labour Law Compliance Detail',
            'item' => $item,
        ]);
    }

    public function updateStatus(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/labour-law-compliance');
        }
        $this->validateCsrfOrFail();
        $item = $this->service->getById($id);
        if (!$item) {
            $this->setFlash('error', 'Record not found');
            return $this->redirect('/admin/labour-law-compliance');
        }
        $item['status'] = $_POST['status'] ?? $item['status'];
        $item['last_filed_date'] = $_POST['last_filed_date'] ?? $item['last_filed_date'];
        $item['next_due_date'] = $_POST['next_due_date'] ?? $item['next_due_date'];
        $item['penalty_amount'] = $_POST['penalty_amount'] ?? $item['penalty_amount'];
        $item['remarks'] = $_POST['remarks'] ?? $item['remarks'];
        $this->service->update($id, $item);
        $this->setFlash('success', 'Status updated');
        return $this->redirect('/admin/labour-law-compliance/show/' . $id);
    }

    public function delete(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/labour-law-compliance');
        }
        $this->validateCsrfOrFail();
        $this->service->delete($id);
        $this->setFlash('success', 'Record deleted');
        return $this->redirect('/admin/labour-law-compliance');
    }
}
