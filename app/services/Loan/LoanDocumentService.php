<?php

namespace App\Services\Loan;

use App\Core\Database;
use PDO;
use App\Traits\ServiceTenantTrait;

class LoanDocumentService
{
    use ServiceTenantTrait;
    protected PDO $db;
    protected CompanyLoanService $loanService;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo) {
            $this->db = $pdo;
        } else {
            $this->db = Database::getInstance()->getConnection();
        }
        $this->loanService = new CompanyLoanService($this->db);
    }

    public function generateLoanAgreement(int $loanId): array
    {
        try {
            $loan = $this->loanService->getLoanById($loanId);
            if (!$loan) return ['success' => false, 'error' => 'Loan not found'];

            $docNumber = 'LA-' . $loan['loan_number'];

            $content = $this->buildLoanAgreementHtml($loan);

            $stmt = $this->db->prepare("INSERT INTO loan_documents (loan_id, document_type, title, document_number, content, status, generated_by, generated_at, tenant_id) VALUES (?, 'loan_agreement', ?, ?, ?, 'draft', ?, NOW(), ?)");
            $stmt->execute([
                $loanId,
                'Loan Agreement - ' . $loan['loan_number'],
                $docNumber,
                $content,
                $_SESSION['admin_id'] ?? null,
                $this->tenantId(),
            ]);
            $docId = (int)$this->db->lastInsertId();

            $this->loanService->logActivity($loanId, 'document_generated', 'Loan agreement document generated: ' . $docNumber);

            return ['success' => true, 'document_id' => $docId, 'document_number' => $docNumber];
        } catch (\Exception $e) {
            error_log('LoanDocumentService::generateLoanAgreement error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generatePromissoryNote(int $loanId): array
    {
        try {
            $loan = $this->loanService->getLoanById($loanId);
            if (!$loan) return ['success' => false, 'error' => 'Loan not found'];

            $docNumber = 'PN-' . $loan['loan_number'];
            $content = $this->buildPromissoryNoteHtml($loan);

            $stmt = $this->db->prepare("INSERT INTO loan_documents (loan_id, document_type, title, document_number, content, status, generated_by, generated_at, tenant_id) VALUES (?, 'promissory_note', ?, ?, ?, 'draft', ?, NOW(), ?)");
            $stmt->execute([
                $loanId,
                'Promissory Note - ' . $loan['loan_number'],
                $docNumber,
                $content,
                $_SESSION['admin_id'] ?? null,
                $this->tenantId(),
            ]);

            $this->loanService->logActivity($loanId, 'document_generated', 'Promissory note generated: ' . $docNumber);

            return ['success' => true, 'document_id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateDemandLetter(int $loanId, int $installmentNo): array
    {
        try {
            $loan = $this->loanService->getLoanById($loanId);
            if (!$loan) return ['success' => false, 'error' => 'Loan not found'];

            $installments = $this->loanService->getInstallments($loanId);
            $installment = null;
            foreach ($installments as $inst) {
                if ((int)$inst['installment_no'] === $installmentNo) {
                    $installment = $inst;
                    break;
                }
            }
            if (!$installment) return ['success' => false, 'error' => 'Installment not found'];

            $docNumber = 'DL-' . $loan['loan_number'] . '-I' . $installmentNo;
            $content = $this->buildDemandLetterHtml($loan, $installment);

            $stmt = $this->db->prepare("INSERT INTO loan_documents (loan_id, document_type, title, document_number, content, status, generated_by, generated_at, tenant_id) VALUES (?, 'demand_letter', ?, ?, ?, 'draft', ?, NOW(), ?)");
            $stmt->execute([
                $loanId,
                'Demand Letter - Installment #' . $installmentNo . ' - ' . $loan['loan_number'],
                $docNumber,
                $content,
                $_SESSION['admin_id'] ?? null,
                $this->tenantId(),
            ]);

            $this->loanService->logActivity($loanId, 'document_generated', 'Demand letter generated for installment #' . $installmentNo);

            return ['success' => true, 'document_id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function generateDefaultNotice(int $loanId): array
    {
        try {
            $loan = $this->loanService->getLoanById($loanId);
            if (!$loan) return ['success' => false, 'error' => 'Loan not found'];

            $docNumber = 'DN-' . $loan['loan_number'] . '-' . date('Ymd');
            $overdueInstallments = $this->loanService->getInstallments($loanId);
            $overdueList = array_filter($overdueInstallments, fn($i) => $i['status'] === 'overdue');

            $content = $this->buildDefaultNoticeHtml($loan, $overdueList);

            $stmt = $this->db->prepare("INSERT INTO loan_documents (loan_id, document_type, title, document_number, content, status, generated_by, generated_at, tenant_id) VALUES (?, 'default_notice', ?, ?, ?, 'draft', ?, NOW(), ?)");
            $stmt->execute([
                $loanId,
                'Default Notice - ' . $loan['loan_number'],
                $docNumber,
                $content,
                $_SESSION['admin_id'] ?? null,
                $this->tenantId(),
            ]);

            $this->loanService->logActivity($loanId, 'document_generated', 'Default notice generated');

            return ['success' => true, 'document_id' => (int)$this->db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function signDocument(int $documentId): array
    {
        try {
            $this->db->prepare("UPDATE loan_documents SET status = 'signed', signed_by_customer = 1, signed_at = NOW() WHERE id = ?" . $this->tenantSql())->execute([$documentId]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function finalizeDocument(int $documentId): array
    {
        try {
            $this->db->prepare("UPDATE loan_documents SET status = 'final' WHERE id = ?" . $this->tenantSql())->execute([$documentId]);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function buildLoanAgreementHtml(array $loan): string
    {
        $companyName = 'APS Dream Home';
        $companyAddr = 'Your Office Address, City, State - PIN';
        $date = date('d/m/Y');
        $interestFreeNote = '';
        if ((int)$loan['interest_free_months'] > 0) {
            $interestFreeNote = "<p><strong>Interest-Free Offer:</strong> The first {$loan['interest_free_months']} months of this loan are interest-free under promotional offer '{$loan['offer_name']}'. If 3 consecutive EMIs are missed, the interest-free period will be revoked and interest will apply from the date of default.</p>";
        }

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Loan Agreement - {$loan['loan_number']}</title>
<style>
body { font-family: 'Georgia', serif; font-size: 14px; line-height: 1.6; margin: 40px; color: #333; }
h1 { text-align: center; font-size: 22px; text-transform: uppercase; border-bottom: 2px solid #333; padding-bottom: 10px; }
h2 { font-size: 16px; margin-top: 20px; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
td, th { border: 1px solid #999; padding: 8px; text-align: left; }
th { background: #f0f0f0; }
.signature-line { border-top: 1px solid #333; margin-top: 60px; padding-top: 5px; width: 250px; }
.footer { margin-top: 40px; font-size: 12px; color: #666; text-align: center; }
</style></head><body>
<h1>Loan Agreement</h1>
<p style="text-align:center;">Date: $date | Agreement No: {$loan['loan_number']}</p>

<h2>Parties</h2>
<p>This Loan Agreement is made on <strong>$date</strong> between:</p>
<p><strong>LENDER:</strong> $companyName, having its registered office at $companyAddr (hereinafter referred to as the "Company")</p>
<p><strong>BORROWER:</strong> <strong>{$loan['customer_name']}</strong>, Phone: {$loan['customer_phone']}, Email: {$loan['customer_email']} (hereinafter referred to as the "Borrower")</p>

<h2>Loan Details</h2>
<table>
<tr><th>Loan Number</th><td>{$loan['loan_number']}</td></tr>
<tr><th>Loan Amount</th><td>₹ {$loan['loan_amount']}</td></tr>
<tr><th>Interest Rate</th><td>{$loan['interest_rate']}% p.a. ({$loan['interest_type']} balance)</td></tr>
<tr><th>Tenure</th><td>{$loan['tenure_months']} months</td></tr>
<tr><th>EMI Amount</th><td>₹ {$loan['emi_amount']}</td></tr>
<tr><th>Total Payable</th><td>₹ {$loan['total_payable']}</td></tr>
<tr><th>Start Date</th><td>{$loan['start_date']}</td></tr>
<tr><th>End Date</th><td>{$loan['end_date']}</td></tr>
{$interestFreeNote}
<tr><th>Property</th><td>{$loan['colony_name']} - Plot {$loan['plot_no']}</td></tr>
<tr><th>Purpose</th><td>{$loan['purpose']}</td></tr>
</table>

<h2>Terms and Conditions</h2>
<ol>
<li><strong>Repayment:</strong> The Borrower agrees to repay the loan in {$loan['tenure_months']} monthly installments of ₹{$loan['emi_amount']} each.</li>
<li><strong>Payment Due:</strong> Each installment is due on the same day of each month. Late payment attracts penal interest at 18% p.a. on the overdue amount after a 5-day grace period.</li>
<li><strong>Default:</strong> If the Borrower fails to pay 3 consecutive installments, the entire outstanding amount shall become due immediately.</li>
<li><strong>Foreclosure:</strong> The Borrower may prepay the loan at any time. Early settlement incentives as per Company policy shall apply.</li>
<li><strong>Security:</strong> The property purchased through this loan shall serve as security until full repayment.</li>
<li><strong>Guarantors:</strong> The guarantors shall be jointly and severally liable for all dues under this agreement.</li>
<li><strong>Jurisdiction:</strong> This agreement shall be governed by the laws of India. Any disputes shall be subject to the jurisdiction of courts at [City].</li>
</ol>

<h2>Signatures</h2>
<table>
<tr><th>For the Company</th><th>Borrower</th></tr>
<tr><td style="height:80px; vertical-align:bottom;">
<div class="signature-line">Authorized Signatory</div>
</td><td style="height:80px; vertical-align:bottom;">
<div class="signature-line">{$loan['customer_name']}</div>
</td></tr>
</table>

<div class="footer">
<p>$companyName | $companyAddr | This is a computer-generated document</p>
</div>
</body></html>
HTML;
    }

    protected function buildPromissoryNoteHtml(array $loan): string
    {
        $date = date('d/m/Y');
        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Promissory Note - {$loan['loan_number']}</title>
<style>
body { font-family: 'Georgia', serif; font-size: 14px; margin: 40px; line-height: 1.8; }
h1 { text-align: center; }
.content { max-width: 600px; margin: 40px auto; }
.signature { margin-top: 60px; }
.stamp { margin-top: 20px; border: 2px dashed #999; padding: 20px; text-align: center; width: 150px; }
</style></head><body>
<h1>Promissory Note</h1>
<p style="text-align:center;">Date: $date</p>

<div class="content">
<p><strong>FOR VALUE RECEIVED</strong>, I, <strong>{$loan['customer_name']}</strong>, residing at [...], do hereby promise to pay to <strong>APS Dream Home</strong> (the "Company"), on demand or as per the agreed schedule, the sum of <strong>₹ {$loan['loan_amount']}</strong> (Rupees ... only) together with interest at the rate of <strong>{$loan['interest_rate']}% per annum</strong>.</p>

<p>The said sum shall be repaid in <strong>{$loan['tenure_months']}</strong> monthly installments of <strong>₹{$loan['emi_amount']}</strong> each, commencing from {$loan['start_date']}.</p>

<p>In case of default in payment of any installment, I agree to pay penal interest at 18% per annum on the overdue amount.</p>

<p>This Promissory Note is executed in favor of APS Dream Home and forms an integral part of the Loan Agreement No. {$loan['loan_number']}.</p>
</div>

<div class="content">
<table><tr>
<td style="width:50%;"><div class="signature"><p><strong>Witness 1:</strong></p><div style="border-top:1px solid #333; margin-top:50px; padding-top:5px;">Signature</div><p>Name: ....................<br>Address: ................</p></div></td>
<td style="width:50%;"><div class="signature"><p><strong>Borrower:</strong></p><div style="border-top:1px solid #333; margin-top:50px; padding-top:5px;">{$loan['customer_name']}</div><div class="stamp">Revenue Stamp</div></div></td>
</tr></table>
</div>

<p style="text-align:center; margin-top:40px; font-size:12px; color:#666;">This is a computer-generated document</p>
</body></html>
HTML;
    }

    protected function buildDemandLetterHtml(array $loan, array $installment): string
    {
        $date = date('d/m/Y');
        $overdueDays = (new \DateTime($installment['due_date']))->diff(new \DateTime())->days;
        $outstanding = (float)$installment['total_amount'] - (float)$installment['paid_amount'];

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Demand Letter - {$loan['loan_number']}</title>
<style>
body { font-family: Arial, sans-serif; font-size: 13px; margin: 40px; line-height: 1.6; }
h1 { font-size: 18px; text-align: center; }
.header { text-align: right; margin-bottom: 30px; }
.content { margin: 20px 0; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
td, th { border: 1px solid #999; padding: 6px; }
th { background: #f5f5f5; text-align: left; }
.footer { margin-top: 50px; }
</style></head><body>
<div class="header">
<p><strong>APS Dream Home</strong><br>[...]<br>Date: $date</p>
</div>

<h1>Demand Letter</h1>
<p><strong>To,</strong><br>{$loan['customer_name']}<br>{$loan['customer_phone']}<br>{$loan['customer_email']}</p>

<p><strong>Subject:</strong> Payment Demand for Installment #{$installment['installment_no']} - Loan {$loan['loan_number']}</p>

<p>Dear {$loan['customer_name']},</p>

<p>This is to bring to your kind attention that Installment #{$installment['installment_no']} of your Loan ({$loan['loan_number']}) is <strong>overdue by $overdueDays days</strong>.</p>

<table>
<tr><th>Installment No</th><td>{$installment['installment_no']}</td></tr>
<tr><th>Due Date</th><td>{$installment['due_date']}</td></tr>
<tr><th>Principal Amount</th><td>₹ {$installment['principal_amount']}</td></tr>
<tr><th>Interest Amount</th><td>₹ {$installment['interest_amount']}</td></tr>
<tr><th>Total Due</th><td>₹ {$installment['total_amount']}</td></tr>
<tr><th>Outstanding</th><td><strong>₹ $outstanding</strong></td></tr>
<tr><th>Penalty Accrued</th><td>₹ {$installment['accrued_penalty']}</td></tr>
</table>

<p>We request you to make the payment at the earliest to avoid further penal charges and adverse action. If the payment is not received within <strong>7 days</strong>, the Company may be constrained to initiate recovery proceedings as per the Loan Agreement.</p>

<p>For any queries or assistance, please contact us at [Phone] or [Email].</p>

<div class="footer">
<p>Thanking you,<br><br><br>
<strong>For APS Dream Home</strong></p>
<div style="border-top:1px solid #333; width:200px; padding-top:5px;">Authorized Signatory</div>
</div>
</body></html>
HTML;
    }

    protected function buildDefaultNoticeHtml(array $loan, array $overdueInstallments): string
    {
        $date = date('d/m/Y');
        $totalOverdue = array_sum(array_map(fn($i) => (float)$i['total_amount'] - (float)$i['paid_amount'], $overdueInstallments));
        $totalPenalty = array_sum(array_map(fn($i) => (float)$i['accrued_penalty'], $overdueInstallments));

        $rows = '';
        foreach ($overdueInstallments as $inst) {
            $outstanding = (float)$inst['total_amount'] - (float)$inst['paid_amount'];
            $rows .= "<tr><td>{$inst['installment_no']}</td><td>{$inst['due_date']}</td><td>₹ {$inst['total_amount']}</td><td>₹ " . round($outstanding, 2) . "</td><td>₹ {$inst['accrued_penalty']}</td></tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Default Notice - {$loan['loan_number']}</title>
<style>
body { font-family: Arial, sans-serif; font-size: 13px; margin: 40px; line-height: 1.6; }
h1 { font-size: 18px; text-align: center; color: #c00; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
td, th { border: 1px solid #999; padding: 6px; }
th { background: #fee; }
.notice { border: 2px solid #c00; padding: 15px; background: #fff5f5; margin: 20px 0; }
</style></head><body>
<h1>Notice of Default</h1>
<p style="text-align:center;">Date: $date</p>

<p><strong>To,</strong><br>{$loan['customer_name']}<br>{$loan['customer_phone']}</p>

<p><strong>Subject:</strong> NOTICE OF DEFAULT - Loan {$loan['loan_number']}</p>

<div class="notice">
<p><strong>TAKE NOTICE</strong> that you are in <strong>DEFAULT</strong> under the Loan Agreement No. {$loan['loan_number']} dated {$loan['start_date']}.</p>
</div>

<p>The following installment(s) are overdue:</p>

<table>
<tr><th>#</th><th>Due Date</th><th>Amount</th><th>Outstanding</th><th>Penalty</th></tr>
$rows
<tr><th colspan="2">Total</th><th>—</th><th>₹ $totalOverdue</th><th>₹ $totalPenalty</th></tr>
</table>

<p><strong>Total Outstanding: ₹ </strong> " . round($totalOverdue + $totalPenalty, 2) . "</p>

<p>You are hereby called upon to pay the entire outstanding amount of <strong>₹ " . round($totalOverdue + $totalPenalty, 2) . "</strong> within <strong>15 days</strong> from the date of this notice.</p>

<p><strong>FAILURE TO PAY</strong> within the stipulated time will result in:</p>
<ol>
<li>Immediate recall of the entire loan amount</li>
<li>Foreclosure of the loan with repossession of the property</li>
<li>Legal action for recovery including filing of civil/criminal proceedings</li>
<li>Reporting to credit bureaus affecting your credit score</li>
</ol>

<p>This notice is issued without prejudice to any other rights and remedies available to the Company under law or contract.</p>

<p style="margin-top:40px;">
<strong>For APS Dream Home</strong><br><br><br>
<div style="border-top:1px solid #333; width:200px; padding-top:5px;">Authorized Signatory</div>
</p>
</body></html>
HTML;
    }
}
