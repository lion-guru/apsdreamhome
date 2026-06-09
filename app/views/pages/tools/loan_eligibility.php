<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; }$phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= $phoneDisplay ?>'); ?>
<?php if (!defined('BASE_URL')) exit('No direct access'); ?>
<section class="py-4 py-md-5" style="background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);">
    <div class="container">
        <div class="text-center mb-4 mb-md-5">
            <h1 class="text-white fw-bold display-5">Home Loan Eligibility & EMI Calculator</h1>
            <p class="text-white-50 fs-5">Check your loan eligibility, calculate EMI, and plan your dream home purchase</p>
        </div>

        <div class="row g-4">
            <!-- Left: Calculator Form -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-lg h-100" style="border-radius: 16px;">
                    <div class="card-body p-3 p-md-4">
                        <h4 class="card-title mb-4"><i class="fas fa-calculator text-primary me-2"></i>Eligibility Calculator</h4>

                        <!-- Monthly Income Slider -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-flex justify-content-between">
                                <span>Monthly Income</span>
                                <span class="text-primary" id="incomeDisplay">₹50,000</span>
                            </label>
                            <input type="range" class="form-range" id="monthlyIncome" min="10000" max="500000" step="5000" value="50000" oninput="calculateAll()">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">₹10K</small>
                                <small class="text-muted">₹5L</small>
                            </div>
                        </div>

                        <!-- Existing EMI -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Existing Monthly Obligations (₹)</label>
                            <input type="number" class="form-control" id="existingEMI" value="0" min="0" step="1000" oninput="calculateAll()" placeholder="e.g. 5000">
                            <small class="text-muted">Car loan, personal loan, credit card EMI, etc.</small>
                        </div>

                        <!-- Interest Rate -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Interest Rate (%)</label>
                            <select class="form-select" id="interestRate" onchange="calculateAll()">
                                <option value="8.25">8.25% p.a.</option>
                                <option value="8.50" selected>8.50% p.a. (SBI)</option>
                                <option value="8.75">8.75% p.a.</option>
                                <option value="9.00">9.00% p.a.</option>
                                <option value="9.25">9.25% p.a.</option>
                                <option value="9.50">9.50% p.a.</option>
                            </select>
                        </div>

                        <!-- Loan Tenure Slider -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-flex justify-content-between">
                                <span>Loan Tenure</span>
                                <span class="text-primary" id="tenureDisplay">20 Years</span>
                            </label>
                            <input type="range" class="form-range" id="loanTenure" min="5" max="30" step="1" value="20" oninput="calculateAll()">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">5 Yr</small>
                                <small class="text-muted">30 Yr</small>
                            </div>
                        </div>

                        <!-- Property Value -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Property Value (₹) <small class="text-muted fw-normal">(optional)</small></label>
                            <input type="number" class="form-control" id="propertyValue" value="0" min="0" step="100000" oninput="calculateAll()" placeholder="e.g. 5000000">
                            <small class="text-muted">Used for LTV (Loan-to-Value) ratio check</small>
                        </div>

                        <div class="alert alert-info py-2 px-3 mb-0 small" role="alert">
                            <i class="fas fa-info-circle me-1"></i>
                            Banks use <strong>50% FOIR</strong> rule. Max EMI = 50% of monthly income minus existing obligations.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Results -->
            <div class="col-lg-7">
                <!-- Main Results Cards -->
                <div class="row g-3 mb-4" id="eligResults">
                    <div class="col-md-4">
                        <div class="card border-0 shadow h-100" style="border-radius: 12px;background:linear-gradient(135deg,#667eea,#764ba2);">
                            <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                                <small class="text-white-50">Eligible Loan Amount</small>
                                <h3 class="text-white fw-bold mb-0 mt-2 fs-4" id="eligibleLoan">₹21,67,781</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow h-100" style="border-radius: 12px;background:linear-gradient(135deg,#f093fb,#f5576c);">
                            <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                                <small class="text-white-50">Maximum EMI</small>
                                <h3 class="text-white fw-bold mb-0 mt-2 fs-4" id="maxEmiDisplay">₹25,000</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow h-100" style="border-radius: 12px;background:linear-gradient(135deg,#4facfe,#00f2fe);">
                            <div class="card-body text-center p-3 d-flex flex-column justify-content-center">
                                <small class="text-white-50">Max Property @80% LTV</small>
                                <h3 class="text-white fw-bold mb-0 mt-2 fs-4" id="propertyPrice">₹27,09,726</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Affordability Meter -->
                <div class="card border-0 shadow mb-4" style="border-radius: 12px;">
                    <div class="card-body p-3 p-md-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-tachometer-alt me-2"></i>Affordability Meter</h6>
                        <div class="progress" style="height:22px;border-radius:11px;background:#e9ecef;">
                            <div class="progress-bar" id="affordabilityBar" role="progressbar" style="width:0%;border-radius:11px;transition:width 0.8s ease;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-1 small">
                            <span class="text-success fw-medium">Comfortable</span>
                            <span class="text-warning fw-medium">Moderate</span>
                            <span class="text-danger fw-medium">Stretched</span>
                        </div>
                        <p class="mt-2 mb-0 fw-medium" id="affordabilityText"><span class="badge bg-success fs-6 me-1">Comfortable</span> You are well within your repayment capacity</p>
                    </div>
                </div>

                <!-- Chart + EMI Calculator Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow h-100" style="border-radius: 12px;">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie me-2"></i>Total Payment Breakdown</h6>
                                <canvas id="emiPieChart" height="180"></canvas>
                                <div class="text-center mt-2 small" id="pieLabels">
                                    <span class="text-primary me-2"><i class="fas fa-square me-1" style="color:#667eea;"></i>Principal: <strong id="principalAmount">₹0</strong></span>
                                    <span class="text-danger"><i class="fas fa-square me-1" style="color:#f5576c;"></i>Interest: <strong id="interestAmount">₹0</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow h-100" style="border-radius: 12px;">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-3"><i class="fas fa-exchange-alt me-2"></i>EMI Calculator</h6>
                                <div class="mb-2">
                                    <label class="small fw-bold">Loan Amount (₹)</label>
                                    <input type="number" class="form-control form-control-sm" id="emiLoanAmount" oninput="syncEMIFromLoan()" min="0" step="10000">
                                </div>
                                <div class="mb-2">
                                    <label class="small fw-bold">Monthly EMI</label>
                                    <h3 class="text-primary fw-bold mb-0" id="emiResult">₹0</h3>
                                    <small class="text-muted" id="emiTotalPayment">Total Payable: ₹0 | Interest: ₹0</small>
                                </div>
                                <div class="d-grid gap-2 d-md-flex">
                                    <button class="btn btn-outline-primary btn-sm flex-fill" onclick="setLoanToEligible()"><i class="fas fa-magic me-1"></i>Use Eligible Amount</button>
                                    <button class="btn btn-outline-success btn-sm flex-fill" onclick="setLoanToMaxProperty()"><i class="fas fa-building me-1"></i>Use 80% LTV</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Breakdown Table -->
                <div class="card border-0 shadow mb-4" style="border-radius: 12px;">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><i class="fas fa-table me-2"></i>Yearly Payment Schedule</h6>
                            <span class="badge bg-secondary" id="yearsCount">0 Years</span>
                        </div>
                        <div class="table-responsive" style="max-height:320px;overflow-y:auto;">
                            <table class="table table-sm table-hover mb-0" id="amortTable">
                                <thead class="table-dark" style="position:sticky;top:0;">
                                    <tr>
                                        <th>Year</th>
                                        <th class="text-end">Principal Paid</th>
                                        <th class="text-end">Interest Paid</th>
                                        <th class="text-end">Total Paid</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody id="amortBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Required -->
        <div class="card border-0 shadow mt-4" style="border-radius: 16px;">
            <div class="card-body p-3 p-md-4">
                <h4 class="fw-bold mb-4"><i class="fas fa-file-alt text-primary me-2"></i>Documents Required for Home Loan</h4>
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-user me-1"></i> Identity Proof (Any One)</h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Aadhaar Card</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>PAN Card</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Passport</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Voter ID</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Driving License</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-home me-1"></i> Address Proof (Any One)</h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Aadhaar Card</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Utility Bill (Electricity/Water)</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Passport</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Bank Statement with address</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Rental Agreement</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-briefcase me-1"></i> Income Proof (Salaried)</h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Last 3 months Salary Slips</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Last 6 months Bank Statements</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Form 16 (Last 2 Years)</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Employment Offer Letter</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>IT Returns (Last 2 Years)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-briefcase me-1"></i> Income Proof (Self-Employed)</h6>
                        <ul class="list-unstyled ms-3">
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>IT Returns (Last 3 Years)</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>CA Certified Balance Sheet</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Profit & Loss Statement</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Business Registration Proof</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Last 6 months Bank Statements</li>
                        </ul>
                    </div>
                    <div class="col-12">
                        <h6 class="fw-bold text-secondary"><i class="fas fa-file-contract me-1"></i> Property Documents</h6>
                        <ul class="list-unstyled ms-3 mb-0">
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Sale Agreement / Allotment Letter</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Chain of Title Documents</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Approved Building Plan (for construction)</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>NOC from Society / Builder</li>
                            <li><i class="fas fa-check-circle text-success me-2 small"></i>Property Tax Receipts</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Interest Rates -->
        <div class="card border-0 shadow mt-4" style="border-radius: 16px;">
            <div class="card-body p-3 p-md-4">
                <h4 class="fw-bold mb-4"><i class="fas fa-percentage text-primary me-2"></i>Current Home Loan Interest Rates</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Bank</th>
                                <th class="text-end">Interest Rate (p.a.)</th>
                                <th class="text-end">Processing Fee</th>
                                <th>Best For</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td><i class="fas fa-university me-2 text-primary"></i>State Bank of India (SBI)</td><td class="text-end fw-bold">8.50%</td><td class="text-end">0.35%</td><td>First-time buyers, Low rates</td></tr>
                            <tr><td><i class="fas fa-building me-2 text-danger"></i>HDFC Ltd.</td><td class="text-end fw-bold">8.55%</td><td class="text-end">0.50%</td><td>Quick processing, Digital</td></tr>
                            <tr><td><i class="fas fa-building me-2 text-warning"></i>ICICI Bank</td><td class="text-end fw-bold">8.55%</td><td class="text-end">0.50%</td><td>Existing users, Balance transfer</td></tr>
                            <tr><td><i class="fas fa-university me-2 text-success"></i>Punjab National Bank (PNB)</td><td class="text-end fw-bold">8.50%</td><td class="text-end">0.25%</td><td>Low processing fee, Govt users</td></tr>
                            <tr><td><i class="fas fa-building me-2 text-info"></i>Axis Bank</td><td class="text-end fw-bold">8.60%</td><td class="text-end">0.50%</td><td>Salaried professionals, NRIs</td></tr>
                            <tr><td><i class="fas fa-building me-2 text-secondary"></i>Kotak Mahindra Bank</td><td class="text-end fw-bold">8.65%</td><td class="text-end">0.50%</td><td>Self-employed, Flexible tenure</td></tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i>Rates indicative as of May 2026. Actual rates may vary based on CIBIL score, loan amount, and your relationship with the bank.</p>
            </div>
        </div>

        <!-- Tips to Improve Eligibility -->
        <div class="card border-0 shadow mt-4" style="border-radius: 16px;">
            <div class="card-body p-3 p-md-4">
                <h4 class="fw-bold mb-4"><i class="fas fa-lightbulb text-warning me-2"></i>Tips to Improve Your Loan Eligibility</h4>
                <div class="accordion" id="eligibilityTips">
                    <div class="accordion-item border-0 mb-2" style="border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tip1">
                                <i class="fas fa-credit-card me-2 text-success"></i>Improve Your Credit Score
                            </button>
                        </h2>
                        <div id="tip1" class="accordion-collapse collapse" data-bs-parent="#eligibilityTips">
                            <div class="accordion-body pt-0">
                                Maintain a CIBIL score of 750+ for best rates. Pay all credit card bills and loan EMIs on time. Keep credit utilization below 30%. Avoid multiple loan applications in a short period. Check your credit report annually for errors.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-2" style="border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tip2">
                                <i class="fas fa-users me-2 text-primary"></i>Add a Co-Applicant
                            </button>
                        </h2>
                        <div id="tip2" class="accordion-collapse collapse" data-bs-parent="#eligibilityTips">
                            <div class="accordion-body pt-0">
                                Adding your spouse, parent, or sibling as a co-applicant can increase the loan amount by 30-50%. The combined income is considered, and it also helps in tax benefits for both applicants under Section 80C and 24(b).
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-2" style="border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tip3">
                                <i class="fas fa-piggy-bank me-2 text-warning"></i>Increase Down Payment
                            </button>
                        </h2>
                        <div id="tip3" class="accordion-collapse collapse" data-bs-parent="#eligibilityTips">
                            <div class="accordion-body pt-0">
                                A higher down payment (30-40% instead of 20%) reduces the LTV ratio, making banks more comfortable approving your loan. It also lowers your EMI burden and total interest payable over the loan tenure.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-2" style="border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tip4">
                                <i class="fas fa-clock me-2 text-info"></i>Choose a Longer Tenure
                            </button>
                        </h2>
                        <div id="tip4" class="accordion-collapse collapse" data-bs-parent="#eligibilityTips">
                            <div class="accordion-body pt-0">
                                Extending your loan tenure from 20 to 30 years reduces the EMI by 15-20%, allowing you to qualify for a higher loan amount. Use the slider above to see how tenure affects your eligibility. Note: longer tenure means more total interest.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 mb-2" style="border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tip5">
                                <i class="fas fa-times-circle me-2 text-danger"></i>Pay Off Existing Debts
                            </button>
                        </h2>
                        <div id="tip5" class="accordion-collapse collapse" data-bs-parent="#eligibilityTips">
                            <div class="accordion-body pt-0">
                                Clearing existing loans (personal loan, car loan, credit card dues) reduces your FOIR, directly increasing your eligible loan amount. Even paying off one small loan can increase eligibility by 15-25%.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0" style="border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#tip6">
                                <i class="fas fa-briefcase me-2 text-secondary"></i>Show All Income Sources
                            </button>
                        </h2>
                        <div id="tip6" class="accordion-collapse collapse" data-bs-parent="#eligibilityTips">
                            <div class="accordion-body pt-0">
                                Include rental income, freelance earnings, bonuses, commissions, and agricultural income when applying. Banks consider your gross annual income including all sources. Proper documentation of additional income can increase eligibility significantly.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="card border-0 shadow mt-4 text-center" style="border-radius: 16px;background:linear-gradient(135deg,#667eea,#764ba2);">
            <div class="card-body p-4 p-md-5">
                <h3 class="text-white fw-bold mb-2">Ready to Take the Next Step?</h3>
                <p class="text-white-50 mb-4">Our home loan experts can help you find the best rates and guide you through the application process.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="tel:<?= $phoneRaw ?>" class="btn btn-light btn-lg px-4 fw-bold rounded-pill">
                        <i class="fas fa-phone-alt me-2"></i>Call <?= $phoneDisplay ?>
                    </a>
                    <a href="<?= BASE_URL ?>/contact" class="btn btn-outline-light btn-lg px-4 fw-bold rounded-pill">
                        <i class="fas fa-envelope me-2"></i>Send Enquiry
                    </a>
                    <a href="<?= BASE_URL ?>/properties" class="btn btn-outline-light btn-lg px-4 fw-bold rounded-pill">
                        <i class="fas fa-home me-2"></i>Browse Properties
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../partials/related_tools.php'; ?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
    'use strict';

    let pieChart = null;

    // DOM refs
    const el = {
        income: document.getElementById('monthlyIncome'),
        incomeDisplay: document.getElementById('incomeDisplay'),
        existingEMI: document.getElementById('existingEMI'),
        interestRate: document.getElementById('interestRate'),
        tenure: document.getElementById('loanTenure'),
        tenureDisplay: document.getElementById('tenureDisplay'),
        propertyValue: document.getElementById('propertyValue'),
        eligibleLoan: document.getElementById('eligibleLoan'),
        maxEmiDisplay: document.getElementById('maxEmiDisplay'),
        propertyPrice: document.getElementById('propertyPrice'),
        affordabilityBar: document.getElementById('affordabilityBar'),
        affordabilityText: document.getElementById('affordabilityText'),
        emiLoanAmount: document.getElementById('emiLoanAmount'),
        emiResult: document.getElementById('emiResult'),
        emiTotalPayment: document.getElementById('emiTotalPayment'),
        principalAmount: document.getElementById('principalAmount'),
        interestAmount: document.getElementById('interestAmount'),
        amortBody: document.getElementById('amortBody'),
        yearsCount: document.getElementById('yearsCount')
    };

    function formatINR(num) {
        if (isNaN(num) || num < 0) return '₹0';
        return '₹' + Math.round(num).toLocaleString('en-IN');
    }

    function calculateEMI(P, R, N) {
        if (P <= 0 || R <= 0 || N <= 0) return 0;
        const r = R / 12 / 100;
        const n = N * 12;
        if (r === 0) return P / n;
        return P * r * Math.pow(1 + r, n) / (Math.pow(1 + r, n) - 1);
    }

    function calculateAll() {
        const income = parseFloat(el.income.value) || 0;
        const existing = parseFloat(el.existingEMI.value) || 0;
        const rate = parseFloat(el.interestRate.value) || 8.5;
        const years = parseInt(el.tenure.value) || 20;
        const propVal = parseFloat(el.propertyValue.value) || 0;

        // Update displays
        el.incomeDisplay.textContent = formatINR(income);
        el.tenureDisplay.textContent = years + ' Year' + (years > 1 ? 's' : '');

        // FOIR: 50% of income minus existing obligations
        const maxEMI = Math.max(0, income * 0.50 - existing);

        // Calculate eligible loan amount using amortization formula
        let eligibleLoan = 0;
        if (maxEMI > 0 && rate > 0) {
            const r = rate / 12 / 100;
            const n = years * 12;
            eligibleLoan = maxEMI * (Math.pow(1 + r, n) - 1) / (r * Math.pow(1 + r, n));
        }

        // LTV check: max 80% of property value
        let maxByLTV = propVal > 0 ? propVal * 0.80 : Infinity;
        let finalLoan = Math.min(eligibleLoan, maxByLTV);
        if (finalLoan < 0) finalLoan = 0;

        // Update result cards
        if (maxEMI <= 0 || income <= 0) {
            el.eligibleLoan.textContent = '₹0';
            el.maxEmiDisplay.textContent = '₹0';
            el.propertyPrice.textContent = '₹0';
            updateAffordability(0);
            updateEMIPieAndTable(0, rate, years);
            el.emiLoanAmount.value = 0;
            el.emiResult.textContent = '₹0';
            el.emiTotalPayment.textContent = 'Total Payable: ₹0 | Interest: ₹0';
            updateAmortTable(0, rate, years);
            return;
        }

        el.eligibleLoan.textContent = formatINR(finalLoan);
        el.maxEmiDisplay.textContent = formatINR(maxEMI);
        el.propertyPrice.textContent = formatINR(eligibleLoan / 0.80);

        // Sync EMI calculator
        el.emiLoanAmount.value = Math.round(finalLoan);
        updateEMIFromLoan(finalLoan, rate, years);

        // Affordability
        const actualEMI = calculateEMI(finalLoan, rate, years);
        const usageRatio = maxEMI > 0 ? Math.min(1, actualEMI / maxEMI) : 0;
        updateAffordability(usageRatio);

        // Chart & table
        updateEMIPieAndTable(finalLoan, rate, years);
    }

    function updateAffordability(ratio) {
        const bar = el.affordabilityBar;
        const text = el.affordabilityText;
        const pct = Math.min(100, Math.round(ratio * 100));
        bar.style.width = pct + '%';
        bar.setAttribute('aria-valuenow', pct);

        if (ratio <= 0.30) {
            bar.style.background = 'linear-gradient(90deg, #28a745, #20c997)';
            text.innerHTML = '<span class="badge bg-success fs-6 me-1">Comfortable</span> You are well within your repayment capacity';
        } else if (ratio <= 0.60) {
            bar.style.background = 'linear-gradient(90deg, #ffc107, #fd7e14)';
            text.innerHTML = '<span class="badge bg-warning text-dark fs-6 me-1">Moderate</span> You have room but consider a longer tenure or higher down payment';
        } else if (ratio <= 0.80) {
            bar.style.background = 'linear-gradient(90deg, #fd7e14, #dc3545)';
            text.innerHTML = '<span class="badge bg-orange fs-6 me-1" style="background:#fd7e14;">Stretched</span> EMI is high relative to income. Consider increasing down payment';
        } else {
            bar.style.background = 'linear-gradient(90deg, #dc3545, #c82333)';
            text.innerHTML = '<span class="badge bg-danger fs-6 me-1">Limited</span> Your EMI burden is very high. Consider a lower loan amount';
        }
    }

    function updateEMIPieAndTable(loanAmount, rate, years) {
        if (loanAmount <= 0 || rate <= 0 || years <= 0) {
            el.principalAmount.textContent = '₹0';
            el.interestAmount.textContent = '₹0';
            updateAmortTable(0, rate, years);
            destroyPieChart();
            return;
        }

        const emi = calculateEMI(loanAmount, rate, years);
        const totalPayable = emi * years * 12;
        const totalInterest = totalPayable - loanAmount;
        const totalPrincipal = loanAmount;

        el.principalAmount.textContent = formatINR(totalPrincipal);
        el.interestAmount.textContent = formatINR(totalInterest);

        // Pie chart
        const ctx = document.getElementById('emiPieChart').getContext('2d');
        destroyPieChart();
        if (totalPayable > 0) {
            pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Principal', 'Interest'],
                    datasets: [{
                        data: [totalPrincipal, totalInterest],
                        backgroundColor: ['#667eea', '#f5576c'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const val = ctx.raw;
                                    return ctx.label + ': ' + Math.round(val).toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
        }

        updateAmortTable(loanAmount, rate, years);
    }

    function updateAmortTable(loanAmount, rate, years) {
        const tbody = el.amortBody;
        tbody.innerHTML = '';
        if (loanAmount <= 0 || rate <= 0 || years <= 0) {
            el.yearsCount.textContent = '0 Years';
            return;
        }

        const emi = calculateEMI(loanAmount, rate, years);
        const r = rate / 12 / 100;
        let balance = loanAmount;
        let yearPrincipal = 0;
        let yearInterest = 0;
        let totalMonths = years * 12;
        let rows = [];

        for (let m = 1; m <= totalMonths; m++) {
            const interestP = balance * r;
            const principalP = emi - interestP;
            balance = Math.max(0, balance - principalP);
            yearPrincipal += principalP;
            yearInterest += interestP;

            if (m % 12 === 0 || m === totalMonths) {
                rows.push({
                    year: Math.ceil(m / 12),
                    principal: yearPrincipal,
                    interest: yearInterest,
                    total: yearPrincipal + yearInterest,
                    balance: balance
                });
                yearPrincipal = 0;
                yearInterest = 0;
            }
        }

        el.yearsCount.textContent = rows.length + ' Year' + (rows.length > 1 ? 's' : '');

        rows.forEach(function(row) {
            var tr = document.createElement('tr');
            if (row.year === rows.length) {
                tr.classList.add('table-active', 'fw-bold');
            }
            tr.innerHTML = '<td>' + row.year + '</td>' +
                '<td class="text-end text-success">' + formatINR(row.principal) + '</td>' +
                '<td class="text-end text-danger">' + formatINR(row.interest) + '</td>' +
                '<td class="text-end text-primary">' + formatINR(row.total) + '</td>' +
                '<td class="text-end text-muted">' + formatINR(row.balance) + '</td>';
            tbody.appendChild(tr);
        });
    }

    function updateEMIFromLoan(amount, rate, years) {
        if (amount <= 0) {
            el.emiResult.textContent = '₹0';
            el.emiTotalPayment.textContent = 'Total Payable: ₹0 | Interest: ₹0';
            return;
        }
        const emi = calculateEMI(amount, rate, years);
        const totalPayable = emi * years * 12;
        const totalInterest = totalPayable - amount;
        el.emiResult.textContent = formatINR(emi);
        el.emiTotalPayment.textContent = 'Total Payable: ' + formatINR(totalPayable) + ' | Interest: ' + formatINR(totalInterest);
    }

    function syncEMIFromLoan() {
        const loanAmt = parseFloat(el.emiLoanAmount.value) || 0;
        const rate = parseFloat(el.interestRate.value) || 8.5;
        const years = parseInt(el.tenure.value) || 20;
        updateEMIFromLoan(loanAmt, rate, years);
        updateEMIPieAndTable(loanAmt, rate, years);
        // Update affordability based on manual loan amount
        const income = parseFloat(el.income.value) || 0;
        const existing = parseFloat(el.existingEMI.value) || 0;
        const maxEMI = Math.max(0, income * 0.50 - existing);
        const actualEMI = calculateEMI(loanAmt, rate, years);
        const ratio = maxEMI > 0 ? Math.min(1, actualEMI / maxEMI) : 1;
        updateAffordability(ratio);
    }

    function setLoanToEligible() {
        const val = el.eligibleLoan.textContent.replace(/[₹,\s]/g, '');
        const num = parseFloat(val) || 0;
        el.emiLoanAmount.value = Math.round(num);
        syncEMIFromLoan();
    }

    function setLoanToMaxProperty() {
        const val = el.propertyPrice.textContent.replace(/[₹,\s]/g, '');
        const num = parseFloat(val) || 0;
        el.emiLoanAmount.value = Math.round(num);
        syncEMIFromLoan();
    }

    function destroyPieChart() {
        if (pieChart) {
            pieChart.destroy();
            pieChart = null;
        }
    }

    // Expose to global scope for inline onclick
    window.calculateAll = calculateAll;
    window.syncEMIFromLoan = syncEMIFromLoan;
    window.setLoanToEligible = setLoanToEligible;
    window.setLoanToMaxProperty = setLoanToMaxProperty;

    // Initial calculation
    calculateAll();

    // Also recalculate on propertyValue and existingEMI blur (in case of partial input)
    el.existingEMI.addEventListener('blur', calculateAll);
    el.propertyValue.addEventListener('blur', calculateAll);

})();
</script>

<style>
#amortTable tbody tr:hover { background: rgba(102,126,234,0.08); }
#amortTable tbody tr:last-child { font-weight: 600; }
.form-range::-webkit-slider-thumb { background: #667eea; }
.form-range::-moz-range-thumb { background: #667eea; }
.form-range:focus::-webkit-slider-thumb { box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
.form-range:focus::-moz-range-thumb { box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
.accordion-button:not(.collapsed) { background: linear-gradient(135deg,#667eea15,#764ba215); color: #302b63; box-shadow: none; }
.accordion-button:focus { box-shadow: none; border-color: rgba(102,126,234,0.3); }
.table-dark th { position: sticky; top: 0; z-index: 1; }
@media (max-width: 767px) {
    #eligResults .col-md-4 { margin-bottom: 0.5rem; }
    .display-5 { font-size: 1.75rem; }
}
.badge.bg-orange { background: #fd7e14; }
</style>
