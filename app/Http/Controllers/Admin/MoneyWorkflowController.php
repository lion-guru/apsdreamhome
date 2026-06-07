<?php
/**
 * Module 3: Money Workflow + Accounting
 *
 * MoneyWorkflowController
 *
 * Thin admin controller over App\Services\Accounting\MoneyWorkflowService.
 * URL prefix: /admin/finance/*
 * All actions require admin auth and use aps-cp-* layout via $this->render().
 */

namespace App\Http\Controllers\Admin;

use App\Services\Accounting\MoneyWorkflowService;
use Exception;

class MoneyWorkflowController extends AdminController
{
    /** @var \PDO|null */
    protected $db;

    /** @var MoneyWorkflowService|null */
    protected $service;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->db = \App\Core\Database\Database::getInstance();
            if (method_exists($this->db, 'getPdo')) {
                $this->db = $this->db->getPdo();
            }
        } catch (Exception $e) {
            $this->db = null;
        }
        try {
            $this->service = new MoneyWorkflowService(
                $this->db instanceof \PDO ? $this->db : null
            );
        } catch (Exception $e) {
            try {
                $this->service = new MoneyWorkflowService();
            } catch (Exception $e2) {
                $this->service = null;
            }
        }
    }

    /* =========================================================
     *  DASHBOARD
     * ========================================================= */
    public function dashboard()
    {
        $this->requireAdmin();
        $stats = $this->safe(fn() => $this->service->getDashboardStats(), []);
        $recentTxns = $this->safe(fn() => $this->service->getDailyCashBook(date('Y-m-01'), date('Y-m-t')), []);
        $recentTxns = array_slice($recentTxns, 0, 10);
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        return $this->render('admin/finance/dashboard', [
            'page_title' => 'Money Workflow Dashboard',
            'page_heading' => 'Money Workflow Dashboard',
            'stats' => $stats,
            'recent_txns' => $recentTxns,
            'banks' => $banks,
        ]);
    }

    /* =========================================================
     *  BANK ACCOUNTS
     * ========================================================= */
    public function bankAccounts()
    {
        $this->requireAdmin();
        $banks = $this->safe(fn() => $this->service->getBankAccounts(false), []);
        return $this->render('admin/finance/bank-accounts', [
            'page_title' => 'Bank Accounts',
            'page_heading' => 'Bank Accounts Master',
            'banks' => $banks,
        ]);
    }

    public function bankAccountForm()
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $bank = null;
        if ($id > 0) {
            $bank = $this->safe(fn() => $this->service->getBankAccount($id), null);
        }
        return $this->render('admin/finance/bank-account-form', [
            'page_title' => $id > 0 ? 'Edit Bank Account' : 'New Bank Account',
            'page_heading' => $id > 0 ? 'Edit Bank Account' : 'New Bank Account',
            'bank' => $bank,
        ]);
    }

    public function bankAccountStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->db->update('bank_accounts_master', [
                    'account_name'         => trim($_POST['account_name'] ?? ''),
                    'account_number'       => trim($_POST['account_number'] ?? ''),
                    'ifsc_code'            => strtoupper(trim($_POST['ifsc_code'] ?? '')),
                    'bank_name'            => trim($_POST['bank_name'] ?? ''),
                    'branch'               => $_POST['branch'] ?? null,
                    'account_type'         => $_POST['account_type'] ?? 'current',
                    'is_escrow'            => !empty($_POST['is_escrow']) ? 1 : 0,
                    'rera_project_id'      => !empty($_POST['rera_project_id']) ? (int)$_POST['rera_project_id'] : null,
                    'gst_registered'       => !empty($_POST['gst_registered']) ? 1 : 0,
                    'signatory_name'       => $_POST['signatory_name'] ?? null,
                    'signatory_pan'        => strtoupper($_POST['signatory_pan'] ?? ''),
                    'active'               => isset($_POST['active']) ? 1 : 0,
                ], 'id = ?', [$id]);
                $this->setFlash('success', 'Bank account updated');
            } else {
                $this->service->createBankAccount($_POST);
                $this->setFlash('success', 'Bank account created');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/bank-accounts');
    }

    /* =========================================================
     *  CASH BOOK
     * ========================================================= */
    public function cashBook()
    {
        $this->requireAdmin();
        $from = $_GET['from'] ?? date('Y-m-01');
        $to = $_GET['to'] ?? date('Y-m-t');
        $bankId = !empty($_GET['bank_account_id']) ? (int)$_GET['bank_account_id'] : null;
        $entries = $this->safe(fn() => $this->service->getDailyCashBook($from, $to, $bankId), []);
        $summary = $this->safe(fn() => $this->service->getCashBookSummary($from, $to), []);
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        return $this->render('admin/finance/cash-book', [
            'page_title' => 'Daily Cash Book',
            'page_heading' => 'Daily Cash Book',
            'entries' => $entries,
            'summary' => $summary,
            'banks' => $banks,
            'from' => $from,
            'to' => $to,
            'bank_id' => $bankId,
        ]);
    }

    public function transactionForm()
    {
        $this->requireAdmin();
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        return $this->render('admin/finance/transaction-form', [
            'page_title' => 'New Cash Transaction',
            'page_heading' => 'Record Cash Transaction',
            'banks' => $banks,
        ]);
    }

    public function transactionStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->recordCashTransaction($_POST);
            $this->setFlash('success', 'Transaction recorded');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/cash-book');
    }

    /* =========================================================
     *  PETTY CASH
     * ========================================================= */
    public function pettyCash()
    {
        $this->requireAdmin();
        $balance = $this->safe(fn() => $this->service->getPettyCashBalance(), 0.0);
        $entries = $this->safe(fn() => $this->db->query(
            "SELECT * FROM petty_cash ORDER BY transaction_date DESC, id DESC LIMIT 100"
        )->fetchAll(\PDO::FETCH_ASSOC), []);
        return $this->render('admin/finance/petty-cash', [
            'page_title' => 'Petty Cash',
            'page_heading' => 'Petty Cash Management',
            'balance' => $balance,
            'entries' => $entries,
        ]);
    }

    public function pettyTopup()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->topupPettyCash(
                (float)($_POST['amount'] ?? 0),
                [
                    'topup_date'  => $_POST['topup_date'] ?? date('Y-m-d'),
                    'source'      => $_POST['source'] ?? null,
                    'remarks'     => $_POST['remarks'] ?? '',
                ]
            );
            $this->setFlash('success', 'Petty cash topped up');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/petty-cash');
    }

    public function pettyExpense()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->recordPettyExpense($_POST);
            $this->setFlash('success', 'Petty expense recorded');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/petty-cash');
    }

    /* =========================================================
     *  CHEQUES
     * ========================================================= */
    public function cheques()
    {
        $this->requireAdmin();
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['bank_account_id'])) $filters['bank_account_id'] = (int)$_GET['bank_account_id'];
        $filters['limit'] = 200;
        $cheques = $this->safe(fn() => $this->service->getChequeRegister($filters), []);
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        $bounceLog = $this->safe(fn() => $this->db->query(
            "SELECT b.*, c.cheque_number FROM cheque_bounce_log b
             LEFT JOIN cheque_register c ON b.cheque_id = c.id
             ORDER BY b.id DESC LIMIT 20"
        )->fetchAll(\PDO::FETCH_ASSOC), []);
        return $this->render('admin/finance/cheques', [
            'page_title' => 'Cheque Register',
            'page_heading' => 'Cheque / DD Register',
            'cheques' => $cheques,
            'banks' => $banks,
            'bounce_log' => $bounceLog,
            'status' => $_GET['status'] ?? '',
            'bank_id' => $_GET['bank_account_id'] ?? '',
        ]);
    }

    public function chequeIssue()
    {
        $this->requireAdmin();
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        return $this->render('admin/finance/cheque-issue', [
            'page_title' => 'Issue Cheque',
            'page_heading' => 'Issue New Cheque / DD',
            'banks' => $banks,
        ]);
    }

    public function chequeStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->issueCheque($_POST);
            $this->setFlash('success', 'Cheque issued');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/cheques');
    }

    public function chequeStatus()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? 'cleared';
            $reason = $_POST['reason'] ?? '';
            $this->service->markChequeStatus($id, $status, $reason);
            $this->setFlash('success', 'Cheque status updated');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/cheques');
    }

    /* =========================================================
     *  BANK RECONCILIATION
     * ========================================================= */
    public function reconciliation()
    {
        $this->requireAdmin();
        $bankId = !empty($_GET['bank_account_id']) ? (int)$_GET['bank_account_id'] : null;
        $reconList = $this->safe(fn() => $this->service->getReconciliations($bankId), []);
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        return $this->render('admin/finance/reconciliation', [
            'page_title' => 'Bank Reconciliation',
            'page_heading' => 'Bank Reconciliation',
            'reconciliations' => $reconList,
            'banks' => $banks,
            'bank_id' => $bankId,
        ]);
    }

    public function reconciliationMatch($id = 0)
    {
        $this->requireAdmin();
        $id = $id ?: (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $recon = $this->safe(fn() => $this->db->fetchOne(
            "SELECT r.*, b.account_name, b.bank_name FROM bank_reconciliation r
             LEFT JOIN bank_accounts_master b ON r.bank_account_id = b.id WHERE r.id = ?",
            [$id]
        ), null);
        $items = $this->safe(fn() => $this->service->getReconciliationItems($id), []);
        $cashBook = $this->safe(fn() => $this->db->query(
            "SELECT id, transaction_date, transaction_type, amount, voucher_number, narration
             FROM daily_cash_book ORDER BY transaction_date DESC LIMIT 100"
        )->fetchAll(\PDO::FETCH_ASSOC), []);
        return $this->render('admin/finance/reconciliation-match', [
            'page_title' => 'Reconciliation Match',
            'page_heading' => 'Match Statement Items',
            'recon' => $recon,
            'items' => $items,
            'cash_book' => $cashBook,
        ]);
    }

    public function reconciliationCreate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $bankId = (int)($_POST['bank_account_id'] ?? 0);
            $this->service->createReconciliation($bankId, $_POST);
            $this->setFlash('success', 'Reconciliation started');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/reconciliation');
    }

    public function reconciliationItemMatch()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $itemId = (int)($_POST['item_id'] ?? 0);
            $status = $_POST['status'] ?? 'matched';
            $cbId = !empty($_POST['cashbook_id']) ? (int)$_POST['cashbook_id'] : null;
            $this->service->matchTransaction($itemId, $status, $cbId);
            $this->setFlash('success', 'Item matched');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/reconciliation/match?id=' . (int)($_POST['recon_id'] ?? 0));
    }

    public function reconciliationComplete()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->completeReconciliation((int)($_POST['id'] ?? 0));
            $this->setFlash('success', 'Reconciliation completed');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/reconciliation');
    }

    /* =========================================================
     *  TDS
     * ========================================================= */
    public function tds()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->currentFy();
        $qtr = $_GET['quarter'] ?? null;
        $filters = ['fy' => $fy, 'limit' => 200];
        if ($qtr) $filters['quarter'] = $qtr;
        $entries = $this->safe(fn() => $this->service->getTdsRegister($filters), []);
        $summary = $this->safe(fn() => $this->service->getTdsSummary($fy), []);
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        $certs = $this->safe(fn() => $this->service->getTdsCertificatesIssued($fy), []);
        return $this->render('admin/finance/tds', [
            'page_title' => 'TDS Register',
            'page_heading' => 'TDS Register & Compliance',
            'entries' => $entries,
            'summary' => $summary,
            'banks' => $banks,
            'certificates' => $certs,
            'fy' => $fy,
            'quarter' => $qtr,
        ]);
    }

    public function tdsRecord()
    {
        $this->requireAdmin();
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        return $this->render('admin/finance/tds-record', [
            'page_title' => 'Record TDS',
            'page_heading' => 'Record New TDS Deduction',
            'banks' => $banks,
        ]);
    }

    public function tdsStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->recordTdsProxy($_POST);
            $this->setFlash('success', 'TDS recorded');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/tds');
    }

    public function tdsCertificates()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->currentFy();
        $certs = $this->safe(fn() => $this->service->getTdsCertificatesIssued($fy), []);
        return $this->render('admin/finance/tds-certificates', [
            'page_title' => 'TDS Certificates',
            'page_heading' => 'TDS Certificates Issued',
            'certificates' => $certs,
            'fy' => $fy,
        ]);
    }

    public function tdsCertificateStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->generateTdsCertificate(
                (int)($_POST['deductee_user_id'] ?? 0),
                $_POST['fy'] ?? $this->currentFy(),
                $_POST['quarter'] ?? null
            );
            $this->setFlash('success', 'TDS certificate generated');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/tds-certificates');
    }

    /* =========================================================
     *  GST
     * ========================================================= */
    public function gst()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->currentFy();
        $entries = $this->safe(fn() => $this->service->getGstTransactions(['fy' => $fy, 'limit' => 200]), []);
        $summary = $this->safe(fn() => $this->service->getGstSummary($fy), []);
        return $this->render('admin/finance/gst', [
            'page_title' => 'GST',
            'page_heading' => 'GST Transactions',
            'entries' => $entries,
            'summary' => $summary,
            'fy' => $fy,
        ]);
    }

    public function gstRecord()
    {
        $this->requireAdmin();
        $fy = $_GET['fy'] ?? $this->currentFy();
        $summary = $this->safe(fn() => $this->service->getGstSummary($fy), []);
        return $this->render('admin/finance/gst-summary', [
            'page_title' => 'GST Summary',
            'page_heading' => 'GST Summary & ITC Reconciliation',
            'summary' => $summary,
            'fy' => $fy,
        ]);
    }

    public function gstStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->recordGstProxy($_POST);
            $this->setFlash('success', 'GST transaction recorded');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/gst');
    }

    /* =========================================================
     *  EXPENSES
     * ========================================================= */
    public function expenses()
    {
        $this->requireAdmin();
        $filters = ['limit' => 200];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        $entries = $this->safe(fn() => $this->service->getExpenses($filters), []);
        return $this->render('admin/finance/expenses', [
            'page_title' => 'Expenses',
            'page_heading' => 'Expense Approvals',
            'entries' => $entries,
            'status' => $_GET['status'] ?? '',
        ]);
    }

    public function expenseForm()
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $expense = null;
        if ($id > 0) {
            $expense = $this->safe(fn() => $this->service->getExpense($id), null);
        }
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        return $this->render('admin/finance/expense-form', [
            'page_title' => $id > 0 ? 'Expense #' . $id : 'Submit Expense',
            'page_heading' => $id > 0 ? 'Expense #' . $id : 'Submit New Expense',
            'expense' => $expense,
            'banks' => $banks,
        ]);
    }

    public function expenseStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->submitExpense($_POST);
            $this->setFlash('success', 'Expense submitted for approval');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/expenses');
    }

    public function expenseApprove()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $id = (int)($_POST['id'] ?? 0);
            $this->service->approveExpenseById($id, $_POST['remarks'] ?? '');
            $this->setFlash('success', 'Expense approved');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/expenses');
    }

    public function expenseReject()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $id = (int)($_POST['id'] ?? 0);
            $this->service->rejectExpenseById($id, $_POST['remarks'] ?? '');
            $this->setFlash('success', 'Expense rejected');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/expenses');
    }

    /* =========================================================
     *  VENDORS / VENDOR PAYMENTS
     * ========================================================= */
    public function vendors()
    {
        $this->requireAdmin();
        $payments = $this->safe(fn() => $this->service->getVendorPayments(['limit' => 200]), []);
        $outstanding = $this->safe(fn() => $this->service->getVendorOutstanding(), []);
        return $this->render('admin/finance/vendors', [
            'page_title' => 'Vendor Payments',
            'page_heading' => 'Vendor Payments & Outstanding',
            'payments' => $payments,
            'outstanding' => $outstanding,
        ]);
    }

    public function vendorPayment()
    {
        $this->requireAdmin();
        $banks = $this->safe(fn() => $this->service->getBankAccounts(true), []);
        return $this->render('admin/finance/vendor-payment', [
            'page_title' => 'New Vendor Payment',
            'page_heading' => 'Record Vendor Payment',
            'banks' => $banks,
        ]);
    }

    public function vendorPaymentStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->recordVendorPayment($_POST);
            $this->setFlash('success', 'Vendor payment recorded');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/vendors');
    }

    /* =========================================================
     *  CASH FLOW FORECAST
     * ========================================================= */
    public function forecast()
    {
        $this->requireAdmin();
        $days = (int)($_GET['days'] ?? 30);
        $forecast = $this->safe(fn() => $this->service->generateForecast($days), []);
        $actuals = $this->safe(fn() => $this->service->getActualVsForecast(date('Y-m-01'), date('Y-m-t', strtotime("+$days days"))), []);
        return $this->render('admin/finance/forecast', [
            'page_title' => 'Cash Flow Forecast',
            'page_heading' => 'Cash Flow Forecast',
            'forecast' => $forecast,
            'actuals' => $actuals,
            'days' => $days,
        ]);
    }

    /* =========================================================
     *  DEMAND LETTER TEMPLATES
     * ========================================================= */
    public function templates()
    {
        $this->requireAdmin();
        $templates = $this->safe(fn() => $this->service->getDemandLetterTemplates(false), []);
        return $this->render('admin/finance/templates', [
            'page_title' => 'Demand Letter Templates',
            'page_heading' => 'Demand Letter Templates',
            'templates' => $templates,
        ]);
    }

    public function templateForm()
    {
        $this->requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        $template = null;
        if ($id > 0) {
            $template = $this->safe(fn() => $this->service->getDemandLetterTemplate($id), null);
        }
        return $this->render('admin/finance/template-form', [
            'page_title' => $id > 0 ? 'Edit Template' : 'New Template',
            'page_heading' => $id > 0 ? 'Edit Template' : 'New Demand Letter Template',
            'template' => $template,
        ]);
    }

    public function templateStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->service->updateDemandLetterTemplate($id, $_POST);
                $this->setFlash('success', 'Template updated');
            } else {
                $this->service->createDemandLetterTemplate($_POST);
                $this->setFlash('success', 'Template created');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/templates');
    }

    public function templateDelete()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $this->service->deleteDemandLetterTemplate((int)($_POST['id'] ?? 0));
            $this->setFlash('success', 'Template deleted');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/templates');
    }

    /* =========================================================
     *  VOUCHER LOG (audit trail)
     * ========================================================= */
    public function voucherLog()
    {
        $this->requireAdmin();
        $vouchers = $this->safe(fn() => $this->service->getVoucherLog(200), []);
        return $this->render('admin/finance/voucher-log', [
            'page_title' => 'Voucher Log',
            'page_heading' => 'Payment Voucher Audit Log',
            'vouchers' => $vouchers,
        ]);
    }

    /* =========================================================
     *  HELPERS
     * ========================================================= */
    private function currentFy(): string
    {
        $m = (int)date('n');
        $y = (int)date('Y');
        if ($m < 4) {
            return ($y - 1) . '-' . substr((string)$y, -2);
        }
        return $y . '-' . substr((string)($y + 1), -2);
    }

    private function safe(callable $fn, $fallback = null)
    {
        try { return $fn(); } catch (Exception $e) { return $fallback; }
    }

    private function validateCsrfOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->setFlash('error', 'Invalid CSRF token. Please retry.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        }
    }
}
