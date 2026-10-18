<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\InterCompanyService;
use Exception;

class InterCompanyController extends AdminController
{
    protected $icService;

    public function __construct()
    {
        parent::__construct();
        $this->icService = new InterCompanyService();
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? '';
            $fromDate = $_GET['from_date'] ?? '';
            $toDate = $_GET['to_date'] ?? '';
            $fromEntity = (int)($_GET['from_entity'] ?? 0);
            $toEntity = (int)($_GET['to_entity'] ?? 0);
            $transactions = $this->icService->getAllTransactions(
                $status ?: null,
                $fromDate ?: null,
                $toDate ?: null,
                $fromEntity,
                $toEntity
            );
            $entities = $this->icService->getEntities();
            $stats = $this->icService->getStats();
            return $this->render('admin/inter_company/index', [
                'page_title' => 'Inter-Company Transactions - APS Dream Home',
                'active_page' => 'inter_company',
                'transactions' => $transactions,
                'entities' => $entities,
                'stats' => $stats,
                'filters' => ['status' => $status, 'from_date' => $fromDate, 'to_date' => $toDate, 'from_entity' => $fromEntity, 'to_entity' => $toEntity],
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load transactions: ' . $e->getMessage());
            return $this->redirect('/admin/accounting');
        }
    }

    public function create()
    {
        $this->requireAdmin();
        try {
            $entities = $this->icService->getEntities();
            return $this->render('admin/inter_company/create', [
                'page_title' => 'New Inter-Company Transaction',
                'active_page' => 'inter_company',
                'entities' => $entities,
                'today' => date('Y-m-d'),
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load form: ' . $e->getMessage());
            return $this->redirect('/admin/inter-company');
        }
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $required = ['transaction_date','from_entity_id','to_entity_id','transaction_type','amount'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) return $this->jsonError(ucfirst(str_replace('_',' ',$f)).' is required', 400);
            }
            if ((int)$_POST['from_entity_id'] === (int)$_POST['to_entity_id']) {
                return $this->jsonError('Source and destination entities must be different', 400);
            }
            if ((float)$_POST['amount'] <= 0) return $this->jsonError('Amount must be greater than 0', 400);
            $id = $this->icService->create($_POST);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Inter-company transaction created',
                'transaction_id' => $id,
                'redirect' => BASE_URL . '/admin/inter-company/' . $id,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $txn = $this->icService->getById((int)$id);
            if (!$txn) {
                $this->setFlash('error', 'Transaction not found');
                return $this->redirect('/admin/inter-company');
            }
            return $this->render('admin/inter_company/show', [
                'page_title' => 'Transaction #' . $txn['transaction_number'],
                'active_page' => 'inter_company',
                'txn' => $txn,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load transaction: ' . $e->getMessage());
            return $this->redirect('/admin/inter-company');
        }
    }

    public function update($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $ok = $this->icService->update((int)$id, $_POST);
            if (!$ok) return $this->jsonError('Nothing to update or transaction not in draft', 400);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Transaction updated',
                'redirect' => BASE_URL . '/admin/inter-company/' . $id,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function delete($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $ok = $this->icService->delete((int)$id);
            if (!$ok) return $this->jsonError('Transaction not found or not in draft (cannot delete)', 400);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Transaction deleted',
                'redirect' => BASE_URL . '/admin/inter-company',
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function post($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $ok = $this->icService->postTransaction((int)$id);
            if (!$ok) return $this->jsonError('Transaction not found or not in draft', 400);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Transaction posted',
                'redirect' => BASE_URL . '/admin/inter-company/' . $id,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function cancel($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $ok = $this->icService->cancelTransaction((int)$id);
            if (!$ok) return $this->jsonError('Transaction not found or cannot be cancelled', 400);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Transaction cancelled',
                'redirect' => BASE_URL . '/admin/inter-company/' . $id,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function reconciliation()
    {
        $this->requireAdmin();
        try {
            $status = $_GET['status'] ?? 'open';
            $reconciliations = $this->icService->getReconciliations($status ?: null);
            $entities = $this->icService->getEntities();
            return $this->render('admin/inter_company/reconcile', [
                'page_title' => 'Inter-Company Reconciliation',
                'active_page' => 'inter_company',
                'reconciliations' => $reconciliations,
                'entities' => $entities,
                'status' => $status,
            ]);
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to load reconciliations: ' . $e->getMessage());
            return $this->redirect('/admin/inter-company');
        }
    }

    public function createReconciliation()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $required = ['from_entity_id','to_entity_id','period_start','period_end'];
            foreach ($required as $f) {
                if (empty($_POST[$f])) return $this->jsonError(ucfirst(str_replace('_',' ',$f)).' is required', 400);
            }
            $id = $this->icService->createReconciliation($_POST);
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Reconciliation created',
                'reconciliation_id' => $id,
                'redirect' => BASE_URL . '/admin/inter-company/reconcile',
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    public function unreconciled()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->jsonError('Invalid request', 400);
        try {
            $fromEntity = (int)($_POST['from_entity_id'] ?? 0);
            $toEntity = (int)($_POST['to_entity_id'] ?? 0);
            $periodStart = $_POST['period_start'] ?? '';
            $periodEnd = $_POST['period_end'] ?? '';
            if (!$fromEntity || !$toEntity || !$periodStart || !$periodEnd) {
                return $this->jsonError('All fields required', 400);
            }
            $items = $this->icService->getUnreconciledPosted($fromEntity, $toEntity, $periodStart, $periodEnd);
            return $this->jsonResponse([
                'success' => true,
                'items' => $items,
            ]);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }
}
