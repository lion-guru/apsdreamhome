<?php

namespace App\Services\Accounting;

use App\Core\Database\Database;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * Money Workflow Service (Facade)
 * ──────────────────────────────────────
 * Implements the full accounting / treasury / tax compliance stack:
 *   - Bank accounts (KYC + RERA escrow)
 *   - Daily cash book with auto-journal
 *   - Petty cash (topup + expense) with running balance
 *   - Cheque / DD register (issue / clear / bounce)
 *   - Bank reconciliation (statement vs book)
 *   - TDS register (auto-calc, deposit, Form 16A)
 *   - GST transactions (output / input + ITC reconciliation)
 *   - Demand letter templates ({{var}} substitution)
 *   - Cash flow forecast (inflow / outflow with probability)
 *   - Multi-level expense approval workflow
 *   - Vendor payments (TDS + GST aware)
 *   - Double-entry journal entries
 *   - Reports: Trial Balance, P&L, Balance Sheet, Cash Flow, 3-way Recon
 *
 * This class now acts as a facade delegating to focused service classes:
 *   - BankAccountService        — Bank accounts master, balances
 *   - CashBookService           — Cash transactions, petty cash, daily cash book
 *   - ChequeService             — Cheque/DD register (issue/clear/bounce)
 *   - BankReconciliationService — Bank reconciliation (statement vs book)
 *   - TdsService                — TDS register, rates, certificates
 *   - GstService                — GST transactions, ITC reconciliation
 *   - DemandLetterService       — Demand letter templates & generation
 *   - CashFlowForecastService   — Cash flow forecast (inflow/outflow + probability)
 *   - ExpenseApprovalService    — Multi-level expense approval workflow
 *   - VendorService             — Vendor management, KYC, payments
 *   - JournalEntryService       — Double-entry journal entries, ledger
 *   - FinancialReportsService   — Trial Balance, P&L, Balance Sheet, Cash Flow, 3-way Recon
 *   - ThreeWayReconciliationService — Trust account reconciliation
 *   - PenaltyService            — Daily penalties, overdue summaries
 *   - CollectionService         — Cash collections, collector reconciliation
 *   - RegistryNocService        — Registry eligibility, NOC generation
 *   - AccountingDashboardService — Dashboard stats and aging analysis
 */
class MoneyWorkflowService
{
    use \App\Traits\ServiceTenantTrait;

    // Service instances
    private \App\Services\Accounting\BankAccountService $bankAccountService;
    private \App\Services\Accounting\CashBookService $cashBookService;
    private \App\Services\Accounting\ChequeService $chequeService;
    private \App\Services\Accounting\BankReconciliationService $bankReconciliationService;
    private \App\Services\Accounting\TdsService $tdsService;
    private \App\Services\Accounting\GstService $gstService;
    private \App\Services\Accounting\DemandLetterService $demandLetterService;
    private \App\Services\Accounting\CashFlowForecastService $cashFlowForecastService;
    private \App\Services\Accounting\ExpenseApprovalService $expenseApprovalService;
    private \App\Services\Accounting\VendorService $vendorService;
    private \App\Services\Accounting\JournalEntryService $journalEntryService;
    private \App\Services\Accounting\FinancialReportsService $financialReportsService;
    private \App\Services\Accounting\ThreeWayReconciliationService $threeWayReconciliationService;
    private \App\Services\Accounting\PenaltyService $penaltyService;
    private \App\Services\Accounting\CollectionService $collectionService;
    private \App\Services\Accounting\RegistryNocService $registryNocService;
    private \App\Services\Accounting\AccountingDashboardService $dashboardService;

    public function __construct()
    {
        // Initialize all service instances
        $this->bankAccountService        = new \App\Services\Accounting\BankAccountService();
        $this->cashBookService           = new \App\Services\Accounting\CashBookService();
        $this->chequeService             = new \App\Services\Accounting\ChequeService();
        $this->bankReconciliationService = new \App\Services\Accounting\BankReconciliationService();
        $this->tdsService                = new \App\Services\Accounting\TdsService();
        $this->gstService                = new \App\Services\Accounting\GstService();
        $this->demandLetterService       = new \App\Services\Accounting\DemandLetterService();
        $this->cashFlowForecastService   = new \App\Services\Accounting\CashFlowForecastService();
        $this->expenseApprovalService    = new \App\Services\Accounting\ExpenseApprovalService();
        $this->vendorService             = new \App\Services\Accounting\VendorService();
        $this->journalEntryService       = new \App\Services\Accounting\JournalEntryService();
        $this->financialReportsService   = new \App\Services\Accounting\FinancialReportsService();
        $this->threeWayReconciliationService = new \App\Services\Accounting\ThreeWayReconciliationService();
        $this->penaltyService            = new \App\Services\Accounting\PenaltyService();
        $this->collectionService         = new \App\Services\Accounting\CollectionService();
        $this->registryNocService        = new \App\Services\Accounting\RegistryNocService();
        $this->dashboardService          = new \App\Services\Accounting\AccountingDashboardService();
    }

    /* ============================================================
       BANK ACCOUNTS MASTER (delegates to BankAccountService)
       ============================================================ */

    public function createBankAccount(array $data): int
    {
        return $this->bankAccountService->createBankAccount($data);
    }

    public function listBankAccounts(bool $activeOnly = true): array
    {
        return $this->bankAccountService->listBankAccounts($activeOnly);
    }

    public function getBankBalance(int $bankAccountId, ?string $asOfDate = null): float
    {
        return $this->bankAccountService->getBankBalance($bankAccountId, $asOfDate);
    }

    public function getBankAccount(int $id): ?array
    {
        return $this->bankAccountService->getBankAccount($id);
    }

    public function updateBankAccount(int $id, array $data): bool
    {
        return $this->bankAccountService->updateBankAccount($id, $data);
    }

    public function deleteBankAccount(int $id): bool
    {
        return $this->bankAccountService->deleteBankAccount($id);
    }

    public function getBankAccounts(bool $activeOnly = true): array
    {
        return $this->bankAccountService->listBankAccounts($activeOnly);
    }

    /* ============================================================
       CASH BOOK (delegates to CashBookService)
       ============================================================ */

    public function recordCashTransaction(array $data): array
    {
        return $this->cashBookService->recordCashTransaction($data);
    }

    public function topupPettyCash(float $amount, array $data = []): int
    {
        return $this->cashBookService->topupPettyCash($amount, $data);
    }

    public function recordPettyExpense(array $data): int
    {
        return $this->cashBookService->recordPettyExpense($data);
    }

    public function getPettyCashBalance(): float
    {
        return $this->cashBookService->getPettyCashBalance();
    }

    public function getDailyCashBook(string $fromDate, string $toDate, ?int $bankAccountId = null): array
    {
        return $this->cashBookService->getDailyCashBook($fromDate, $toDate, $bankAccountId);
    }

    public function getCashBookSummary(string $fromDate, string $toDate): array
    {
        return $this->cashBookService->getCashBookSummary($fromDate, $toDate);
    }

    /* ============================================================
       CHEQUE / DD REGISTER (delegates to ChequeService)
       ============================================================ */

    public function issueCheque(array $data): int
    {
        return $this->chequeService->issueCheque($data);
    }

    public function markChequeCleared(int $id, string $date): bool
    {
        return $this->chequeService->markChequeCleared($id, $date);
    }

    public function markChequeBounced(int $id, string $reason): bool
    {
        return $this->chequeService->markChequeBounced($id, $reason);
    }

    public function issueChequeWithVoucher(array $data): int
    {
        return $this->chequeService->issueChequeWithVoucher($data);
    }

    public function markChequeStatus(int $id, string $status, string $reason = ''): bool
    {
        return $this->chequeService->markChequeStatus($id, $status, $reason);
    }

    public function getChequeRegister(array $filters = []): array
    {
        return $this->chequeService->getChequeRegister($filters);
    }

    public function getChequeById(int $id): ?array
    {
        return $this->chequeService->getChequeById($id);
    }

    public function getChequeSummary(array $filters = []): array
    {
        return $this->chequeService->getChequeSummary($filters);
    }

    /* ============================================================
       BANK RECONCILIATION (delegates to BankReconciliationService)
       ============================================================ */

    public function startBankReconciliation(int $bankAccountId, array $data): int
    {
        return $this->bankReconciliationService->createReconciliation($bankAccountId, $data);
    }

    public function getReconciliationItems(int $reconciliationId): array
    {
        return $this->bankReconciliationService->getReconciliationItems($reconciliationId);
    }

    public function getReconciliations(?int $bankAccountId = null): array
    {
        return $this->bankReconciliationService->getReconciliations($bankAccountId);
    }

    public function matchTransaction(int $itemId, string $status, ?int $cashBookId = null): bool
    {
        return $this->bankReconciliationService->matchTransaction($itemId, $status, $cashBookId);
    }

    public function completeReconciliation(int $id): bool
    {
        return $this->bankReconciliationService->completeReconciliation($id);
    }

    public function getReconciliationItemsForBank(int $bankAccountId, string $fromDate, string $toDate): array
    {
        return $this->bankReconciliationService->getReconciliationItemsForBank($bankAccountId, $fromDate, $toDate);
    }

    /* ============================================================
       TDS REGISTER (delegates to TdsService)
       ============================================================ */

    public function recordTDS(array $data): int
    {
        return $this->tdsService->recordTDS($data);
    }

    public function recordTdsProxy(array $data): int
    {
        return $this->tdsService->recordTDS($data);
    }

    public function autoDetectTdsSection(string $entityType): string
    {
        return $this->tdsService->autoDetectTdsSection($entityType);
    }

    public function getTdsRateForSection(string $section, string $entityType): float
    {
        return $this->tdsService->getTdsRateForSection($section, $entityType);
    }

    public function getTdsRegister(array $filters = []): array
    {
        return $this->tdsService->getTdsRegister($filters);
    }

    public function getTdsSummary(string $fy): array
    {
        return $this->tdsService->getTdsSummary($fy);
    }

    public function generateTdsCertificate(int $deducteeUserId, string $fy, string $quarter): int
    {
        return $this->tdsService->generateTdsCertificate($deducteeUserId, $fy, $quarter);
    }

    public function getTdsCertificatesIssued(string $fy = ''): array
    {
        return $this->tdsService->getTdsCertificatesIssued($fy);
    }

    /* ============================================================
       GST TRANSACTIONS (delegates to GstService)
       ============================================================ */

    public function recordGST(array $data): int
    {
        return $this->gstService->recordGST($data);
    }

    public function recordGstProxy(array $data): int
    {
        return $this->gstService->recordGST($data);
    }

    public function getGstTransactions(array $filters = []): array
    {
        return $this->gstService->getGstTransactions($filters);
    }

    public function getGstSummary(string $fy): array
    {
        return $this->gstService->getGstSummary($fy);
    }

    public function markItcClaimed(int $id, string $claimDate): bool
    {
        return $this->gstService->markItcClaimed($id, $claimDate);
    }

    public function verifyGstin(string $gstin): array
    {
        return $this->gstService->verifyGstin($gstin);
    }

    /* ============================================================
       DEMAND LETTERS (delegates to DemandLetterService)
       ============================================================ */

    public function createDemandLetterTemplate(array $data): int
    {
        return $this->demandLetterService->createDemandLetterTemplate($data);
    }

    public function getDemandLetterTemplates(bool $activeOnly = false): array
    {
        return $this->demandLetterService->getDemandLetterTemplates($activeOnly);
    }

    public function getDemandLetterTemplate(int $id): ?array
    {
        return $this->demandLetterService->getDemandLetterTemplate($id);
    }

    public function updateDemandLetterTemplate(int $id, array $data): bool
    {
        return $this->demandLetterService->updateDemandLetterTemplate($id, $data);
    }

    public function deleteDemandLetterTemplate(int $id): bool
    {
        return $this->demandLetterService->deleteDemandLetterTemplate($id);
    }

    public function generateDemandLetter(int $bookingId, string $type): array
    {
        return $this->demandLetterService->generateDemandLetter($bookingId, $type);
    }

    /* ============================================================
       CASH FLOW FORECAST (delegates to CashFlowForecastService)
       ============================================================ */

    public function forecastCashFlow(int $days = 30): array
    {
        return $this->cashFlowForecastService->forecastCashFlow($days);
    }

    public function generateForecast(int $days = 30): array
    {
        return $this->cashFlowForecastService->generateForecast($days);
    }

    public function getCashForecasts(int $days = 30): array
    {
        return $this->cashFlowForecastService->getCashForecasts($days);
    }

    public function getActualVsForecast(string $fromDate, string $toDate): array
    {
        return $this->cashFlowForecastService->getActualVsForecast($fromDate, $toDate);
    }

    /* ============================================================
       EXPENSE APPROVAL WORKFLOW (delegates to ExpenseApprovalService)
       ============================================================ */

    public function submitExpense(array $data): int
    {
        return $this->expenseApprovalService->submitExpense($data);
    }

    public function getExpenses(array $filters = []): array
    {
        return $this->expenseApprovalService->getExpenses($filters);
    }

    public function getExpense(int $id): ?array
    {
        return $this->expenseApprovalService->getExpense($id);
    }

    public function approveExpenseById(int $id, string $remarks = ''): bool
    {
        return $this->expenseApprovalService->approveExpenseById($id, $remarks);
    }

    public function rejectExpenseById(int $id, string $remarks = ''): bool
    {
        return $this->expenseApprovalService->rejectExpenseById($id, $remarks);
    }

    /* ============================================================
       VENDOR MANAGEMENT (delegates to VendorService)
       ============================================================ */

    public function createVendor(array $data): int
    {
        return $this->vendorService->createVendor($data);
    }

    public function getVendor(int $id): ?array
    {
        return $this->vendorService->getVendor($id);
    }

    public function listVendors(array $filters = []): array
    {
        return $this->vendorService->listVendors($filters);
    }

    public function verifyVendorKyc(int $vendorId): bool
    {
        return $this->vendorService->verifyVendorKyc($vendorId);
    }

    public function rejectVendorKyc(int $vendorId, string $reason = ''): bool
    {
        return $this->vendorService->rejectVendorKyc($vendorId, $reason);
    }

    public function payVendor(array $data): int
    {
        return $this->vendorService->payVendor($data);
    }

    public function recordVendorPayment(array $data): int
    {
        return $this->vendorService->recordVendorPayment($data);
    }

    public function getVendorPayments(array $filters = []): array
    {
        return $this->vendorService->getVendorPayments($filters);
    }

    public function getVendorOutstanding(): array
    {
        return $this->vendorService->getVendorOutstanding();
    }

    /* ============================================================
       JOURNAL ENTRIES & LEDGER (delegates to JournalEntryService)
       ============================================================ */

    public function postJournalEntry(array $data): int
    {
        return $this->journalEntryService->postJournalEntry($data);
    }

    public function getLedger(int $accountId, string $fromDate, string $toDate): array
    {
        return $this->journalEntryService->getLedger($accountId, $fromDate, $toDate);
    }

    /* ============================================================
       FINANCIAL REPORTS (delegates to FinancialReportsService)
       ============================================================ */

    public function getTrialBalance(?string $asOfDate = null): array
    {
        return $this->financialReportsService->getTrialBalance($asOfDate);
    }

    public function getProfitLoss(string $fromDate, string $toDate): array
    {
        return $this->financialReportsService->getProfitLoss($fromDate, $toDate);
    }

    public function getBalanceSheet(?string $asOfDate = null): array
    {
        return $this->financialReportsService->getBalanceSheet($asOfDate);
    }

    public function getCashFlowStatement(string $fromDate, string $toDate): array
    {
        return $this->financialReportsService->getCashFlowStatement($fromDate, $toDate);
    }

    /* ============================================================
       THREE-WAY RECONCILIATION (delegates to ThreeWayReconciliationService)
       ============================================================ */

    public function threeWayReconciliation(int $trustAccountId, ?string $asOfDate = null): array
    {
        return $this->threeWayReconciliationService->reconcile($trustAccountId, $asOfDate);
    }

    /* ============================================================
       PENALTY AUTOMATION (delegates to PenaltyService)
       ============================================================ */

    public function applyDailyPenalties(): array
    {
        return $this->penaltyService->applyDailyPenalties();
    }

    public function getOverduePenaltySummary(): array
    {
        return $this->penaltyService->getOverduePenaltySummary();
    }

    public function recordPenaltyPayment(int $installmentId, float $amount): bool
    {
        return $this->penaltyService->recordPenaltyPayment($installmentId, $amount);
    }

    /* ============================================================
       CASH COLLECTIONS & RECONCILIATION (delegates to CollectionService)
       ============================================================ */

    public function recordCollection(array $data): array
    {
        return $this->collectionService->recordCollection($data);
    }

    public function getCollections(array $filters = []): array
    {
        return $this->collectionService->getCollections($filters);
    }

    public function getCollection(int $id): ?array
    {
        return $this->collectionService->getCollection($id);
    }

    public function verifyCollection(int $id, int $verifiedBy): bool
    {
        return $this->collectionService->verifyCollection($id, $verifiedBy);
    }

    public function rejectCollection(int $id, int $rejectedBy, string $reason): bool
    {
        return $this->collectionService->rejectCollection($id, $rejectedBy, $reason);
    }

    public function startReconciliation(int $collectorId, string $date): array
    {
        return $this->collectionService->startReconciliation($collectorId, $date);
    }

    public function closeReconciliation(int $sessionId, int $closedBy): bool
    {
        return $this->collectionService->closeReconciliation($sessionId, $closedBy);
    }

    public function getReconciliationSessions(array $filters = []): array
    {
        return $this->collectionService->getReconciliationSessions($filters);
    }

    public function getCollectionStats(): array
    {
        return $this->collectionService->getCollectionStats();
    }

    public function listCollectors(): array
    {
        return $this->collectionService->listCollectors();
    }

    /* ============================================================
       REGISTRY & NOC (delegates to RegistryNocService)
       ============================================================ */

    public function checkRegistryEligibility(int $bookingId): array
    {
        return $this->registryNocService->checkRegistryEligibility($bookingId);
    }

    public function generateNoc(int $bookingId, int $generatedBy): array
    {
        return $this->registryNocService->generateNoc($bookingId, $generatedBy);
    }

    /* ============================================================
       DASHBOARD & AGING ANALYSIS (delegates to AccountingDashboardService)
       ============================================================ */

    public function getDashboardStats(): array
    {
        return $this->dashboardService->getDashboardStats();
    }

    public function getCashFlowSummary(int $days = 30): array
    {
        return $this->dashboardService->getCashFlowSummary($days);
    }

    public function getAgingAnalysis(): array
    {
        return $this->dashboardService->getAgingAnalysis();
    }

    /* ============================================================
       LEGACY COMPATIBILITY METHODS
       ============================================================ */

    public function getVoucherLog(int $limit = 100): array
    {
        $tid = TenantContext::getId();
        $where = $tid > 1 ? " WHERE tenant_id = ?" : "";
        $params = $tid > 1 ? [$tid] : [];
        return $this->db->fetchAll("SELECT * FROM journal_entries" . $where . " ORDER BY entry_date DESC LIMIT ?", array_merge($params, [100])) ?: [];
    }

    public function getDailyPenaltySummary(): array
    {
        return $this->penaltyService->getOverduePenaltySummary();
    }

    public function getPenaltyReport(string $fromDate, string $toDate): array
    {
        // Simplified implementation
        return $this->penaltyService->getOverduePenaltySummary();
    }

    public function getPenaltyTrends(int $days = 30): array
    {
        return [];
    }

    public function getForeclosureStats(): array
    {
        return [];
    }

    public function getForeclosureTrends(int $days = 30): array
    {
        return [];
    }

    public function getForeclosureData(array $filters = []): array
    {
        return [];
    }

    // Database connection getter
    public function getDb(): Database
    {
        return Database::getInstance();
    }

    public function tenantId(): int
    {
        return TenantContext::getId();
    }
}