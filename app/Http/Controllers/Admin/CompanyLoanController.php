<?php

namespace App\Http\Controllers\Admin;

use App\Services\Loan\CompanyLoanService;
use App\Services\Loan\LoanDocumentService;
use App\Services\Loan\InterestFreeOfferService;
use Exception;

class CompanyLoanController extends AdminController
{
    protected $db;
    protected $loanService;
    protected $docService;
    protected $offerService;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->db = \App\Core\Database::getInstance();
            if (method_exists($this->db, 'getPdo')) {
                $this->db = $this->db->getPdo();
            }
        } catch (\Exception $e) {
            $this->db = null;
        }
        $this->loanService = new CompanyLoanService($this->db);
        $this->docService = new LoanDocumentService($this->db);
        $this->offerService = new InterestFreeOfferService($this->db);
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $stats = $this->loanService->getDashboardStats();
            $loans = $this->loanService->listLoans(['limit' => 50]);

            $this->data['page_title'] = 'Company Loan Management';
            $this->data['page_heading'] = 'Company Loan Management';
            $this->data['stats'] = $stats;
            $this->data['loans'] = $loans;
            $this->data['offers'] = $this->loanService->getOffers();

            $this->render('admin/loans/company/index', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load loan dashboard: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    public function createForm()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Create New Loan';
        $this->data['page_heading'] = 'Create New Loan';
        $this->data['customers'] = $this->loanService->getCustomers();
        $this->data['plots'] = $this->loanService->getPlots();
        $this->data['offers'] = $this->loanService->getOffers();
        $this->data['early_incentives'] = $this->loanService->getEarlyIncentives();

        $this->render('admin/loans/company/form', $this->data);
    }

    public function createStore()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $data = $_POST;
            $data['created_by'] = $_SESSION['admin_id'] ?? null;

            $result = $this->loanService->createLoan($data);
            if ($result['success']) {
                $this->setFlash('success', 'Loan created successfully. Loan #: ' . $result['loan_number']);
                $this->redirect('/admin/company-loans/' . $result['loan_id']);
            } else {
                $this->setFlash('error', $result['error']);
                $this->redirect('/admin/company-loans/create');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error creating loan: ' . $e->getMessage());
            $this->redirect('/admin/company-loans/create');
        }
    }

    public function detail($id)
    {
        $this->requireAdmin();
        try {
            $loan = $this->loanService->getLoanById((int)$id);
            if (!$loan) {
                $this->setFlash('error', 'Loan not found');
                $this->redirect('/admin/company-loans');
                return;
            }

            $installments = $this->loanService->getInstallments((int)$id);
            $guarantors = $this->loanService->getGuarantors((int)$id);
            $documents = $this->loanService->getDocuments((int)$id);
            $activityLog = $this->loanService->getActivityLog((int)$id);
            $earlySettlement = $this->loanService->calculateEarlySettlement((int)$id);

            $this->data['page_title'] = 'Loan Detail - ' . $loan['loan_number'];
            $this->data['page_heading'] = 'Loan: ' . $loan['loan_number'];
            $this->data['loan'] = $loan;
            $this->data['installments'] = $installments;
            $this->data['guarantors'] = $guarantors;
            $this->data['documents'] = $documents;
            $this->data['activity_log'] = $activityLog;
            $this->data['early_settlement'] = $earlySettlement;

            $this->render('admin/loans/company/detail', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error loading loan: ' . $e->getMessage());
            $this->redirect('/admin/company-loans');
        }
    }

    public function disburse($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $result = $this->loanService->disburseLoan((int)$id, (int)($_SESSION['admin_id'] ?? 0));
            if ($result['success']) {
                $this->setFlash('success', 'Loan disbursed successfully');
            } else {
                $this->setFlash('error', $result['error']);
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error disbursing loan: ' . $e->getMessage());
        }
        $this->redirect('/admin/company-loans/' . $id);
    }

    public function markDefault($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->loanService->markDefault((int)$id);
        if ($result['success']) {
            $this->setFlash('success', 'Loan marked as defaulted');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirect('/admin/company-loans/' . $id);
    }

    public function foreclose($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $settlementAmount = (float)($_POST['settlement_amount'] ?? 0);
        $result = $this->loanService->forecloseLoan((int)$id, $settlementAmount);
        if ($result['success']) {
            $this->setFlash('success', 'Loan foreclosed successfully');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirect('/admin/company-loans/' . $id);
    }

    public function addGuarantor($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->loanService->addGuarantor((int)$id, $_POST);
        if ($result['success']) {
            $this->setFlash('success', 'Guarantor added successfully');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirect('/admin/company-loans/' . $id);
    }

    public function recordPayment($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $installmentId = (int)($_POST['installment_id'] ?? 0);
        if ($installmentId <= 0) {
            $this->setFlash('error', 'Invalid installment');
            $this->redirect('/admin/company-loans/' . $id);
            return;
        }

        $result = $this->loanService->recordPayment($installmentId, $_POST);
        if ($result['success']) {
            $this->setFlash('success', 'Payment recorded successfully');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirect('/admin/company-loans/' . $id);
    }

    public function generateDocument($id, $type)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        try {
            $result = match ($type) {
                'agreement' => $this->docService->generateLoanAgreement((int)$id),
                'promissory' => $this->docService->generatePromissoryNote((int)$id),
                'demand' => $this->docService->generateDemandLetter((int)$id, (int)($_POST['installment_no'] ?? 1)),
                'default_notice' => $this->docService->generateDefaultNotice((int)$id),
                default => ['success' => false, 'error' => 'Unknown document type'],
            };

            if ($result['success']) {
                $this->setFlash('success', 'Document generated successfully');
            } else {
                $this->setFlash('error', $result['error']);
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error generating document: ' . $e->getMessage());
        }
        $this->redirect('/admin/company-loans/' . $id);
    }

    public function viewDocument($docId)
    {
        $this->requireAdmin();
        try {
            $stmt = $this->db->prepare("SELECT * FROM loan_documents WHERE id = ?");
            $stmt->execute([(int)$docId]);
            $doc = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$doc) {
                $this->setFlash('error', 'Document not found');
                $this->redirect('/admin/company-loans');
                return;
            }

            header('Content-Type: text/html; charset=utf-8');
            echo $doc['content'];
            exit;
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error viewing document');
            $this->redirect('/admin/company-loans');
        }
    }

    public function signDocument($docId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->docService->signDocument((int)$docId);
        if ($result['success']) {
            $this->setFlash('success', 'Document marked as signed');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirectToReferrer('/admin/company-loans');
    }

    public function finalizeDocument($docId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->docService->finalizeDocument((int)$docId);
        if ($result['success']) {
            $this->setFlash('success', 'Document finalized');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirectToReferrer('/admin/company-loans');
    }

    // --- Offer Management ---

    public function offers()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Loan Offers';
        $this->data['page_heading'] = 'Loan Offers';
        $this->data['offers'] = $this->loanService->getOffers(false);
        $this->render('admin/loans/company/offers', $this->data);
    }

    public function offerCreate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->loanService->addOffer($_POST);
        if ($result['success']) {
            $this->setFlash('success', 'Offer created successfully');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirect('/admin/company-loans/offers');
    }

    public function offerUpdate($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->loanService->updateOffer((int)$id, $_POST);
        if ($result['success']) {
            $this->setFlash('success', 'Offer updated successfully');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirect('/admin/company-loans/offers');
    }

    // --- Early Incentive Management ---

    public function earlyIncentives()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Early Payment Incentives';
        $this->data['page_heading'] = 'Early Payment Incentives';
        $this->data['incentives'] = $this->loanService->getEarlyIncentives(false);
        $this->render('admin/loans/company/incentives', $this->data);
    }

    public function earlyIncentiveCreate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $result = $this->loanService->addEarlyIncentive($_POST);
        if ($result['success']) {
            $this->setFlash('success', 'Incentive created successfully');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirect('/admin/company-loans/early-incentives');
    }

    // --- Calculator / Preview ---

    public function calculator()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Loan Calculator';
        $this->data['page_heading'] = 'Loan Calculator';
        $this->data['offers'] = $this->loanService->getOffers();

        $result = null;
        if (!empty($_GET['calculate'])) {
            $amount = (float)($_GET['amount'] ?? 1000000);
            $rate = (float)($_GET['rate'] ?? 10);
            $tenure = (int)($_GET['tenure'] ?? 60);
            $interestFreeMonths = (int)($_GET['interest_free_months'] ?? 0);

            $result = $this->offerService->calculateSavings($amount, $rate, $tenure, $interestFreeMonths);
        }

        $this->data['calculation'] = $result;
        $this->render('admin/loans/company/calculator', $this->data);
    }

    // --- API: Check offer eligibility ---

    public function checkEligibility()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        $offerId = (int)($_GET['offer_id'] ?? 0);
        $amount = (float)($_GET['amount'] ?? 0);
        $tenure = (int)($_GET['tenure'] ?? 0);

        if (!$offerId) {
            echo json_encode(['eligible' => false, 'issues' => ['No offer selected']]);
            exit;
        }

        $offers = $this->loanService->getOffers();
        $offer = null;
        foreach ($offers as $o) {
            if ((int)$o['id'] === $offerId) {
                $offer = $o;
                break;
            }
        }

        if (!$offer) {
            echo json_encode(['eligible' => false, 'issues' => ['Offer not found']]);
            exit;
        }

        $result = $this->offerService->checkEligibility($amount, $tenure, $offer);
        echo json_encode($result);
        exit;
    }

    // --- Penalty Cron ---

    public function runPenalties()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $this->loanService->logActivity(0, 'cron_manual', 'Admin manually triggered penalty run');
        $result = $this->loanService->applyDailyPenalties();

        if ($result['success']) {
            $this->setFlash('success', 'Penalties applied: ' . ($result['penalties_applied'] ?? 0) . ' installments updated');
        } else {
            $this->setFlash('error', $result['error'] ?? 'Failed to apply penalties');
        }
        $this->redirect('/admin/company-loans');
    }

    protected function redirectToReferrer(string $default): void
    {
        $referrer = $_SERVER['HTTP_REFERER'] ?? $default;
        $this->redirect($referrer);
    }
}
