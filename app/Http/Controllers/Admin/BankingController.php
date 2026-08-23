<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class BankingController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, u.name as user_name
                FROM transactions t
                LEFT JOIN users u ON t.user_id = u.id
                ORDER BY t.created_at DESC
                LIMIT 100
            ");
            $stmt->execute();
            $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $transactions = [];
        }
        return $this->render('admin/banking/index', [
            'page_title' => 'Banking Transactions',
            'transactions' => $transactions
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, u.name as user_name, u.email as user_email
                FROM transactions t
                LEFT JOIN users u ON t.user_id = u.id
                WHERE t.id = ?
            ");
            $stmt->execute([$id]);
            $transaction = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $transaction = null;
        }
        if (!$transaction) {
            $this->setFlash('error', 'Transaction not found');
            $this->redirect('/admin/banking');
        }
        return $this->render('admin/banking/show', [
            'page_title' => 'Transaction #' . $id,
            'transaction' => $transaction
        ]);
    }

    public function reconciliation()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT t.*, u.name as user_name
                FROM transactions t
                LEFT JOIN users u ON t.user_id = u.id
                WHERE t.reconciliation_status IS NULL OR t.reconciliation_status = 'pending'
                ORDER BY t.created_at DESC
                LIMIT 100
            ");
            $stmt->execute();
            $transactions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $transactions = [];
        }
        try {
            $fyStmt = $this->db->prepare("SELECT DISTINCT financial_year FROM transactions WHERE financial_year IS NOT NULL ORDER BY financial_year DESC");
            $fyStmt->execute();
            $financialYears = $fyStmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $financialYears = [];
        }
        return $this->render('admin/banking/reconciliation', [
            'page_title' => 'Bank Reconciliation',
            'transactions' => $transactions,
            'financialYears' => $financialYears
        ]);
    }

    public function reconcile()
    {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'reconciled';
        $notes = $_POST['notes'] ?? '';
        $financial_year = $_POST['financial_year'] ?? date('Y') . '-' . (date('Y') + 1);
        $financial_period = $_POST['financial_period'] ?? date('F Y');
        try {
            list($tenantClause, $tenantParams) = $this->tenantWhere();
            $stmt = $this->db->prepare("UPDATE transactions SET reconciliation_status = ?, reconciled_at = NOW(), reconciled_by = ?, financial_year = ?, financial_period = ? WHERE id = ?" . $tenantClause);
            $params = [$status, $_SESSION['admin_id'] ?? 0, $financial_year, $financial_period, $id];
            $params = array_merge($params, $tenantParams);
            $stmt->execute($params);
            $this->setFlash('success', 'Transaction reconciled successfully');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to reconcile: ' . $e->getMessage());
        }
        $this->redirect('/admin/banking/reconciliation');
    }

    public function financialYears()
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("
                SELECT financial_year, financial_period, COUNT(*) as transaction_count,
                    SUM(CASE WHEN reconciliation_status = 'reconciled' THEN 1 ELSE 0 END) as reconciled_count
                FROM transactions
                WHERE financial_year IS NOT NULL
                GROUP BY financial_year, financial_period
                ORDER BY financial_year DESC
            ");
            $stmt->execute();
            $years = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $years = [];
        }
        return $this->render('admin/banking/financial-years', [
            'page_title' => 'Financial Years',
            'years' => $years
        ]);
    }
}
