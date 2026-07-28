<?php
namespace App\Http\Controllers\Admin;

class FarmerAdminController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    public function index()
    {
        $this->requireAdmin();
        try {
            $farmers = $this->db->fetchAll("
                SELECT *, 'farmer' as source, 'किसान' as source_hi FROM farmer_land_management
                ORDER BY id DESC
            ");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
            $farmers = [];
        }
        $totalFarmers = count($farmers ?? []);
        $activeAgreements = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM farmer_agreements WHERE status = 'active'");
        $activeLoans = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM farmer_loans WHERE status IN ('sanctioned','disbursed','active')");
        $this->render('admin/farmers/index', [
            'page_title' => 'Farmers Management',
            'farmers' => $farmers,
            'total_farmers' => $totalFarmers,
            'active_agreements' => $activeAgreements,
            'active_loans' => $activeLoans,
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();
        try {
            $farmer = $this->db->fetch("SELECT * FROM farmer_land_management WHERE id = ?", [$id]);
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        if (!$farmer) {
            $this->setFlash('error', 'Farmer not found');
            $this->redirect('/admin/farmers');
            return;
        }
        $agreements = $this->db->fetchAll("SELECT * FROM farmer_agreements WHERE farmer_id = ? ORDER BY created_at DESC", [$id]);
        $loans = $this->db->fetchAll("SELECT * FROM farmer_loans WHERE farmer_id = ? ORDER BY created_at DESC", [$id]);
        $transactions = $this->db->fetchAll("SELECT * FROM farmer_transactions WHERE farmer_id = ? ORDER BY payment_date DESC", [$id]);
        $documents = $this->db->fetchAll("SELECT * FROM documents WHERE entity_type = 'farmer' AND entity_id = ? ORDER BY uploaded_on DESC", [$id]);
        $this->render('admin/farmers/show', [
            'page_title' => 'Farmer: ' . ($farmer['farmer_name'] ?? 'Unknown'),
            'farmer' => $farmer,
            'agreements' => $agreements,
            'loans' => $loans,
            'transactions' => $transactions,
            'documents' => $documents,
            'is_legacy' => false,
        ]);
    }

    public function agreements()
    {
        $this->requireAdmin();
        try {
            $agreements = $this->db->fetchAll("
                SELECT a.*, k.farmer_name, k.farmer_mobile
                FROM farmer_agreements a
                LEFT JOIN farmer_land_management k ON a.farmer_id = k.id
                ORDER BY a.created_at DESC
            ");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $totalAgreements = count($agreements ?? []);
        $activeCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM farmer_agreements WHERE status = 'active'");
        $completedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM farmer_agreements WHERE status = 'completed'");
        $terminatedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM farmer_agreements WHERE status = 'terminated'");
        $this->render('admin/farmers/agreements', [
            'page_title' => 'Farmer Agreements',
            'agreements' => $agreements,
            'total_agreements' => $totalAgreements,
            'active_count' => $activeCount,
            'completed_count' => $completedCount,
            'terminated_count' => $terminatedCount,
        ]);
    }

    public function showAgreement($id)
    {
        $this->requireAdmin();
        try {
            $agreement = $this->db->fetch("
                SELECT a.*, k.farmer_name, k.farmer_mobile, k.district, k.city
                FROM farmer_agreements a
                LEFT JOIN farmer_land_management k ON a.farmer_id = k.id
                WHERE a.id = ?
            ", [$id]);
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        if (!$agreement) {
            $this->setFlash('error', 'Agreement not found');
            $this->redirect('/admin/farmers/agreements');
            return;
        }
        $this->render('admin/farmers/agreement-show', [
            'page_title' => 'Agreement: ' . ($agreement['agreement_number'] ?? 'N/A'),
            'agreement' => $agreement,
        ]);
    }

    public function storeAgreement()
    {
        $this->requireAdmin();
        $farmerId = (int)($_POST['farmer_id'] ?? 0);
        $agreementNumber = $_POST['agreement_number'] ?? 'AGR-' . time();
        $type = $_POST['agreement_type'] ?? 'land_purchase';
        $startDate = $_POST['start_date'] ?? date('Y-m-d');
        $endDate = $_POST['end_date'] ?? '';
        $terms = $_POST['terms_conditions'] ?? '';
        $totalAmount = (float)($_POST['total_amount'] ?? 0);
        $advanceAmount = (float)($_POST['advance_amount'] ?? 0);
        $commissionRate = (float)($_POST['commission_rate'] ?? 0);
        $remarks = $_POST['remarks'] ?? '';
        $tid = $this->tenantId();
        $this->db->query("INSERT INTO farmer_agreements (farmer_id, agreement_number, agreement_type, start_date, end_date, terms_conditions, total_amount, advance_amount, commission_rate, status, created_by, created_at, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?, NOW(), ?)", [
            $farmerId, $agreementNumber, $type, $startDate, $endDate ?: null,
            $terms, $totalAmount, $advanceAmount, $commissionRate,
            $_SESSION['admin_id'] ?? 0, $tid,
        ]);
        $this->setFlash('success', 'Agreement created successfully');
        $this->redirect('/admin/farmers/agreements');
    }

    public function updateAgreementStatus($id)
    {
        $this->requireAdmin();
        $status = $_POST['status'] ?? '';
        $signedByFarmer = isset($_POST['signed_by_farmer']) ? (int)$_POST['signed_by_farmer'] : 0;
        $signedByCompany = isset($_POST['signed_by_company']) ? (int)$_POST['signed_by_company'] : 0;
        $remarks = $_POST['remarks'] ?? '';
        if (!in_array($status, ['draft','active','completed','terminated','cancelled'])) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('/admin/farmers/agreements/' . $id);
            return;
        }
        $signedDate = null;
        if ($status === 'active' && ($signedByFarmer || $signedByCompany)) {
            $signedDate = date('Y-m-d');
        }
        $this->db->query("UPDATE farmer_agreements SET status=?, signed_by_farmer=?, signed_by_company=?, signed_date=COALESCE(?, signed_date), remarks=? WHERE id=? AND tenant_id=?", [
            $status, $signedByFarmer, $signedByCompany, $signedDate, $remarks, $id, $this->tenantId(),
        ]);
        $this->setFlash('success', 'Agreement status updated');
        $this->redirect('/admin/farmers/agreements/' . $id);
    }

    public function loans()
    {
        $this->requireAdmin();
        try {
            $loans = $this->db->fetchAll("
                SELECT l.*, k.farmer_name, k.farmer_mobile
                FROM farmer_loans l
                LEFT JOIN farmer_land_management k ON l.farmer_id = k.id
                ORDER BY l.created_at DESC
            ");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $totalLoans = count($loans ?? []);
        $sanctionedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM farmer_loans WHERE status = 'sanctioned'");
        $activeCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM farmer_loans WHERE status IN ('disbursed','active')");
        $closedCount = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM farmer_loans WHERE status = 'closed'");
        $this->render('admin/farmers/loans', [
            'page_title' => 'Farmer Loans',
            'loans' => $loans,
            'total_loans' => $totalLoans,
            'sanctioned_count' => $sanctionedCount,
            'active_count' => $activeCount,
            'closed_count' => $closedCount,
        ]);
    }

    public function showLoan($id)
    {
        $this->requireAdmin();
        try {
            $loan = $this->db->fetch("
                SELECT l.*, k.farmer_name, k.farmer_mobile, k.district, k.city
                FROM farmer_loans l
                LEFT JOIN farmer_land_management k ON l.farmer_id = k.id
                WHERE l.id = ?
            ", [$id]);
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        if (!$loan) {
            $this->setFlash('error', 'Loan not found');
            $this->redirect('/admin/farmers/loans');
            return;
        }
        $this->render('admin/farmers/loan-show', [
            'page_title' => 'Loan: ' . ($loan['loan_number'] ?? 'N/A'),
            'loan' => $loan,
        ]);
    }

    public function storeLoan()
    {
        $this->requireAdmin();
        $farmerId = (int)($_POST['farmer_id'] ?? 0);
        $loanNumber = $_POST['loan_number'] ?? 'LN-' . time();
        $loanAmount = (float)($_POST['loan_amount'] ?? 0);
        $interestRate = (float)($_POST['interest_rate'] ?? 0);
        $loanTenure = (int)($_POST['loan_tenure'] ?? 0);
        $emiAmount = (float)($_POST['emi_amount'] ?? 0);
        $purpose = $_POST['purpose'] ?? '';
        $sanctionDate = $_POST['sanction_date'] ?? date('Y-m-d');
        $maturityDate = $_POST['maturity_date'] ?? '';
        $collateralType = $_POST['collateral_type'] ?? '';
        $collateralValue = (float)($_POST['collateral_value'] ?? 0);
        $guarantorName = $_POST['guarantor_name'] ?? '';
        $guarantorPhone = $_POST['guarantor_phone'] ?? '';
        $tid = $this->tenantId();
        $this->db->query("INSERT INTO farmer_loans (farmer_id, loan_number, loan_amount, interest_rate, loan_tenure, emi_amount, purpose, sanction_date, maturity_date, outstanding_amount, status, collateral_type, collateral_value, guarantor_name, guarantor_phone, created_by, created_at, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'sanctioned', ?, ?, ?, ?, ?, NOW(), ?)", [
            $farmerId, $loanNumber, $loanAmount, $interestRate, $loanTenure,
            $emiAmount, $purpose, $sanctionDate, $maturityDate ?: null,
            $loanAmount, $collateralType, $collateralValue, $guarantorName, $guarantorPhone,
            $_SESSION['admin_id'] ?? 0, $tid,
        ]);
        $this->setFlash('success', 'Loan created successfully');
        $this->redirect('/admin/farmers/loans');
    }

    public function updateLoanStatus($id)
    {
        $this->requireAdmin();
        $status = $_POST['status'] ?? '';
        $disbursementDate = $_POST['disbursement_date'] ?? '';
        if (!in_array($status, ['applied','sanctioned','disbursed','active','closed','defaulted'])) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('/admin/farmers/loans/' . $id);
            return;
        }
        $tid = $this->tenantId();
        $this->db->query("UPDATE farmer_loans SET status=?, disbursement_date=COALESCE(?, disbursement_date) WHERE id=? AND tenant_id=?", [
            $status, $disbursementDate ?: null, $id, $tid,
        ]);
        if ($status === 'closed') {
            $this->db->query("UPDATE farmer_loans SET outstanding_amount=0 WHERE id=? AND tenant_id=?", [$id, $tid]);
        }
        $this->setFlash('success', 'Loan status updated');
        $this->redirect('/admin/farmers/loans/' . $id);
    }

    public function gata()
    {
        $this->requireAdmin();
        try {
            $gataRecords = $this->db->fetchAll("
                SELECT g.*, s.name as site_name
                FROM gata_master g
                LEFT JOIN sites s ON g.site_id = s.id
                ORDER BY g.gata_id DESC
            ");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $this->render('admin/farmers/gata', [
            'page_title' => 'Gata Records',
            'gata_records' => $gataRecords,
        ]);
    }

    public function storeGata()
    {
        $this->requireAdmin();
        $siteId = (int)($_POST['site_id'] ?? 0);
        $gataNo = $_POST['gata_no'] ?? '';
        $area = (float)($_POST['area'] ?? 0);
        $availableArea = (float)($_POST['available_area'] ?? $area);
        try {
            $this->db->query("INSERT INTO gata_master (site_id, gata_no, area, available_area, tenant_id) VALUES (?, ?, ?, ?, ?)", [
                $siteId, $gataNo, $area, $availableArea, $this->tenantId(),
            ]);
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $this->setFlash('success', 'Gata record added');
        $this->redirect('/admin/farmers/gata');
    }
}

