<?php
/**
 * Finance/Accounting Controller
 * Payments, Invoices, Expenses
 */

namespace App\Http\Controllers\Admin;

class FinanceController extends AdminController
{
    /**
     * Dashboard
     */
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/index', []);
    }
    
    /**
     * All invoices
     */
    public function invoices()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/invoices', []);
    }
    
    /**
     * Create invoice
     */
    public function createInvoice()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/create-invoice', []);
    }
    
    /**
     * All expenses
     */
    public function expenses()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/expenses', []);
    }
    
    /**
     * Create expense
     */
    public function createExpense()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/create-expense', []);
    }
    
    /**
     * All payments
     */
    public function payments()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/payments', []);
    }
    
    /**
     * EMI management
     */
    public function emi()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/emi', []);
    }
    
    /**
     * EMI Calculator
     */
    public function calculator()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/calculator', []);
    }
    
    /**
     * Bank accounts
     */
    public function bankAccounts()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/bank-accounts', []);
    }
    
    /**
     * View single invoice
     */
    public function viewInvoice(int $id)
    {
        $this->requireAdmin();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT i.*, COALESCE((SELECT SUM(amount) FROM invoice_payments WHERE invoice_id = i.id), 0) as paid_amount FROM invoices i WHERE i.id = ?");
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($invoice) {
                $itemsStmt = $db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order");
                $itemsStmt->execute([$id]);
                $invoice['items'] = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

                $paymentsStmt = $db->prepare("SELECT * FROM invoice_payments WHERE invoice_id = ? ORDER BY payment_date DESC");
                $paymentsStmt->execute([$id]);
                $invoice['payments'] = $paymentsStmt->fetchAll(\PDO::FETCH_ASSOC);
            }
        } catch (\Exception $e) {
            $invoice = null;
        }

        if (!$invoice) {
            $this->flashMessage('Invoice not found', 'error');
            header('Location: ' . BASE_URL . '/admin/invoices');
            exit;
        }

        $this->render('admin/invoices/view', [
            'page_title' => 'Invoice #' . $invoice['invoice_number'],
            'invoice' => $invoice,
        ]);
    }

    /**
     * Download invoice as HTML
     */
    public function downloadInvoice(int $id)
    {
        $this->requireAdmin();

        try {
            $inv = new \App\Models\Invoice();
            $html = $inv->generateInvoiceHTML($id);
            if (!$html) {
                $this->flashMessage('Invoice not found', 'error');
                header('Location: ' . BASE_URL . '/admin/invoices');
                exit;
            }
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="invoice_' . time() . '.html"');
            echo $html;
        } catch (\Exception $e) {
            $this->flashMessage('Download failed: ' . $e->getMessage(), 'error');
            header('Location: ' . BASE_URL . '/admin/invoices');
        }
        exit;
    }

    /**
     * Reports
     */
    public function reports()
    {
        $this->requireAdmin();
        return $this->render('admin/finance/reports', []);
    }

    public function adminAccounts()
    {
        $this->requireAdmin();
        return $this->render('admin/accounts/index', ['page_title' => 'Accounts']);
    }
}