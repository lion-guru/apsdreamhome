<?php

namespace App\Services\Loan;

use App\Core\Database;
use App\Core\Middleware\TenantContext;
use PDO;
use \App\Traits\ServiceTenantTrait;

class CompanyLoanService
{
    use \App\Traits\ServiceTenantTrait;

    protected PDO $db;
    protected int $tenantId;
    protected const EMI_PENALTY_RATE = 18.0;
    protected const PENALTY_GRACE_DAYS = 5;
    protected const INTEREST_FREE_MISSED_LIMIT = 3;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->db = $pdo;
        } else {
            $this->db = Database::getInstance()->getConnection();
        }
        $this->tenantId = TenantContext::getId();
    }

    public function createLoan(array $data): array
    {
        try {
            // Build loan number: CL-YYYY-MM-XXXX
            $prefix = 'CL-' . date('Y') . '-' . date('m') . '-';
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM company_loans WHERE loan_number LIKE ?");
            $stmt->execute([$prefix . '%']);
            $count = (int)$stmt->fetchColumn() + 1;
            $loanNumber = $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);

            $interestRate = (float)($data['interest_rate'] ?? 10.0);
            $tenureMonths = (int)($data['tenure_months'] ?? 12);
            $loanAmount = (float)($data['loan_amount'] ?? 0);
            $interestFreeMonths = (int)($data['interest_free_months'] ?? 0);
            $interestType = $data['interest_type'] ?? 'reducing';

            // Calculate EMI
            $emiAmount = $this->calculateEMI($loanAmount, $interestRate, $tenureMonths, $interestType, $interestFreeMonths);
            $totalPayable = $emiAmount * $tenureMonths;
            $totalInterest = $totalPayable - $loanAmount;

            $stmt = $this->db->prepare("INSERT INTO company_loans (customer_id, plot_booking_id, property_id, offer_id, loan_number, loan_amount, interest_rate, interest_type, tenure_months, emi_amount, total_payable, total_interest, amount_paid, balance_amount, interest_free_months, interest_free_active, start_date, end_date, status, purpose, notes, created_by, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $startDate = $data['start_date'] ?? date('Y-m-d');
            $endDate = date('Y-m-d', strtotime($startDate . ' + ' . $tenureMonths . ' months'));

            $stmt->execute([
                (int)$data['customer_id'],
                isset($data['plot_booking_id']) ? (int)$data['plot_booking_id'] : null,
                isset($data['property_id']) ? (int)$data['property_id'] : null,
                isset($data['offer_id']) ? (int)$data['offer_id'] : null,
                $loanNumber,
                $loanAmount,
                $interestRate,
                $interestType,
                $tenureMonths,
                $emiAmount,
                $totalPayable,
                $totalInterest,
                $loanAmount,
                $interestFreeMonths,
                $interestFreeMonths > 0 ? 1 : 0,
                $startDate,
                $endDate,
                'pending',
                $data['purpose'] ?? null,
                $data['notes'] ?? null,
                isset($data['created_by']) ? (int)$data['created_by'] : null,
                $this->tenantId
            ]);

            $loanId = (int)$this->db->lastInsertId();

            // Generate installment schedule
            if ($loanId > 0) {
                $this->generateInstallmentSchedule($loanId, $loanAmount, $interestRate, $tenureMonths, $startDate, $interestType, $interestFreeMonths);
                $this->logActivity($loanId, 'loan_created', 'Loan created with amount ₹' . number_format($loanAmount) . ' for ' . $tenureMonths . ' months at ' . $interestRate . '%');
            }

            return ['success' => true, 'loan_id' => $loanId, 'loan_number' => $loanNumber];
        } catch (\Exception $e) {
            error_log('CompanyLoanService::createLoan error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create loan: ' . $e->getMessage()];
        }
    }

    public function getLoanById(int $id): ?array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT l.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                p.plot_number as plot_no, c.name as colony_name, o.name as offer_name, o.offer_type
                FROM company_loans l
                LEFT JOIN users u ON l.customer_id = u.id
                LEFT JOIN plots p ON l.property_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN loan_offers o ON l.offer_id = o.id
                WHERE l.id = ?";
            $params = [$id];
            if ($tid > 1) { $sql .= " AND l.tenant_id = ?"; $params[] = $tid; }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $loan = $stmt->fetch(PDO::FETCH_ASSOC);
            return $loan ?: null;
        } catch (\Exception $e) {
            error_log('CompanyLoanService::getLoanById error: ' . $e->getMessage());
            return null;
        }
    }

    public function listLoans(array $filters = []): array
    {
        try {
            $tid = $this->tenantId;
            $where = [];
            $params = [];

            if ($tid > 1) {
                $where[] = 'l.tenant_id = ?';
                $params[] = $tid;
            }
            if (!empty($filters['status'])) {
                $where[] = 'l.status = ?';
                $params[] = $filters['status'];
            }
            if (!empty($filters['customer_id'])) {
                $where[] = 'l.customer_id = ?';
                $params[] = (int)$filters['customer_id'];
            }
            if (!empty($filters['search'])) {
                $where[] = '(l.loan_number LIKE ? OR u.name LIKE ? OR u.phone LIKE ?)';
                $s = '%' . $filters['search'] . '%';
                $params[] = $s;
                $params[] = $s;
                $params[] = $s;
            }

            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            $limit = (int)($filters['limit'] ?? 50);
            $offset = (int)($filters['offset'] ?? 0);

            $sql = "SELECT l.*, u.name as customer_name, u.phone as customer_phone,
                p.plot_number as plot_no, c.name as colony_name, o.name as offer_name
                FROM company_loans l
                LEFT JOIN users u ON l.customer_id = u.id
                LEFT JOIN plots p ON l.property_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN loan_offers o ON l.offer_id = o.id
                $whereClause
                ORDER BY l.created_at DESC LIMIT $limit OFFSET $offset";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('CompanyLoanService::listLoans error: ' . $e->getMessage());
            return [];
        }
    }

    public function getDashboardStats(): array
    {
        try {
            $tid = $this->tenantId;
            $stats = [];

            $tWhere = '';
            $tParams = [];
            if ($tid > 1) { $tWhere = " AND tenant_id = ?"; $tParams[] = $tid; }

            $iWhere = '';
            $iParams = [];
            if ($tid > 1) {
                $iWhere = " AND li.tenant_id = ?";
                $iParams[] = $tid;
            }

            $stmt1 = $this->db->prepare("SELECT COUNT(*) FROM company_loans WHERE 1=1" . $tWhere);
            $stmt1->execute($tParams);
            $stats['total_loans'] = (int)$stmt1->fetchColumn();

            $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM company_loans WHERE status = 'active'" . $tWhere);
            $stmt2->execute($tParams);
            $stats['active_loans'] = (int)$stmt2->fetchColumn();

            $stmt3 = $this->db->prepare("SELECT COUNT(*) FROM company_loans WHERE status = 'completed'" . $tWhere);
            $stmt3->execute($tParams);
            $stats['completed_loans'] = (int)$stmt3->fetchColumn();

            $stmt4 = $this->db->prepare("SELECT COUNT(*) FROM company_loans WHERE status = 'defaulted'" . $tWhere);
            $stmt4->execute($tParams);
            $stats['defaulted_loans'] = (int)$stmt4->fetchColumn();

            $stmt5 = $this->db->prepare("SELECT COUNT(*) FROM company_loans WHERE status = 'pending'" . $tWhere);
            $stmt5->execute($tParams);
            $stats['pending_loans'] = (int)$stmt5->fetchColumn();

            $stmt6 = $this->db->prepare("SELECT COALESCE(SUM(loan_amount),0) FROM company_loans WHERE status IN ('active','completed')" . $tWhere);
            $stmt6->execute($tParams);
            $stats['total_disbursed'] = (float)$stmt6->fetchColumn();

            $stmt7 = $this->db->prepare("SELECT COALESCE(SUM(balance_amount),0) FROM company_loans WHERE status IN ('active','defaulted')" . $tWhere);
            $stmt7->execute($tParams);
            $stats['total_outstanding'] = (float)$stmt7->fetchColumn();

            $stmt8 = $this->db->prepare("SELECT COALESCE(SUM(amount_paid),0) FROM company_loans WHERE status IN ('active','completed','defaulted')" . $tWhere);
            $stmt8->execute($tParams);
            $stats['total_collected'] = (float)$stmt8->fetchColumn();

            $stmt9 = $this->db->prepare("SELECT COUNT(DISTINCT loan_id) FROM loan_installments li WHERE status = 'overdue'" . $iWhere);
            $stmt9->execute($iParams);
            $stats['overdue_count'] = (int)$stmt9->fetchColumn();

            $stmt10 = $this->db->prepare("SELECT COALESCE(SUM(total_amount - paid_amount),0) FROM loan_installments li WHERE status = 'overdue'" . $iWhere);
            $stmt10->execute($iParams);
            $stats['overdue_amount'] = (float)$stmt10->fetchColumn();

            $stmt11 = $this->db->prepare("SELECT COALESCE(SUM(accrued_penalty),0) FROM loan_installments li WHERE accrued_penalty > 0" . $iWhere);
            $stmt11->execute($iParams);
            $stats['total_penalty'] = (float)$stmt11->fetchColumn();

            return $stats;
        } catch (\Exception $e) {
            error_log('CompanyLoanService::getDashboardStats error: ' . $e->getMessage());
            return [];
        }
    }

    public function calculateEMI(float $principal, float $rate, int $tenureMonths, string $type = 'reducing', int $interestFreeMonths = 0): float
    {
        if ($principal <= 0 || $tenureMonths <= 0) return 0;

        if ($type === 'fixed') {
            $totalInterest = $principal * ($rate / 100) * ($tenureMonths / 12);
            $totalPayable = $principal + $totalInterest;
            return round($totalPayable / $tenureMonths, 2);
        }

        // Reducing balance
        $monthlyRate = ($rate / 12) / 100;
        if ($monthlyRate <= 0) {
            return round($principal / $tenureMonths, 2);
        }

        $pow = pow(1 + $monthlyRate, $tenureMonths);
        $emi = $principal * $monthlyRate * $pow / ($pow - 1);
        return round($emi, 2);
    }

    public function generateInstallmentSchedule(int $loanId, float $amount, float $rate, int $tenureMonths, string $startDate, string $type = 'reducing', int $interestFreeMonths = 0): array
    {
        try {
            $tid = $this->tenantId;
            // Delete existing schedule if regenerating
            $delSql = "DELETE FROM loan_installments WHERE loan_id = ?";
            $delParams = [$loanId];
            if ($tid > 1) { $delSql .= " AND tenant_id = ?"; $delParams[] = $tid; }
            $this->db->prepare($delSql)->execute($delParams);

            $emiAmount = $this->calculateEMI($amount, $rate, $tenureMonths, $type, $interestFreeMonths);
            $installments = [];

            if ($type === 'fixed') {
                $perPrincipal = round($amount / $tenureMonths, 2);
                for ($i = 1; $i <= $tenureMonths; $i++) {
                    $interestAmt = ($amount * ($rate / 100)) / 12;
                    $dueDate = date('Y-m-d', strtotime($startDate . ' + ' . $i . ' months'));
                    $isInterestFree = ($i <= $interestFreeMonths);
                    $waivedInterest = $isInterestFree ? $interestAmt : 0;
                    $actualInterest = $isInterestFree ? 0 : $interestAmt;
                    $totalAmt = $perPrincipal + $actualInterest;

                    $installments[] = [
                        'loan_id' => $loanId,
                        'installment_no' => $i,
                        'due_date' => $dueDate,
                        'principal_amount' => $perPrincipal,
                        'interest_amount' => $actualInterest,
                        'total_amount' => $totalAmt,
                        'waived_interest' => $waivedInterest,
                    ];
                }
            } else {
                // Reducing balance
                $monthlyRate = ($rate / 12) / 100;
                $remainingPrincipal = $amount;

                for ($i = 1; $i <= $tenureMonths; $i++) {
                    $interestAmt = $monthlyRate > 0 ? round($remainingPrincipal * $monthlyRate, 2) : 0;
                    $principalAmt = round($emiAmount - $interestAmt, 2);

                    if ($principalAmt > $remainingPrincipal) {
                        $principalAmt = $remainingPrincipal;
                    }

                    $isInterestFree = ($i <= $interestFreeMonths);
                    $waivedInterest = $isInterestFree ? $interestAmt : 0;
                    $actualInterest = $isInterestFree ? 0 : $interestAmt;
                    $totalAmt = $principalAmt + $actualInterest;

                    $dueDate = date('Y-m-d', strtotime($startDate . ' + ' . $i . ' months'));

                    $installments[] = [
                        'loan_id' => $loanId,
                        'installment_no' => $i,
                        'due_date' => $dueDate,
                        'principal_amount' => $principalAmt,
                        'interest_amount' => $actualInterest,
                        'total_amount' => $totalAmt,
                        'waived_interest' => $waivedInterest,
                    ];

                    $remainingPrincipal -= $principalAmt;
                    if ($remainingPrincipal < 0) $remainingPrincipal = 0;
                }
            }

            // Batch insert
            $stmt = $this->db->prepare("INSERT INTO loan_installments (loan_id, installment_no, due_date, principal_amount, interest_amount, total_amount, waived_interest, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($installments as $inst) {
                $stmt->execute([
                    $inst['loan_id'],
                    $inst['installment_no'],
                    $inst['due_date'],
                    $inst['principal_amount'],
                    $inst['interest_amount'],
                    $inst['total_amount'],
                    $inst['waived_interest'],
                    $this->tenantId
                ]);
            }

            return ['success' => true, 'count' => count($installments)];
        } catch (\Exception $e) {
            error_log('CompanyLoanService::generateInstallmentSchedule error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getInstallments(int $loanId): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT * FROM loan_installments WHERE loan_id = ?";
            $params = [$loanId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY installment_no ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('CompanyLoanService::getInstallments error: ' . $e->getMessage());
            return [];
        }
    }

    public function recordPayment(int $installmentId, array $data): array
    {
        try {
            $tid = $this->tenantId;
            $this->db->beginTransaction();

            $sqlSel = "SELECT * FROM loan_installments WHERE id = ?";
            $selParams = [$installmentId];
            if ($tid > 1) { $sqlSel .= " AND tenant_id = ?"; $selParams[] = $tid; }
            $stmt = $this->db->prepare($sqlSel);
            $stmt->execute($selParams);
            $installment = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$installment) {
                throw new \Exception('Installment not found');
            }

            $paidAmount = (float)($data['amount'] ?? 0);
            $newPaid = $installment['paid_amount'] + $paidAmount;
            $status = ($newPaid >= $installment['total_amount']) ? 'paid' : 'partial';

            $updInstSql = "UPDATE loan_installments SET paid_amount = ?, status = ?, paid_at = NOW(), payment_method = ?, transaction_id = ?, notes = CONCAT(COALESCE(notes,''), ?) WHERE id = ?";
            $updInstParams = [$newPaid, $status, $data['payment_method'] ?? null, $data['transaction_id'] ?? null, ($data['notes'] ?? '') ? ' | ' . $data['notes'] : '', $installmentId];
            if ($tid > 1) { $updInstSql .= " AND tenant_id = ?"; $updInstParams[] = $tid; }
            $stmt = $this->db->prepare($updInstSql);
            $stmt->execute($updInstParams);

            // Update loan balance
            $updLoanSql = "UPDATE company_loans SET amount_paid = amount_paid + ?, balance_amount = loan_amount - amount_paid WHERE id = ?";
            $updLoanParams = [$paidAmount, $installment['loan_id']];
            if ($tid > 1) { $updLoanSql .= " AND tenant_id = ?"; $updLoanParams[] = $tid; }
            $loanStmt = $this->db->prepare($updLoanSql);
            $loanStmt->execute($updLoanParams);

            // Check if loan is fully paid
            $loan = $this->getLoanById($installment['loan_id']);
            if ($loan && $loan['balance_amount'] <= 0) {
                $compSql = "UPDATE company_loans SET status = 'completed', closed_at = NOW() WHERE id = ?";
                $compParams = [$installment['loan_id']];
                if ($tid > 1) { $compSql .= " AND tenant_id = ?"; $compParams[] = $tid; }
                $this->db->prepare($compSql)->execute($compParams);
            }

            $this->db->commit();

            $this->logActivity($installment['loan_id'], 'payment_received', 'Payment of ₹' . number_format($paidAmount) . ' received for installment #' . $installment['installment_no']);

            return ['success' => true, 'status' => $status];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log('CompanyLoanService::recordPayment error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function disburseLoan(int $loanId, int $userId): array
    {
        try {
            $tid = $this->tenantId;
            $loan = $this->getLoanById($loanId);
            if (!$loan) return ['success' => false, 'error' => 'Loan not found'];
            if ($loan['status'] !== 'pending') return ['success' => false, 'error' => 'Loan is not in pending status'];

            $sql = "UPDATE company_loans SET status = 'active', disbursed_at = NOW(), disbursed_by = ? WHERE id = ?";
            $params = [$userId, $loanId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);

            $this->logActivity($loanId, 'loan_disbursed', 'Loan disbursed of ₹' . number_format($loan['loan_amount']));

            return ['success' => true];
        } catch (\Exception $e) {
            error_log('CompanyLoanService::disburseLoan error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function markDefault(int $loanId): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "UPDATE company_loans SET status = 'defaulted' WHERE id = ?";
            $params = [$loanId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $this->db->prepare($sql)->execute($params);
            $this->logActivity($loanId, 'loan_defaulted', 'Loan marked as defaulted');
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function forecloseLoan(int $loanId, float $settlementAmount): array
    {
        try {
            $tid = $this->tenantId;
            $this->db->beginTransaction();

            $fcSql = "UPDATE company_loans SET status = 'foreclosed', amount_paid = amount_paid + ?, balance_amount = 0, closed_at = NOW() WHERE id = ?";
            $fcParams = [$settlementAmount, $loanId];
            if ($tid > 1) { $fcSql .= " AND tenant_id = ?"; $fcParams[] = $tid; }
            $this->db->prepare($fcSql)->execute($fcParams);

            // Mark remaining installments as paid
            $instSql = "UPDATE loan_installments SET status = 'paid', paid_amount = total_amount WHERE loan_id = ? AND status = 'pending'";
            $instParams = [$loanId];
            if ($tid > 1) { $instSql .= " AND tenant_id = ?"; $instParams[] = $tid; }
            $this->db->prepare($instSql)->execute($instParams);

            $this->db->commit();
            $this->logActivity($loanId, 'loan_foreclosed', 'Loan foreclosed with settlement of ₹' . number_format($settlementAmount));

            return ['success' => true];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function applyDailyPenalties(): array
    {
        try {
            $tid = $this->tenantId;
            $today = date('Y-m-d');
            $graceDate = date('Y-m-d', strtotime('-' . self::PENALTY_GRACE_DAYS . ' days'));

            $selSql = "SELECT id, loan_id, total_amount, paid_amount, accrued_penalty FROM loan_installments WHERE due_date <= ? AND status IN ('pending','overdue') AND total_amount > paid_amount";
            $selParams = [$graceDate];
            if ($tid > 1) { $selSql .= " AND tenant_id = ?"; $selParams[] = $tid; }
            $stmt = $this->db->prepare($selSql);
            $stmt->execute($selParams);
            $overdue = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count = 0;
            foreach ($overdue as $inst) {
                $outstanding = $inst['total_amount'] - $inst['paid_amount'];
                $dailyPenalty = round($outstanding * (self::EMI_PENALTY_RATE / 100) / 365, 2);
                $newPenalty = round((float)$inst['accrued_penalty'] + $dailyPenalty, 2);

                $updSql = "UPDATE loan_installments SET accrued_penalty = ?, status = 'overdue' WHERE id = ?";
                $updParams = [$newPenalty, $inst['id']];
                if ($tid > 1) { $updSql .= " AND tenant_id = ?"; $updParams[] = $tid; }
                $this->db->prepare($updSql)->execute($updParams);
                $count++;
            }

            // Check 3-consecutive-missed rule for interest-free loans
            $clSql = "SELECT id, interest_free_active, interest_free_months FROM company_loans WHERE status = 'active' AND interest_free_active = 1";
            $clParams = [];
            if ($tid > 1) { $clSql .= " AND tenant_id = ?"; $clParams[] = $tid; }
            $activeLoans = $this->db->prepare($clSql);
            $activeLoans->execute($clParams);
            $activeLoansArr = $activeLoans->fetchAll(PDO::FETCH_ASSOC);
            foreach ($activeLoansArr as $loan) {
                $cntSql = "SELECT COUNT(*) FROM loan_installments WHERE loan_id = ? AND status IN ('pending','overdue') AND due_date < ?";
                $cntParams = [$loan['id'], $today];
                if ($tid > 1) { $cntSql .= " AND tenant_id = ?"; $cntParams[] = $tid; }
                $stmt2 = $this->db->prepare($cntSql);
                $stmt2->execute($cntParams);
                $missedCount = (int)$stmt2->fetchColumn();

                if ($missedCount >= self::INTEREST_FREE_MISSED_LIMIT) {
                    $revSql = "UPDATE company_loans SET interest_free_active = 0, missed_consecutive_emis = ?, interest_start_date = ? WHERE id = ?";
                    $revParams = [$missedCount, $today, $loan['id']];
                    if ($tid > 1) { $revSql .= " AND tenant_id = ?"; $revParams[] = $tid; }
                    $this->db->prepare($revSql)->execute($revParams);
                    $this->logActivity($loan['id'], 'interest_free_revoked', 'Interest-free period revoked due to ' . $missedCount . ' consecutive missed EMIs');
                } else {
                    $misSql = "UPDATE company_loans SET missed_consecutive_emis = ? WHERE id = ?";
                    $misParams = [$missedCount, $loan['id']];
                    if ($tid > 1) { $misSql .= " AND tenant_id = ?"; $misParams[] = $tid; }
                    $this->db->prepare($misSql)->execute($misParams);
                }
            }

            return ['success' => true, 'penalties_applied' => $count];
        } catch (\Exception $e) {
            error_log('CompanyLoanService::applyDailyPenalties error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function calculateEarlySettlement(int $loanId, string $incentiveType = 'interest_discount'): array
    {
        try {
            $loan = $this->getLoanById($loanId);
            if (!$loan) return ['success' => false, 'error' => 'Loan not found'];

            $remainingPrincipal = (float)$loan['balance_amount'];
            $pendingInstallments = $this->getInstallments($loanId);
            $remainingInterest = 0;
            foreach ($pendingInstallments as $inst) {
                if ($inst['status'] === 'pending') {
                    $remainingInterest += (float)$inst['interest_amount'];
                }
            }

            // Find applicable incentive
            $stmt = $this->db->prepare("SELECT * FROM loan_early_incentives WHERE is_active = 1 AND incentive_type = ? ORDER BY discount_percent DESC LIMIT 1");
            $stmt->execute([$incentiveType]);
            $incentive = $stmt->fetch(PDO::FETCH_ASSOC);

            $discountAmount = 0;
            $penaltyWaived = 0;

            if ($incentive) {
                if ($incentive['incentive_type'] === 'interest_discount') {
                    $discountAmount = round($remainingInterest * ((float)$incentive['discount_percent'] / 100), 2);
                } elseif ($incentive['incentive_type'] === 'penalty_waiver') {
                    foreach ($pendingInstallments as $inst) {
                        $penaltyWaived += (float)$inst['accrued_penalty'];
                    }
                }
            }

            $totalPenalty = 0;
            foreach ($pendingInstallments as $inst) {
                $totalPenalty += (float)$inst['accrued_penalty'];
            }

            $settlementAmount = $remainingPrincipal + ($remainingInterest - $discountAmount) + ($totalPenalty - $penaltyWaived);

            return [
                'success' => true,
                'remaining_principal' => $remainingPrincipal,
                'remaining_interest' => $remainingInterest,
                'discount_amount' => $discountAmount,
                'penalty_waived' => $penaltyWaived,
                'total_penalty' => $totalPenalty,
                'incentive_applied' => $incentive ? $incentive['name'] : null,
                'settlement_amount' => max($settlementAmount, 0),
            ];
        } catch (\Exception $e) {
            error_log('CompanyLoanService::calculateEarlySettlement error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getOffers(bool $activeOnly = true): array
    {
        try {
            $sql = "SELECT * FROM loan_offers";
            if ($activeOnly) {
                $sql .= " WHERE is_active = 1 AND (valid_until IS NULL OR valid_until >= CURDATE())";
            }
            $sql .= " ORDER BY name ASC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getEarlyIncentives(bool $activeOnly = true): array
    {
        try {
            $sql = "SELECT * FROM loan_early_incentives";
            if ($activeOnly) $sql .= " WHERE is_active = 1";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getGuarantors(int $loanId): array
    {
        try {
            $tid = $this->tenantId;
            $sql = "SELECT * FROM loan_guarantors WHERE loan_id = ?";
            $params = [$loanId];
            if ($tid > 1) { $sql .= " AND tenant_id = ?"; $params[] = $tid; }
            $sql .= " ORDER BY id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function addGuarantor(int $loanId, array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO loan_guarantors (loan_id, name, phone, email, address, pan_number, aadhar_number, occupation, annual_income, relationship, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $loanId,
                $data['name'],
                $data['phone'],
                $data['email'] ?? null,
                $data['address'] ?? null,
                $data['pan_number'] ?? null,
                $data['aadhar_number'] ?? null,
                $data['occupation'] ?? null,
                (float)($data['annual_income'] ?? 0),
                $data['relationship'] ?? null,
                $this->tenantId
            ]);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addOffer(array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO loan_offers (name, description, offer_type, discount_percent, interest_free_months, max_tenure_months, max_amount, terms_conditions, valid_from, valid_until) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['offer_type'] ?? 'interest_free',
                (float)($data['discount_percent'] ?? 0),
                (int)($data['interest_free_months'] ?? 0),
                (int)($data['max_tenure_months'] ?? 0),
                (float)($data['max_amount'] ?? 0),
                $data['terms_conditions'] ?? null,
                $data['valid_from'] ?? null,
                $data['valid_until'] ?? null
            ]);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateOffer(int $id, array $data): array
    {
        try {
            $fields = [];
            $params = [];
            foreach (['name', 'description', 'offer_type', 'discount_percent', 'interest_free_months', 'max_tenure_months', 'max_amount', 'terms_conditions', 'valid_from', 'valid_until', 'is_active'] as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = ?";
                    $params[] = $data[$f];
                }
            }
            if (empty($fields)) return ['success' => false, 'error' => 'No fields to update'];
            $params[] = $id;
            $this->db->prepare("UPDATE loan_offers SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function addEarlyIncentive(array $data): array
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO loan_early_incentives (name, description, incentive_type, calculation_method, discount_percent, fixed_amount, min_remaining_months, max_remaining_months, min_loan_amount, max_loan_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['incentive_type'] ?? 'interest_discount',
                $data['calculation_method'] ?? 'percentage',
                (float)($data['discount_percent'] ?? 0),
                (float)($data['fixed_amount'] ?? 0),
                (int)($data['min_remaining_months'] ?? 0),
                (int)($data['max_remaining_months'] ?? 0),
                (float)($data['min_loan_amount'] ?? 0),
                (float)($data['max_loan_amount'] ?? 0)
            ]);
            return ['success' => true, 'id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getDocuments(int $loanId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM loan_documents WHERE loan_id = ? AND tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$loanId, $this->tenantId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getActivityLog(int $loanId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM loan_activity_log WHERE loan_id = ? AND tenant_id = ? ORDER BY created_at DESC");
            $stmt->execute([$loanId, $this->tenantId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function logActivity(int $loanId, string $action, string $description, ?string $oldValue = null, ?string $newValue = null, ?int $userId = null): void
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO loan_activity_log (loan_id, action, description, old_value, new_value, performed_by, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$loanId, $action, $description, $oldValue, $newValue, $userId ?? ($_SESSION['admin_id'] ?? null), $this->tenantId]);
        } catch (\Exception $e) {
            error_log('CompanyLoanService::logActivity error: ' . $e->getMessage());
        }
    }

    public function getCustomers(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, phone, email FROM users WHERE role IN ('customer','associate','agent') AND tenant_id = ? ORDER BY name ASC");
            $stmt->execute([$this->tenantId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getPlots(): array
    {
        try {
            $stmt = $this->db->prepare("SELECT p.id, p.plot_number, p.total_price, c.name as colony_name FROM plots p LEFT JOIN colonies c ON p.colony_id = c.id WHERE p.status IN ('available','booked') AND p.tenant_id = ? ORDER BY c.name, p.plot_number");
            $stmt->execute([$this->tenantId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }
}
