<section class="py-5" style="background: linear-gradient(135deg, #0f172a, #1e3a5f, #1e293b);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold"><i class="fas fa-calculator me-2"></i><?php echo __('tool_loan_eligibility_title', [], 'Home Loan Eligibility Calculator'); ?></h1>
            <p class="text-white-50"><?php echo __('tool_loan_eligibility_subtitle', [], 'Aap kitna home loan le sakte hain jaanein'); ?></p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('monthly_income_rs', [], 'Monthly Income (₹)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="income" value="80000" oninput="calcLoan()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold"><?php echo __('monthly_emi_rs', [], 'Monthly Existing EMIs (₹)'); ?></label>
                                <input type="number" class="form-control form-control-lg" id="existEmi" value="0" oninput="calcLoan()">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('interest_rate', [], 'Interest Rate (%)'); ?></label>
                                <input type="number" class="form-control" id="intRate" value="8.5" step="0.1" oninput="calcLoan()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('tenure_years', [], 'Tenure (Years)'); ?></label>
                                <input type="number" class="form-control" id="tenure" value="20" min="1" max="30" oninput="calcLoan()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold"><?php echo __('foir_limit', [], 'FOIR Limit (%)'); ?></label>
                                <select class="form-select" id="foir" onchange="calcLoan()">
                                    <option value="50" selected>50%</option>
                                    <option value="60">60%</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 text-center">
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('max_loan', [], 'Max Loan Amount'); ?></small>
                                    <h4 class="text-primary mb-0" id="maxLoan">₹53,00,000</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-light rounded-3 p-3">
                                    <small class="text-muted d-block"><?php echo __('max_monthly_emi', [], 'Max Monthly EMI'); ?></small>
                                    <h4 class="text-danger mb-0" id="maxEmi">₹40,000</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-success text-white rounded-3 p-3">
                                    <small class="d-block"><?php echo __('total_interest', [], 'Total Interest Payable'); ?></small>
                                    <h5 class="mb-0" id="totalInt">₹45,20,000</h5>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 mt-3">
                            <div class="card-body p-3">
                                <h6 class="fw-bold"><i class="fas fa-info-circle me-1"></i><?php echo __('emi_breakdown', [], 'EMI Breakdown'); ?></h6>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <small class="text-muted"><?php echo __('net_monthly_income', [], 'Net Monthly Income:'); ?></small>
                                        <span class="fw-bold" id="netIncome">₹80,000</span><br>
                                        <small class="text-muted"><?php echo __('available_for_emi', [], 'Available for EMI:'); ?></small>
                                        <span class="fw-bold text-success" id="availEmi">₹40,000</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted"><?php echo __('total_payment', [], 'Total Payment:'); ?></small>
                                        <span class="fw-bold" id="totalPay">₹98,20,000</span><br>
                                        <small class="text-muted"><?php echo __('loan_to_value', [], 'Loan to Value:'); ?></small>
                                        <span class="fw-bold" id="ltv">80%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i><?php echo __('loan_eligibility_disclaimer', [], 'Banks typically allow FOIR up to 50-60% of monthly income. This calculator provides an estimate; actual eligibility depends on credit score, employer, age, and bank-specific policies. Consult your bank for exact eligibility.'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcLoan() {
    const income = parseFloat(document.getElementById('income').value) || 0;
    const existEmi = parseFloat(document.getElementById('existEmi').value) || 0;
    const rate = (parseFloat(document.getElementById('intRate').value) || 8.5) / 100;
    const tenure = parseInt(document.getElementById('tenure').value) || 20;
    const foir = (parseFloat(document.getElementById('foir').value) || 50) / 100;
    const availEmi = income * foir - existEmi;
    const r = rate / 12;
    const n = tenure * 12;
    let maxLoan = 0;
    if (r > 0 && availEmi > 0) {
        maxLoan = availEmi * ((1 - Math.pow(1 + r, -n)) / r);
    } else if (availEmi > 0) {
        maxLoan = availEmi * n;
    }
    const totalPay = availEmi * n;
    const totalInt = totalPay - maxLoan;
    document.getElementById('maxLoan').textContent = '\u20B9' + Math.round(maxLoan).toLocaleString('en-IN');
    document.getElementById('maxEmi').textContent = '\u20B9' + Math.round(Math.max(0, availEmi)).toLocaleString('en-IN');
    document.getElementById('totalInt').textContent = '\u20B9' + Math.round(totalInt).toLocaleString('en-IN');
    document.getElementById('netIncome').textContent = '\u20B9' + income.toLocaleString('en-IN');
    document.getElementById('availEmi').textContent = '\u20B9' + Math.round(Math.max(0, availEmi)).toLocaleString('en-IN');
    document.getElementById('totalPay').textContent = '\u20B9' + Math.round(totalPay).toLocaleString('en-IN');
}
calcLoan();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
