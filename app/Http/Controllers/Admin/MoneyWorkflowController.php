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
use UploadValidator;

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
        $forecast = $this->safe(fn() => $this->service->forecastCashFlow(30), ['summary' => [], 'rows' => []]);
        return $this->render('admin/finance/dashboard', [
            'page_title' => 'Money Workflow Dashboard',
            'page_heading' => 'Money Workflow Dashboard',
            'stats' => $stats,
            'recent_txns' => $recentTxns,
            'banks' => $banks,
            'forecast' => $forecast,
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
     *  CASH FLOW FORECAST
     * ========================================================= */
    public function cashFlow()
    {
        $this->requireAdmin();
        $days = (int)($_GET['days'] ?? 30);
        $forecast = $this->safe(fn() => $this->service->forecastCashFlow($days), ['summary' => [], 'rows' => [], 'from' => date('Y-m-d'), 'to' => date('Y-m-d')]);
        return $this->render('admin/finance/cash-flow', [
            'page_title' => 'Cash Flow Forecast',
            'page_heading' => 'Cash Flow Forecast',
            'summary' => $forecast['summary'] ?? [],
            'rows' => $forecast['rows'] ?? [],
            'from' => $forecast['from'] ?? date('Y-m-d'),
            'to' => $forecast['to'] ?? date('Y-m-d'),
            'days' => $days,
        ]);
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

    public function chequePrint(int $id)
    {
        $cheque = $this->safe(fn() => $this->service->getChequeById($id), null);
        if (!$cheque) {
            $this->setFlash('error', 'Cheque not found');
            return $this->redirect('/admin/finance/cheques');
        }
        return $this->render('admin/finance/cheque-print', [
            'cheque' => $cheque,
        ]);
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

        // Fetch live exchange rates via ExchangeRateService
        $currencies = [
            'INR' => ['symbol' => '₹',  'name' => 'Indian Rupee',   'rate' => 1.0],
        ];
        try {
            $fxService = new \App\Services\ExchangeRateService();
            $fxResult = $fxService->getAllRates('INR');
            $supported = $fxService->getSupportedCurrencies();
            $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'AED' => 'د.إ', 'SGD' => 'S$', 'JPY' => '¥', 'CAD' => 'C$', 'AUD' => 'A$'];
            $names = ['USD' => 'US Dollar', 'EUR' => 'Euro', 'GBP' => 'British Pound', 'AED' => 'UAE Dirham', 'SGD' => 'Singapore Dollar', 'JPY' => 'Japanese Yen', 'CAD' => 'Canadian Dollar', 'AUD' => 'Australian Dollar'];
            foreach ($supported as $code) {
                if ($code === 'INR') continue;
                $currencies[$code] = [
                    'symbol' => $symbols[$code] ?? $code,
                    'name'   => $names[$code] ?? $code,
                    'rate'   => ($fxResult['success'] && isset($fxResult['rates'][$code]))
                        ? (float)$fxResult['rates'][$code]
                        : 1.0,
                ];
            }
        } catch (Exception $e) {
            // Fallback to hardcoded rates
            $currencies['USD'] = ['symbol' => '$',  'name' => 'US Dollar',       'rate' => 83.50];
            $currencies['EUR'] = ['symbol' => '€',  'name' => 'Euro',            'rate' => 90.25];
            $currencies['GBP'] = ['symbol' => '£',  'name' => 'British Pound',   'rate' => 105.80];
            $currencies['AED'] = ['symbol' => 'د.إ', 'name' => 'UAE Dirham',     'rate' => 22.73];
        }

        return $this->render('admin/finance/vendor-payment', [
            'page_title' => 'New Vendor Payment',
            'page_heading' => 'Record Vendor Payment',
            'banks' => $banks,
            'currencies' => $currencies,
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
     *  EMI PENALTY ENGINE
     * ========================================================= */
    public function penaltySummary()
    {
        $this->requireAdmin();
        $summary = [];
        if ($this->service) {
            $summary = $this->safe(fn() => $this->service->getOverduePenaltySummary(), []);
        }
        return $this->render('admin/finance/penalty-summary', [
            'page_title'   => 'EMI Penalties',
            'page_heading' => 'EMI Penalty Engine',
            'summary'      => $summary,
        ]);
    }

    public function applyPenalties()
    {
        $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        $result = [];
        if ($this->service) {
            $result = $this->safe(fn() => $this->service->applyDailyPenalties(), ['success' => false, 'error' => 'Service unavailable']);
        }
        $this->json($result);
    }

    /* =========================================================
     *  ON-FIELD CASH COLLECTIONS
     * ========================================================= */
    public function collections()
    {
        $this->requireAdmin();
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['collector_id'])) $filters['collector_id'] = (int)$_GET['collector_id'];
        if (!empty($_GET['from_date'])) $filters['from_date'] = $_GET['from_date'];
        if (!empty($_GET['to_date'])) $filters['to_date'] = $_GET['to_date'];

        $collections = $this->safe(fn() => $this->service->getCollections($filters), []);
        $stats = $this->safe(fn() => $this->service->getCollectionStats(), []);
        $collectors = $this->safe(fn() => $this->service->listCollectors(), []);

        return $this->render('admin/finance/collections', [
            'page_title'   => 'Cash Collections',
            'page_heading' => 'On-Field Cash Collections',
            'collections'  => $collections,
            'stats'        => $stats,
            'collectors'   => $collectors,
            'filters'      => $filters,
        ]);
    }

    public function collectionForm()
    {
        $this->requireAdmin();
        $collectors = $this->safe(fn() => $this->service->listCollectors(), []);
        $bookings = $this->safe(function() {
            $stmt = $this->db->prepare("SELECT id, booking_number FROM plot_bookings ORDER BY id DESC LIMIT 100");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }, []);

        return $this->render('admin/finance/collection-form', [
            'page_title'   => 'Record Collection',
            'page_heading' => 'Record Cash Collection',
            'collectors'   => $collectors,
            'bookings'     => $bookings,
        ]);
    }

    public function collectionStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $receiptPath = null;
            if (!empty($_FILES['receipt_photo']['tmp_name']) && $_FILES['receipt_photo']['error'] === UPLOAD_ERR_OK) {
                $v = UploadValidator::validate($_FILES['receipt_photo'], ['types' => 'images', 'max_size' => 5]);
                if ($v['valid']) {
                    $uploadDir = $this->getReceiptUploadDir();
                    $safeName = UploadValidator::safeFilename($_FILES['receipt_photo']['name']);
                    $ext = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
                    $filename = 'receipt_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    $target = $uploadDir . '/' . $filename;
                    if (move_uploaded_file($_FILES['receipt_photo']['tmp_name'], $target)) {
                        $receiptPath = 'storage/uploads/receipts/' . $filename;
                    }
                }
            }

            $data = $_POST;
            $data['receipt_photo'] = $receiptPath;
            $data['collector_id'] = (int)($_POST['collector_id'] ?? ($_SESSION['admin_id'] ?? 0));

            $result = $this->service->recordCollection($data);
            if ($result['success']) {
                $this->setFlash('success', 'Collection recorded: ' . ($result['collection_number'] ?? ''));
            } else {
                $this->setFlash('error', $result['error'] ?? 'Failed to record collection');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/collections');
    }

    public function collectionVerify()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $id = (int)($_POST['id'] ?? 0);
            $adminId = (int)($_SESSION['admin_id'] ?? 0);
            $this->service->verifyCollection($id, $adminId);
            $this->setFlash('success', 'Collection #' . $id . ' verified');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/collections');
    }

    public function collectionReject()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $id = (int)($_POST['id'] ?? 0);
            $adminId = (int)($_SESSION['admin_id'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');
            if ($reason === '') {
                $this->setFlash('error', 'Rejection reason is required');
                return $this->redirect('/admin/finance/collections');
            }
            $this->service->rejectCollection($id, $adminId, $reason);
            $this->setFlash('success', 'Collection #' . $id . ' rejected');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/collections');
    }

    /* =========================================================
     *  COLLECTION RECONCILIATION
     * ========================================================= */
    public function reconciliationCollections()
    {
        $this->requireAdmin();
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['collector_id'])) $filters['collector_id'] = (int)$_GET['collector_id'];

        $sessions = $this->safe(fn() => $this->service->getReconciliationSessions($filters), []);
        $collectors = $this->safe(fn() => $this->service->listCollectors(), []);

        return $this->render('admin/finance/reconciliation-collections', [
            'page_title'   => 'Collection Reconciliation',
            'page_heading' => 'Cash Collection Reconciliation',
            'sessions'     => $sessions,
            'collectors'   => $collectors,
            'filters'      => $filters,
        ]);
    }

    public function reconciliationCollectionsStart()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $collectorId = (int)($_POST['collector_id'] ?? 0);
            $date = $_POST['session_date'] ?? date('Y-m-d');
            $result = $this->service->startReconciliation($collectorId, $date);
            if ($result['success']) {
                $this->setFlash('success', 'Reconciliation session started');
            } else {
                $this->setFlash('error', $result['error'] ?? 'Failed to start reconciliation');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/reconciliation-collections');
    }

    public function reconciliationCollectionsClose()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $sessionId = (int)($_POST['session_id'] ?? 0);
            $adminId = (int)($_SESSION['admin_id'] ?? 0);
            $this->service->closeReconciliation($sessionId, $adminId);
            $this->setFlash('success', 'Reconciliation session closed');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/reconciliation-collections');
    }

    /* =========================================================
     *  LEGACY ACCOUNTS & INVOICES (migrated from FinanceController)
     * ========================================================= */
    public function adminAccounts()
    {
        $this->requireAdmin();
        return $this->redirect('/admin/finance/bank-accounts');
    }

    public function invoices()
    {
        $this->requireAdmin();
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';
        try {
            $sql = "SELECT i.* FROM invoices i WHERE 1=1";
            $params = [];
            if ($status !== '') {
                $sql .= " AND i.status = ?";
                $params[] = $status;
            }
            if ($search !== '') {
                $sql .= " AND (i.invoice_number LIKE ? OR i.client_name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $sql .= " ORDER BY i.created_at DESC LIMIT 200";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $invoices = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $invoices = [];
        }
        return $this->render('admin/finance/invoices', [
            'page_title' => 'Invoices',
            'page_heading' => 'Invoice Register',
            'invoices' => $invoices,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function viewInvoice(int $id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = ?");
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($invoice) {
                $invoice['paid_amount'] = 0;
                try {
                    $payStmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM invoice_payments WHERE invoice_id = ?");
                    $payStmt->execute([$id]);
                    $invoice['paid_amount'] = $payStmt->fetchColumn() ?: 0;
                } catch (Exception $e) {
                    // invoice_payments table may not exist
                }

                $itemsStmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order");
                $itemsStmt->execute([$id]);
                $invoice['items'] = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

                try {
                    $paymentsStmt = $this->db->prepare("SELECT * FROM invoice_payments WHERE invoice_id = ? ORDER BY payment_date DESC");
                    $paymentsStmt->execute([$id]);
                    $invoice['payments'] = $paymentsStmt->fetchAll(\PDO::FETCH_ASSOC);
                } catch (Exception $e) {
                    $invoice['payments'] = [];
                }
            }
        } catch (Exception $e) {
            $invoice = null;
        }

        if (!$invoice) {
            $this->setFlash('error', 'Invoice not found');
            return $this->redirect('/admin/finance/invoices');
        }

        return $this->render('admin/invoices/view', [
            'page_title' => 'Invoice #' . $invoice['invoice_number'],
            'page_heading' => 'Invoice #' . $invoice['invoice_number'],
            'invoice' => $invoice,
        ]);
    }

    public function downloadInvoice(int $id)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT * FROM invoices WHERE id = ?");
            $stmt->execute([$id]);
            $invoice = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$invoice) {
                $this->setFlash('error', 'Invoice not found');
                return $this->redirect('/admin/finance/invoices');
            }

            $itemsStmt = $this->db->prepare("SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY sort_order");
            $itemsStmt->execute([$id]);
            $items = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);

            $html = $this->buildInvoiceHtml($invoice, $items);

            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="invoice_' . htmlspecialchars($invoice['invoice_number']) . '.html"');
            echo $html;
            exit;
        } catch (Exception $e) {
            $this->setFlash('error', 'Download failed: ' . $e->getMessage());
            return $this->redirect('/admin/finance/invoices');
        }
    }

    public function deleteInvoice(int $id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $stmt = $this->db->prepare("UPDATE invoices SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$id]);
            $this->setFlash('success', 'Invoice cancelled');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/finance/invoices');
    }

    private function buildInvoiceHtml(array $invoice, array $items): string
    {
        $ob = ob_start();
        ?>
        <!DOCTYPE html>
        <html><head><meta charset="utf-8"><title>Invoice <?= htmlspecialchars($invoice['invoice_number']) ?></title>
        <style>body{font-family:Arial,sans-serif;margin:40px;color:#333}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ddd;padding:8px 12px;text-align:left}th{background:#f5f5f5}.text-right{text-align:right}.total{font-size:1.2em;font-weight:bold}</style>
        </head><body>
        <h1>Invoice #<?= htmlspecialchars($invoice['invoice_number']) ?></h1>
        <p><strong>Date:</strong> <?= htmlspecialchars($invoice['invoice_date']) ?> | <strong>Due:</strong> <?= htmlspecialchars($invoice['due_date']) ?></p>
        <p><strong>Client:</strong> <?= htmlspecialchars($invoice['client_name']) ?></p>
        <?php if (!empty($invoice['client_email'])): ?><p>Email: <?= htmlspecialchars($invoice['client_email']) ?></p><?php endif; ?>
        <?php if (!empty($invoice['client_phone'])): ?><p>Phone: <?= htmlspecialchars($invoice['client_phone']) ?></p><?php endif; ?>
        <table><thead><tr><th>Item</th><th>Qty</th><th>Rate</th><th>Discount</th><th>Tax</th><th>Total</th></tr></thead><tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= (int)$item['quantity'] ?></td>
            <td>₹<?= number_format($item['unit_price'], 2) ?></td>
            <td><?= $item['discount_percent'] > 0 ? $item['discount_percent'] . '%' : '-' ?></td>
            <td><?= $item['tax_percent'] > 0 ? $item['tax_percent'] . '%' : '-' ?></td>
            <td>₹<?= number_format($item['line_total'] ?? ($item['unit_price'] * $item['quantity']), 2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table>
        <p class="total">Total: ₹<?= number_format($invoice['total_amount'] ?? 0, 2) ?></p>
        <p>Status: <?= strtoupper(htmlspecialchars($invoice['status'])) ?></p>
        </body></html>
        <?php
        $html = ob_get_clean();
        return $html;
    }

    /* =========================================================
     *  EXCHANGE RATE API
     * ========================================================= */
    public function getExchangeRate()
    {
        $this->requireAdmin();
        $from = strtoupper(trim($_GET['from'] ?? 'USD'));
        $to   = strtoupper(trim($_GET['to'] ?? 'INR'));

        try {
            $fxService = new \App\Services\ExchangeRateService();
            $result = $fxService->getRate($from, $to);
        } catch (Exception $e) {
            $result = ['success' => false, 'error' => $e->getMessage()];
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    public function getAllRates()
    {
        $this->requireAdmin();
        $base = strtoupper(trim($_GET['base'] ?? 'INR'));

        try {
            $fxService = new \App\Services\ExchangeRateService();
            $result = $fxService->getAllRates($base);
        } catch (Exception $e) {
            $result = ['success' => false, 'error' => $e->getMessage()];
        }

        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    /* =========================================================
     *  PDF DOWNLOADS
     * ========================================================= */
    public function downloadAgreement($bookingId = null)
    {
        $this->requireAdmin();
        $bookingId = (int)($bookingId ?? 0);
        if ($bookingId <= 0) {
            $this->setFlash('error', 'Invalid booking ID.');
            $this->redirect('/admin/sales/bookings');
        }
        try {
            $pdfService = new \App\Services\PDF\AgreementPDFService($this->db instanceof \PDO ? $this->db : null);
            $result = $pdfService->generateBookingAgreement($bookingId);
            if (!empty($result['success']) && !empty($result['pdf_path']) && file_exists($result['pdf_path'])) {
                $filename = $result['filename'] ?? basename($result['pdf_path']);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($result['pdf_path']));
                header('Cache-Control: no-cache, must-revalidate');
                readfile($result['pdf_path']);
                exit;
            }
            $this->setFlash('error', $result['error'] ?? 'Failed to generate agreement PDF.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        } catch (\Exception $e) {
            $this->setFlash('error', 'PDF generation failed: ' . $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        }
    }

    public function downloadDemandLetter($installmentId = null)
    {
        $this->requireAdmin();
        $installmentId = (int)($installmentId ?? 0);
        if ($installmentId <= 0) {
            $this->setFlash('error', 'Invalid installment ID.');
            $this->redirect('/admin/finance/dashboard');
        }
        try {
            $pdfService = new \App\Services\PDF\AgreementPDFService($this->db instanceof \PDO ? $this->db : null);
            $result = $pdfService->generateDemandLetter($installmentId);
            if (!empty($result['success']) && !empty($result['pdf_path']) && file_exists($result['pdf_path'])) {
                $filename = $result['filename'] ?? basename($result['pdf_path']);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($result['pdf_path']));
                header('Cache-Control: no-cache, must-revalidate');
                readfile($result['pdf_path']);
                exit;
            }
            $this->setFlash('error', $result['error'] ?? 'Failed to generate demand letter PDF.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        } catch (\Exception $e) {
            $this->setFlash('error', 'PDF generation failed: ' . $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        }
    }

    public function downloadAllotmentLetter($bookingId = null)
    {
        $this->requireAdmin();
        $bookingId = (int)($bookingId ?? 0);
        if ($bookingId <= 0) {
            $this->setFlash('error', 'Invalid booking ID.');
            $this->redirect('/admin/sales/bookings');
        }
        try {
            $pdfService = new \App\Services\PDF\AgreementPDFService($this->db instanceof \PDO ? $this->db : null);
            $result = $pdfService->generateAllotmentLetter($bookingId);
            if (!empty($result['success']) && !empty($result['pdf_path']) && file_exists($result['pdf_path'])) {
                $filename = $result['filename'] ?? basename($result['pdf_path']);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($result['pdf_path']));
                header('Cache-Control: no-cache, must-revalidate');
                readfile($result['pdf_path']);
                exit;
            }
            $this->setFlash('error', $result['error'] ?? 'Failed to generate allotment letter PDF.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        } catch (\Exception $e) {
            $this->setFlash('error', 'PDF generation failed: ' . $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        }
    }

    public function downloadRefundVoucher($refundId = null)
    {
        $this->requireAdmin();
        $refundId = (int)($refundId ?? 0);
        if ($refundId <= 0) {
            $this->setFlash('error', 'Invalid refund ID.');
            $this->redirect('/admin/finance/dashboard');
        }
        try {
            $pdfService = new \App\Services\PDF\AgreementPDFService($this->db instanceof \PDO ? $this->db : null);
            $result = $pdfService->generateRefundVoucher($refundId);
            if (!empty($result['success']) && !empty($result['pdf_path']) && file_exists($result['pdf_path'])) {
                $filename = $result['filename'] ?? basename($result['pdf_path']);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($result['pdf_path']));
                header('Cache-Control: no-cache, must-revalidate');
                readfile($result['pdf_path']);
                exit;
            }
            $this->setFlash('error', $result['error'] ?? 'Failed to generate refund voucher PDF.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        } catch (\Exception $e) {
            $this->setFlash('error', 'PDF generation failed: ' . $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/admin/finance/dashboard');
        }
    }

    /* =========================================================
     *  EMI AUTO-PAYMENT
     * ========================================================= */
    public function emiAutoPayDashboard()
    {
        $this->requireAdmin();
        try {
            $autoPayService = new \App\Services\Payment\EMIAutoPaymentService($this->db);
            $stats = $autoPayService->getDashboardStats();
            $mandates = $autoPayService->listMandates();
            $failedPayments = $autoPayService->getFailedPayments(50);
        } catch (Exception $e) {
            $stats = [];
            $mandates = [];
            $failedPayments = [];
        }
        return $this->render('admin/finance/emi-auto-pay', [
            'page_title'    => 'EMI Auto-Pay',
            'page_heading'  => 'EMI Auto-Payment Dashboard',
            'stats'         => $stats,
            'mandates'      => $mandates,
            'failedPayments'=> $failedPayments,
            'isTestMode'    => ($_ENV['RAZORPAY_TEST_MODE'] ?? 'true') === 'true',
        ]);
    }

    public function runAutoPaymentCron()
    {
        $this->requireAdmin();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }
        try {
            $autoPayService = new \App\Services\Payment\EMIAutoPaymentService($this->db);
            $result = $autoPayService->processDueEmiPayments();
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
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

    private function getReceiptUploadDir(): string
    {
        $dir = __DIR__ . '/../../../../storage/uploads/receipts';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }
}
