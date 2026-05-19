<section class="py-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="text-white fw-bold">Home Loan Eligibility Calculator</h1>
            <p class="text-white-50">Aapki salary ke hisaab se kitna loan milega? Check karein instantly!</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Monthly Income (₹)</label>
                            <input type="number" class="form-control form-control-lg" id="monthlyIncome" value="50000" oninput="calcEligibility()">
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Existing EMI (₹)</label>
                                <input type="number" class="form-control" id="existingEmi" value="0" oninput="calcEligibility()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Interest Rate (%)</label>
                                <input type="number" class="form-control" id="eligRate" value="8.5" step="0.1" oninput="calcEligibility()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tenure (Years)</label>
                                <select class="form-select" id="eligTenure" onchange="calcEligibility()">
                                    <option value="5">5 Years</option>
                                    <option value="10">10 Years</option>
                                    <option value="15">15 Years</option>
                                    <option value="20" selected>20 Years</option>
                                    <option value="25">25 Years</option>
                                    <option value="30">30 Years</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 text-center" id="eligResults">
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted">Eligible Loan</small>
                                    <h5 class="text-primary mb-0" id="eligibleLoan">₹21,67,781</h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted">Max EMI</small>
                                    <h5 class="text-success mb-0" id="maxEmiAmt">₹18,750</h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light rounded p-3">
                                    <small class="text-muted">Property Price @80% LTV</small>
                                    <h5 class="text-danger mb-0" id="propertyPrice">₹27,09,726</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
function calcEligibility() {
    const income = parseFloat(document.getElementById('monthlyIncome').value) || 0;
    const existing = parseFloat(document.getElementById('existingEmi').value) || 0;
    const rate = parseFloat(document.getElementById('eligRate').value) || 8.5;
    const years = parseFloat(document.getElementById('eligTenure').value) || 20;
    const maxEmi = income * 0.50 - existing;
    if (maxEmi <= 0) {
        document.getElementById('eligibleLoan').textContent = '₹0';
        document.getElementById('maxEmiAmt').textContent = '₹0';
        document.getElementById('propertyPrice').textContent = '₹0';
        return;
    }
    const R = rate / 12 / 100;
    const N = years * 12;
    const loan = maxEmi * (Math.pow(1 + R, N) - 1) / (R * Math.pow(1 + R, N));
    const propPrice = loan / 0.80;
    document.getElementById('eligibleLoan').textContent = '₹' + Math.round(loan).toLocaleString('en-IN');
    document.getElementById('maxEmiAmt').textContent = '₹' + Math.round(maxEmi).toLocaleString('en-IN');
    document.getElementById('propertyPrice').textContent = '₹' + Math.round(propPrice).toLocaleString('en-IN');
}
calcEligibility();
</script>
<?php include __DIR__ . '/../partials/related_tools.php'; ?>
