<?php
/**
 * Investment Declaration
*/

$page_title = $page_title ?? 'Investment Declaration';
$declarations = $declarations ?? [];
$financial_year = $financial_year ?? date('Y');
$fy_label = $fy_label ?? ($financial_year . '-' . ($financial_year + 1));
$section_limits = $section_limits ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-9">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Investment Declaration - FY <?= htmlspecialchars($fy_label) ?></h4>
                    <span class="badge bg-light text-dark">Section 80C Limit: ₹<?= number_format($section_limits['80C'] ?? 150000) ?></span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Declare Your Tax-Saving Investments</h6>
                        <p class="mb-0">Enter your planned investments for FY <?= htmlspecialchars($fy_label) ?>. These declarations are used for TDS calculation. You must upload proofs by <strong>January 31st</strong> of the financial year.</p>
                    </div>

                    <form method="POST" id="declarationForm" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="financial_year" value="<?= $financial_year ?>">
                        
                        <?php
                        $sections = [
                            '80C' => ['label' => 'Section 80C (Max ₹1,50,000)', 'icon' => 'fas fa-piggy-bank', 'color' => 'primary', 'items' => [
                                ['code' => 'PPF', 'name' => 'Public Provident Fund (PPF)'],
                                ['code' => 'ELSS', 'name' => 'Equity Linked Savings Scheme (ELSS)'],
                                ['code' => 'LIFE_INS', 'name' => 'Life Insurance Premium'],
                                ['code' => 'NSC', 'name' => 'National Savings Certificate (NSC)'],
                                ['code' => 'SSY', 'name' => 'Sukanya Samriddhi Yojana (SSY)'],
                                ['code' => 'EPF', 'name' => 'Employee Provident Fund (EPF) - Employee Share'],
                                ['code' => 'VPF', 'name' => 'Voluntary Provident Fund (VPF)'],
                                ['code' => 'FD_5YR', 'name' => '5-Year Tax Saver Fixed Deposit'],
                                ['code' => 'HOME_LOAN_PRINCIPAL', 'name' => 'Home Loan Principal Repayment'],
                                ['code' => 'STAMP_DUTY', 'name' => 'Stamp Duty & Registration Charges'],
                                ['code' => 'OTHER_80C', 'name' => 'Other 80C Investments'],
                            ]],
                            '80CCD1B' => ['label' => 'Section 80CCD(1B) - NPS Additional (Max ₹50,000)', 'icon' => 'fas fa-user-shield', 'color' => 'info', 'items' => [
                                ['code' => 'NPS_TIER1', 'name' => 'NPS Tier 1 Additional Contribution'],
                            ]],
                            '80D' => ['label' => 'Section 80D - Health Insurance (Max ₹25,000 / ₹50,000)', 'icon' => 'fas fa-heartbeat', 'color' => 'danger', 'items' => [
                                ['code' => 'HEALTH_SELF', 'name' => 'Self & Family (Max ₹25,000)'],
                                ['code' => 'HEALTH_PARENTS', 'name' => 'Parents (Max ₹25,000 / ₹50,000 if senior)'],
                                ['code' => 'PREVENTIVE', 'name' => 'Preventive Health Checkup (Max ₹5,000)'],
                            ]],
                            'HRA' => ['label' => 'House Rent Allowance (HRA) Exemption', 'icon' => 'fas fa-home', 'color' => 'warning', 'items' => [
                                ['code' => 'RENT_PAID', 'name' => 'Rent Paid per Month'],
                                ['code' => 'HRA_RECEIVED', 'name' => 'HRA Received per Month'],
                                ['code' => 'CITY_TYPE', 'name' => 'Metro (50%) / Non-Metro (40%)'],
                            ]],
                            '24B' => ['label' => 'Section 24(b) - Home Loan Interest (Max ₹2,00,000)', 'icon' => 'fas fa-university', 'color' => 'secondary', 'items' => [
                                ['code' => 'HOME_LOAN_INTEREST', 'name' => 'Home Loan Interest Paid'],
                            ]],
                            '80E' => ['label' => 'Section 80E - Education Loan Interest (No Limit)', 'icon' => 'fas fa-graduation-cap', 'color' => 'purple', 'items' => [
                                ['code' => 'EDU_LOAN_INTEREST', 'name' => 'Education Loan Interest Paid'],
                            ]],
                            '80G' => ['label' => 'Section 80G - Donations', 'icon' => 'fas fa-hand-holding-heart', 'color' => 'pink', 'items' => [
                                ['code' => 'DONATION_100', 'name' => '100% Deduction (PM Relief Fund, etc.)'],
                                ['code' => 'DONATION_50', 'name' => '50% Deduction (Charitable Institutions)'],
                            ]],
                            '80TTA' => ['label' => 'Section 80TTA - Savings Account Interest (Max ₹10,000)', 'icon' => 'fas fa-university', 'color' => 'teal', 'items' => [
                                ['code' => 'SAVINGS_INTEREST', 'name' => 'Savings Account Interest'],
                            ]],
                        ];

                        $declared = [];
                        foreach ($declarations as $section => $items) {
                            foreach ($items as $item) {
                                $declared[$section][$item['subsection']] = $item;
                            }
                        }
                        ?>

                        <?php foreach ($sections as $sectionKey => $section): ?>
                        <div class="card mb-3 section-card" data-section="<?= $sectionKey ?>">
                            <div class="card-header bg-<?= $section['color'] ?> text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="<?= $section['icon'] ?> me-2"></i>
                                        <?= $section['label'] ?>
                                    </h5>
                                    <span class="badge bg-light text-dark">Limit: ₹<?= number_format($section_limits[$sectionKey] ?? 0) ?></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php foreach ($section['items'] as $item): ?>
                                <?php 
                                $existing = $declared[$sectionKey][$item['code']] ?? null;
                                $existingAmount = $existing ? (float)$existing['amount'] : 0;
                                $existingDesc = $existing ? $existing['description'] : '';
                                ?>
                                <div class="row mb-3 investment-row align-items-end" data-code="<?= $item['code'] ?>">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium"><?= htmlspecialchars($item['name']) ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" min="0" 
                                                name="declarations[<?= $sectionKey ?>][<?= $item['code'] ?>][amount]" 
                                                class="form-control amount-input" 
                                                value="<?= number_format($existingAmount, 2, '.', '') ?>"
                                                placeholder="0.00">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Description / Details (Optional)</label>
                                        <input type="text" 
                                            name="declarations[<?= $sectionKey ?>][<?= $item['code'] ?>][description]" 
                                            class="form-control" 
                                            value="<?= htmlspecialchars($existingDesc) ?>"
                                            placeholder="Policy No., Bank Name, Property Address, etc.">
                                    </div>
                                    <input type="hidden" name="declarations[<?= $sectionKey ?>][<?= $item['code'] ?>][section]" value="<?= $sectionKey ?>">
                                    <input type="hidden" name="declarations[<?= $sectionKey ?>][<?= $item['code'] ?>][subsection]" value="<?= $item['code'] ?>">
                                    <input type="hidden" name="declarations[<?= $sectionKey ?>][<?= $item['code'] ?>][investment_type]" value="<?= htmlspecialchars($item['name']) ?>">
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-calculator me-1"></i>
                                                Section Total: <span class="section-total fw-bold text-<?= $section['color'] ?>">₹0.00</span>
                                            </small>
                                            <button type="button" class="btn btn-sm btn-outline-<?= $section['color'] ?>" 
                                                onclick="clearSection('<?= $sectionKey ?>')">
                                                <i class="fas fa-eraser me-1"></i>Clear Section
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- Grand Total -->
                        <div class="card bg-light mb-4">
                            <div class="card-body text-center py-4">
                                <h4 class="mb-1">Grand Total Declared</h4>
                                <h1 class="display-4 text-primary" id="grandTotal">₹0.00</h1>
                                <p class="text-muted mb-0">For TDS Calculation</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?= BASE_URL ?>/employee/self-service" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="saveDraft()">
                                    <i class="fas fa-save me-1"></i> Save as Draft
                                </button>
                                <button type="submit" class="btn btn-success btn-lg px-5" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i> Submit Declaration
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">
                    <h5><i class="fas fa-upload me-2"></i>Upload Investment Proof</h5>
                    <p class="text-muted small">JPG, PNG, WebP or PDF, max 5MB. Uploaded proofs are stored for verification.</p>
                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/investment-declaration/upload-proof" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="financial_year" value="<?= $financial_year ?>">
                        <div class="col-md-4">
                            <label class="form-label">Section</label>
                            <select name="section" class="form-select" required>
                                <?php foreach (array_keys($section_limits) as $sec): ?>
                                <option value="<?= htmlspecialchars($sec) ?>"><?= htmlspecialchars($sec) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Proof File</label>
                            <input type="file" name="proof_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-upload me-1"></i>Upload Proof</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.section-card { border-left: 4px solid; }
.section-card[data-section="80C"] { border-color: #0d6efd; }
.section-card[data-section="80CCD1B"] { border-color: #0dcaf0; }
.section-card[data-section="80D"] { border-color: #dc3545; }
.section-card[data-section="HRA"] { border-color: #ffc107; }
.section-card[data-section="24B"] { border-color: #6c757d; }
.section-card[data-section="80E"] { border-color: #6f42c1; }
.section-card[data-section="80G"] { border-color: #e83e8c; }
.section-card[data-section="80TTA"] { border-color: #20c997; }

.investment-row .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.15); }
.amount-input { text-align: right; }
</style>

<script>
const sectionLimits = <?= json_encode($section_limits) ?>;
const declared = <?= json_encode($declared) ?>;

function calculateTotals() {
    let grandTotal = 0;
    
    document.querySelectorAll('.section-card').forEach(card => {
        const section = card.dataset.section;
        let sectionTotal = 0;
        
        card.querySelectorAll('.amount-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            sectionTotal += val;
        });
        
        const totalEl = card.querySelector('.section-total');
        totalEl.textContent = '₹' + sectionTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        // Check limit
        const limit = sectionLimits[section] || 0;
        if (limit > 0 && sectionTotal > limit) {
            totalEl.classList.add('text-danger');
            totalEl.title = `Exceeds limit of ₹${limit.toLocaleString('en-IN')}`;
        } else {
            totalEl.classList.remove('text-danger');
        }
        
        grandTotal += sectionTotal;
    });
    
    document.getElementById('grandTotal').textContent = '₹' + grandTotal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function clearSection(section) {
    if (!confirm('Clear all entries in this section?')) return;
    const card = document.querySelector(`.section-card[data-section="${section}"]`);
    card.querySelectorAll('.amount-input').forEach(input => input.value = '');
    card.querySelectorAll('input[name$="[description]"]').forEach(input => input.value = '');
    calculateTotals();
}

function saveDraft() {
    const form = document.getElementById('declarationForm');
    const formData = new FormData(form);
    formData.append('draft', '1');
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    }).then(() => {
        showToast('Draft saved successfully!', 'success');
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>`;
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

// Live calculation
document.querySelectorAll('.amount-input').forEach(input => {
    input.addEventListener('input', calculateTotals);
    input.addEventListener('blur', calculateTotals);
});

// Initial calculation
calculateTotals();
</script>