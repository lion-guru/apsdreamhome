<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\PayrollService;

class PayrollController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PayrollService();
    }

    public function index()
    {
        $this->requireAdmin();
        $filters = [
            'status' => $_GET['status'] ?? null,
            'from_date' => $_GET['from_date'] ?? null,
            'to_date' => $_GET['to_date'] ?? null,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $runs = $this->service->getRuns($filters);
        $stats = $this->service->getStats();

        return $this->render('admin/payroll/index', [
            'page_title' => 'Payroll Management',
            'runs' => $runs,
            'stats' => $stats,
            'filters' => $_GET,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/payroll/create', [
            'page_title' => 'Create Payroll Run',
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = [
            'period_start' => $_POST['period_start'],
            'period_end' => $_POST['period_end'],
            'pay_date' => $_POST['pay_date'],
            'notes' => $_POST['notes'] ?? null,
        ];
        $result = $this->service->createRun($data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $run = $this->service->getRun($id);
        if (!$run) {
            $this->setFlash('error', 'Payroll run not found');
            $this->redirect(BASE_URL . '/admin/payroll');
        }
        return $this->render('admin/payroll/show', [
            'page_title' => 'Payroll Run: ' . $run['run_number'],
            'run' => $run,
        ]);
    }

    public function calculate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->calculateRun($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll/' . $id);
    }

    public function approve($id)
    {
        $this->requireAdmin();
        $result = $this->service->approveRun($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll/' . $id);
    }

    public function process($id)
    {
        $this->requireAdmin();
        $result = $this->service->processRun($id);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/payroll/' . $id);
    }

    public function stats()
    {
        $this->requireAdmin();
        return $this->jsonResponse($this->service->getStats());
    }

    public function statutoryReturns()
    {
        $this->requireAdmin();
        $filters = [
            'return_type' => $_GET['return_type'] ?? null,
            'status' => $_GET['status'] ?? null,
            'period_year' => $_GET['period_year'] ?? null,
            'period_month' => $_GET['period_month'] ?? null,
        ];
        $returns = $this->service->getStatutoryReturns($filters);
        return $this->render('admin/payroll/statutory_returns', [
            'page_title' => 'Statutory Returns',
            'returns' => $returns,
            'filters' => $filters,
        ]);
    }

    public function fileStatutoryReturn($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->markReturnFiled($id, $_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/statutory-returns');
    }
}