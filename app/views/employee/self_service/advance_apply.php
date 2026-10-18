<?php
/**
 * Apply for Salary Advance / Loan
 */

$page_title = $page_title ?? 'Request Salary Advance / Loan';
$page_description = $page_description ?? 'Request a salary advance or loan with flexible repayment options';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i><?= htmlspecialchars($page_title) ?></h4>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?= htmlspecialchars($page_description) ?></p>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="<?= BASE_URL ?>/employee/self-service/advances/apply" id="advanceApplyForm">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <!-- Type Selection -->
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label fw-bold">Type</label>
                            <div class="col-sm-10">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check form-check-inline mb-2">
                                            <input class="form-check-input" type="radio" name="type" id="type_advance" value="advance" checked>
                                            <label class="form-check-label" for="type_advance">
                                                <i class="fas fa-money-bill-wave me-1"></i> Salary Advance (Interest-free)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-check-inline mb-2">
                                            <input class="form-check-input" type="radio" name="type" id="type_loan" value="loan">
                                            <label class="form-check-label" for="type_loan">
                                                <i class="fas fa-university me-1"></i> Salary Loan (With interest)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">Advance: Repaid from next salary, no interest. Loan: Longer repayment with interest.</div>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="row mb-3">
                            <label for="amount" class="col-sm-2 col-form-label fw-bold">Amount <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="amount" name="amount" step="100" min="1000" max="500000" required>
                                </div>
                                <div class="form-text">Minimum ₹1,000, Maximum ₹5,00,000</div>
                            </div>
                        </div>

                        <!-- Installments -->
                        <div class="row mb-3">
                            <label for="installments" class="col-sm-2 col-form-label fw-bold">Repayment Installments <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <select class="form-select" id="installments" name="installments" required>
                                    <option value="1">1 Month (Next Salary)</option>
                                    <option value="2">2 Months</option>
                                    <option value="3">3 Months</option>
                                    <option value="6">6 Months</option>
                                    <option value="12">12 Months</option>
                                </select>
                                <div class="form-text">Advance: Max 3 installments. Loan: Up to 12 installments.</div>
                            </div>
                        </div>

                        <!-- Interest Rate (for loans) -->
                        <div class="row mb-3" id="interestRateRow" style="display: none;">
                            <label for="interest_rate" class="col-sm-2 col-form-label fw-bold">Interest Rate (% p.a.)</label>
                            <div class="col-sm-10">
                                <div class="input-group">
                                    <input type="number" class="form-control" id="interest_rate" name="interest_rate" step="0.1" min="0" max="24" value="12">
                                    <span class="input-group-text">% per annum</span>
                                </div>
                                <div class="form-text">Standard rate: 12% p.a. (Reducing balance)</div>
                            </div>
                        </div>

                        <!-- Reason -->
                        <div class="row mb-3">
                            <label for="reason" class="col-sm-2 col-form-label fw-bold">Reason <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Explain why you need this advance/loan (e.g., medical emergency, home repair, education, wedding, etc.)"></textarea>
                                <div class="form-text">Please provide a clear reason for your request.</div>
                            </div>
                        </div>

                        <!-- Estimated Repayment Preview -->
                        <div class="row mb-3" id="repaymentPreview" style="display: none;">
                            <label class="col-sm-2 col-form-label fw-bold">Estimated Monthly Repayment</label>
                            <div class="col-sm-10">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <div class="fw-bold text-primary" id="previewEMI">₹0</div>
                                                <small class="text-muted">EMI Amount</small>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="fw-bold text-success" id="previewTotal">₹0</div>
                                                <small class="text-muted">Total Repayment</small>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="fw-bold text-warning" id="previewInterest">₹0</div>
                                                <small class="text-muted">Total Interest</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" class="btn btn-primary btn-lg me-2">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Request
                                </button>
                                <a href="<?= BASE_URL ?>/employee/self-service/advances" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const typeAdvance = document.getElementById('type_advance');
    const typeLoan = document.getElementById('type_loan');
    const interestRateRow = document.getElementById('interestRateRow');
    const installmentsSelect = document.getElementById('installments');
    const repaymentPreview = document.getElementById('repaymentPreview');
    const amountInput = document.getElementById('amount');

    function updateUI() {
        const isLoan = typeLoan.checked;
        interestRateRow.style.display = isLoan ? 'block' : 'none';
        repaymentPreview.style.display = isLoan ? 'block' : 'none';
        
        // Update installment options
        const currentValue = installmentsSelect.value;
        installmentsSelect.innerHTML = '';
        if (isLoan) {
            [1, 2, 3, 6, 12].forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = m + ' Month' + (m > 1 ? 's' : '');
                installmentsSelect.appendChild(opt);
            });
        } else {
            [1, 2, 3].forEach(m => {
                const opt = document.createElement('option');
                opt.value = m;
                opt.textContent = m + ' Month' + (m > 1 ? 's' : '');
                installmentsSelect.appendChild(opt);
            });
        }
        // Restore valid value
        if (![...installmentsSelect.options].some(o => o.value === currentValue)) {
            installmentsSelect.value = isLoan ? 12 : 1;
        }
    }

    function calculateEMI() {
        const amount = parseFloat(amountInput.value) || 0;
        const months = parseInt(installmentsSelect.value) || 1;
        const rate = parseFloat(document.getElementById('interest_rate').value) || 0;
        
        if (amount > 0 && months > 0) {
            if (rate > 0 && typeLoan.checked) {
                // EMI formula for reducing balance
                const monthlyRate = rate / 12 / 100;
                const emi = amount * monthlyRate * Math.pow(1 + monthlyRate, months) / (Math.pow(1 + monthlyRate, months) - 1);
                const total = emi * months;
                const interest = total - amount;
                
                document.getElementById('previewEMI').textContent = '₹' + emi.toLocaleString('en-IN', {maximumFractionDigits: 2});
                document.getElementById('previewTotal').textContent = '₹' + total.toLocaleString('en-IN', {maximumFractionDigits: 2});
                document.getElementById('previewInterest').textContent = '₹' + interest.toLocaleString('en-IN', {maximumFractionDigits: 2});
            } else {
                // Simple division for advance
                const emi = amount / months;
                document.getElementById('previewEMI').textContent = '₹' + emi.toLocaleString('en-IN', {maximumFractionDigits: 2});
                document.getElementById('previewTotal').textContent = '₹' + amount.toLocaleString('en-IN', {maximumFractionDigits: 2});
                document.getElementById('previewInterest').textContent = '₹0';
            }
        }
    }

    typeAdvance.addEventListener('change', updateUI);
    typeLoan.addEventListener('change', updateUI);
    amountInput.addEventListener('input', calculateEMI);
    installmentsSelect.addEventListener('change', calculateEMI);
    document.getElementById('interest_rate').addEventListener('input', calculateEMI);

    // Initial
    updateUI();
    calculateEMI();
});
</script>