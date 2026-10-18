<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\RecurringTransactionService;
use Exception;

class RecurringTransactionController extends AdminController
{
    protected $rtService;

    public function __construct()
    {
        parent::__construct();
        $this->rtService = new RecurringTransactionService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $transactions = $this->rtService->getAll($status ?: null);
            $stats = $this->rtService->getStats();
            return $this->render('admin/recurring/index', [
                'page_title' => 'Recurring Journal Entries - APS Dream Home',
                'active_page' => 'recurring',
                'transactions' => $transactions,
                'stats' => $stats,
                'filters' => ['status' => $status],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/recurring/create', [
            'page_title' => 'Add Recurring Transaction',
            'active_page' => 'recurring',
            'types' => $this->rtService->getTransactionTypes(),
            'frequencies' => $this->rtService->getFrequencies(),
            'users' => $this->rtService->getUsers(),
            'vendors' => $this->rtService->getVendors(),
            'today' => date('Y-m-d'),
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $required = ['transaction_type','amount','start_date'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) return $this->jsonError(ucfirst(str_replace('_',' ',$f)).' is required', 400);
            }
            $id = $this->rtService->create($_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Created','rt_id'=>$id,'redirect'=>BASE_URL.'/admin/recurring']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $txn = $this->rtService->getById((int)$id);
        if (!$txn) { $this->setFlash('error','Not found'); return $this->redirect('/admin/recurring'); }
        return $this->render('admin/recurring/edit', [
            'page_title' => 'Edit Recurring Transaction',
            'active_page' => 'recurring',
            'txn' => $txn,
            'types' => $this->rtService->getTransactionTypes(),
            'frequencies' => $this->rtService->getFrequencies(),
            'users' => $this->rtService->getUsers(),
            'vendors' => $this->rtService->getVendors(),
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->rtService->update((int)$id, $_POST);
            return $this->jsonResponse(['success'=>true,'message'=>'Updated','redirect'=>BASE_URL.'/admin/recurring']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: '.$e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $this->rtService->delete((int)$id);
            return $this->jsonResponse(['success'=>true,'message'=>'Deleted']);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function execute($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $jeId = $this->rtService->execute((int)$id, $_SESSION['user_id'] ?? 1);
            return $this->jsonResponse(['success'=>true,'message'=>'Executed','je_id'=>$jeId]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }

    public function processDue()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $result = $this->rtService->processAllDue($_SESSION['user_id'] ?? 1);
            return $this->jsonResponse(['success'=>true,'message'=>"Processed {$result['executed']} of {$result['due']}",'details'=>$result]);
        } catch (Exception $e) {
            return $this->jsonError($e->getMessage(), 500);
        }
    }
}