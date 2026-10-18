<?php
/**
 * Interactive Tax Calculator
 * Compare Old vs New Regime with actual salary numbers
*/

$page_title = $page_title ?? 'Tax Calculator';
$financial_year = $financial_year ?? date('Y');
$fy_label = $fy_label ?? ($financial_year . '-' . ($financial_year + 1));

// Get employee's current salary for pre-filling
$employeeId = $_SESSION['employee_id'] ?? 0;
$currentSalary = [];
if ($employeeId) {
    try {
        $db = \App\Core\Database\Database::getInstance();
        // S343: `$tidSql` / `$tidParams` were never defined in this view, so
        // array_merge() received null and threw
        // `TypeError: array_merge(): Argument #2 must be of type array, null given`,
        // which is an Error (not an Exception) so the catch below never fired
        // and the whole page 500'd. Plain lookup matches the controller.
        $emp = $db->fetchOne("SELECT id FROM employees WHERE user_id=?", [$employeeId]);
        if ($emp) {
            $salary = $db->fetchOne("SELECT * FROM salary_structures WHERE employee_id=? AND status='active' ORDER BY effective_date DESC LIMIT 1", [$emp['id']]);
            if ($salary) {
                $currentSalary = [
                    'basic' => $salary['basic_salary'] ?? 0,
                    'hra' => $salary['hra'] ?? 0,
                    'conveyance' => $salary['conveyance'] ?? 0,
                    'medical' => $salary['medical_allowance'] ?? 0,
                    'special' => $salary['special_allowance'] ?? 0,
                    'other' => $salary['other_allowances'] ?? 0,
                ];
            }
        }
    } catch (\Exception $e) {}
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.tax-calc-card { border: none; border-radius: 16px; transition: transform 0.2s; }
.tax-calc-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.tax-slab-row { transition: background 0.15s; }
.tax-slab-row:hover { background: #fef3e2; }
.result-card { border: none; border-radius: 14px; }
.result-card .card-body { padding: 1.5rem; }
.tax-result { font-size: 1.5rem; font-weight: 700; }
.tax-savings { font-size: 1.25rem; }
.slab-active { background: #fef3e2; }
.comparison-bar { height: 8px; border-radius: 4px; background: #e2e8f0; overflow: hidden; }
.comparison-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-calculator me-2 text-primary"></i>Interactive Tax Calculator</h4>
                    <p class="text-muted mb-0">Compare Old vs New Regime with your actual salary — FY <?= htmlspecialchars($fy_label) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>/employee/self-service/tax-regime" class="btn btn-outline-primary">
                        <i class="fas fa-cog me-1"></i> Tax Regime Selection
                    </a>
                </div>
            </div>

            <!-- Input Form -->
            <div class="card tax-calc-card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Enter Your Salary Details</h5>
                </div>
                <div class="card-body">
                    <form id="taxCalcForm" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="financial_year" value="<?= $financial_year ?>">
                        
                        <h6 class="mb-3 text-primary"><i class="fas fa-money-bill-wave me-2"></i>Salary Components (Monthly)</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Basic Salary <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" name="basic" class="form-control" 
                                    value="<?= $currentSalary['basic'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">HRA Received</label>
                                <input type="number" step="0.01" min="0" name="hra" class="form-control" 
                                    value="<?= $currentSalary['hra'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Conveyance Allowance</label>
                                <input type="number" step="0.01" min="0" name="conveyance" class="form-control" 
                                    value="<?= $currentSalary['conveyance'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Medical Allowance</label>
                                <input type="number" step="0.01" min="0" name="medical" class="form-control" 
                                    value="<?= $currentSalary['medical'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Special Allowance</label>
                                <input type="number" step="0.01" min="0" name="special" class="form-control" 
                                    value="<?= $currentSalary['special'] ?? '' ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Other Allowances</label>
                                <input type="number" step="0.01" min="0" name="other" class="form-control" 
                                    value="<?= $currentSalary['other'] ?? '' ?>">
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3 text-primary"><i class="fas fa-piggy-bank me-2"></i>Deductions & Exemptions (Annual)</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">80C Investments</label>
                                <input type="number" step="0.01" min="0" max="150000" name="ded_80c" class="form-control" value="0">
                                <div class="form-text">Max ₹1,50,000</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">80CCD(1B) - NPS</label>
                                <input type="number" step="0.01" min="0" max="50000" name="ded_80ccd1b" class="form-control" value="0">
                                <div class="form-text">Max ₹50,000</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">80D Health Insurance</label>
                                <input type="number" step="0.01" min="0" max="100000" name="ded_80d" class="form-control" value="0">
                                <div class="form-text">Max ₹1,00,000</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">HRA Exemption</label>
                                <input type="number" step="0.01" min="0" name="ded_hra" class="form-control" value="0">
                                <div class="form-text">Based on rent paid</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Home Loan Interest (24b)</label>
                                <input type="number" step="0.01" min="0" max="200000" name="ded_24b" class="form-control" value="0">
                                <div class="form-text">Max ₹2,00,000</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">80E Education Loan</label>
                                <input type="number" step="0.01" min="0" name="ded_80e" class="form-control" value="0">
                                <div class="form-text">No limit</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Standard Deduction</label>
                                <input type="number" step="0.01" min="0" max="50000" name="ded_std" class="form-control" value="50000" readonly>
                                <div class="form-text">Fixed ₹50,000</div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Professional Tax</label>
                                <input type="number" step="0.01" min="0" max="2500" name="ded_pt" class="form-control" value="2400">
                                <div class="form-text">Max ₹2,500</div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3 text-primary"><i class="fas fa-home me-2"></i>HRA Calculation Details (if claiming HRA)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Rent Paid (Monthly)</label>
                                <input type="number" step="0.01" min="0" name="rent_paid" class="form-control" value="0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">City Type</label>
                                <select name="city_type" class="form-select">
                                    <option value="metro">Metro (50% of Basic)</option>
                                    <option value="non_metro">Non-Metro (40% of Basic)</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-calculator me-2"></i> Calculate Tax
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Section (hidden initially) -->
            <div id="resultsSection" class="d-none">
                <div class="row g-4 mb-4">
                    <!-- Old Regime Result -->
                    <div class="col-lg-6">
                        <div class="card result-card border-warning h-100">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Old Regime</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <div class="tax-result text-warning" id="oldTotalTax">₹0</div>
                                    <small class="text-muted">Total Tax (Annual)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Effective Tax Rate</label>
                                    <div class="comparison-bar">
                                        <div class="comparison-fill bg-warning" id="oldRateBar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="oldRateText">0%</small>
                                </div>
                                
                                <h6 class="mb-3"><i class="fas fa-list me-2"></i>Tax Breakdown</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover" id="oldBreakdownTable">
                                        <thead class="table-light"><tr><th>Slab</th><th class="text-end">Tax</th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                
                                <h6 class="mt-3 mb-2"><i class="fas fa-minus-circle me-2"></i>Deductions Claimed</h6>
                                <div id="oldDeductionsList"></div>
                            </div>
                        </div>
                    </div>

                    <!-- New Regime Result -->
                    <div class="col-lg-6">
                        <div class="card result-card border-primary h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>New Regime (Default)</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <div class="tax-result text-primary" id="newTotalTax">₹0</div>
                                    <small class="text-muted">Total Tax (Annual)</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">Effective Tax Rate</label>
                                    <div class="comparison-bar">
                                        <div class="comparison-fill bg-primary" id="newRateBar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted" id="newRateText">0%</small>
                                </div>
                                
                                <h6 class="mb-3"><i class="fas fa-list me-2"></i>Tax Breakdown</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover" id="newBreakdownTable">
                                        <thead class="table-light"><tr><th>Slab</th><th class="text-end">Tax</th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                
                                <h6 class="mt-3 mb-2"><i class="fas fa-minus-circle me-2"></i>Standard Deduction Only</h6>
                                <div id="newDeductionsList"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Savings Summary -->
                <div class="card tax-calc-card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Savings Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="tax-savings text-success" id="savingsAmount">₹0</div>
                                <small class="text-muted">Annual Savings</small>
                            </div>
                            <div class="col-md-4">
                                <div class="tax-savings text-info" id="savingsPercent">0%</div>
                                <small class="text-muted">% of Gross</small>
                            </div>
                            <div class="col-md-4">
                                <div class="tax-savings text-warning" id="betterRegime">-</div>
                                <small class="text-muted">Better Regime</small>
                            </div>
                        </div>
                        <div class="comparison-bar mt-3" id="savingsBar">
                            <div class="comparison-fill bg-success" id="savingsFill" style="width: 0%"></div>
                        </div>
                        <div class="mt-2 text-center">
                            <small class="text-muted" id="recommendationText">Enter your salary and click Calculate to see recommendation</small>
                        </div>
                    </div>
                </div>

                <!-- Tax Slabs Reference -->
                <div class="card tax-calc-card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Tax Slabs Reference (FY <?= htmlspecialchars($fy_label) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-warning"><i class="fas fa-sun me-1"></i>Old Regime Slabs</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light"><tr><th>Income Range</th><th class="text-center">Rate</th></tr></thead>
                                    <tbody>
                                        <tr><td>₹0 - ₹2,50,000</td><td class="text-center">0%</td></tr>
                                        <tr><td>₹2,50,001 - ₹5,00,000</td><td class="text-center">5%</td></tr>
                                        <tr><td>₹5,00,001 - ₹10,00,000</td><td class="text-center">20%</td></tr>
                                        <tr><td>₹10,00,001 and above</td><td class="text-center">30%</td></tr>
                                    </tbody>
                                </table>
                                <small class="text-muted">+ 4% Cess | Surcharge: 10% (50L-1Cr), 15% (1Cr-2Cr), 25% (2Cr-5Cr), 37% (>5Cr)</small>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary"><i class="fas fa-moon me-1"></i>New Regime Slabs</h6>
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light"><tr><th>Income Range</th><th class="text-center">Rate</th></tr></thead>
                                    <tbody>
                                        <tr><td>₹0 - ₹3,00,000</td><td class="text-center">0%</td></tr>
                                        <tr><td>₹3,00,001 - ₹6,00,000</td><td class="text-center">5%</td></tr>
                                        <tr><td>₹6,00,001 - ₹9,00,000</td><td class="text-center">10%</td></tr>
                                        <tr><td>₹9,00,001 - ₹12,00,000</td><td class="text-center">15%</td></tr>
                                        <tr><td>₹12,00,001 - ₹15,00,000</td><td class="text-center">20%</td></tr>
                                        <tr><td>₹15,00,001 and above</td><td class="text-center">30%</td></tr>
                                    </tbody>
                                </table>
                                <small class="text-muted">+ 4% Cess | Surcharge: Same as old | Standard Deduction ₹50,000 | No 80C/80D/HRA</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
// FY 2024-25 Tax Slabs (Assessment Year 2025-26)
const OLD_SLABS = [
    { limit: 250000, rate: 0 },
    { limit: 500000, rate: 0.05 },
    { limit: 1000000, rate: 0.20 },
    { limit: Infinity, rate: 0.30 }
];

const NEW_SLABS = [
    { limit: 300000, rate: 0 },
    { limit: 600000, rate: 0.05 },
    { limit: 900000, rate: 0.10 },
    { limit: 1200000, rate: 0.15 },
    { limit: 1500000, rate: 0.20 },
    { limit: Infinity, rate: 0.30 }
];

const CESS_RATE = 0.04;
const SURCHARGE_SLABS = [
    { limit: 5000000, rate: 0 },
    { limit: 10000000, rate: 0.10 },
    { limit: 20000000, rate: 0.15 },
    { limit: 50000000, rate: 0.25 },
    { limit: Infinity, rate: 0.37 }
];

const STD_DEDUCTION_OLD = 50000;
const STD_DEDUCTION_NEW = 50000;

function calculateTax(taxableIncome, slabs) {
    let tax = 0;
    let prevLimit = 0;
    const breakdown = [];
    
    for (const slab of slabs) {
        if (taxableIncome <= prevLimit) break;
        const taxableInSlab = Math.min(taxableIncome, slab.limit) - prevLimit;
        if (taxableInSlab > 0) {
            const slabTax = taxableInSlab * slab.rate;
            tax += slabTax;
            breakdown.push({
                range: formatINR(prevLimit) + ' - ' + (slab.limit === Infinity ? 'Above ' + formatINR(prevLimit) : formatINR(slab.limit)),
                rate: (slab.rate * 100) + '%',
                taxable: taxableInSlab,
                tax: slabTax
            });
        }
        prevLimit = slab.limit;
    }
    
    return { tax, breakdown };
}

function getSurcharge(income) {
    for (const slab of SURCHARGE_SLABS) {
        if (income <= slab.limit) return slab.rate;
    }
    return 0;
}

function calculateTotalTax(grossIncome, deductions, regime) {
    const stdDeduction = regime === 'old' ? STD_DEDUCTION_OLD : STD_DEDUCTION_NEW;
    const totalDeductions = regime === 'old' ? deductions + stdDeduction : stdDeduction;
    const taxableIncome = Math.max(0, grossIncome - totalDeductions);
    
    const slabs = regime === 'old' ? OLD_SLABS : NEW_SLABS;
    const { tax, breakdown } = calculateTax(taxableIncome, slabs);
    
    const surchargeRate = getSurcharge(tax);
    const surcharge = tax * surchargeRate;
    const taxWithSurcharge = tax + surcharge;
    const cess = taxWithSurcharge * CESS_RATE;
    const totalTax = taxWithSurcharge + cess;
    
    return {
        grossIncome,
        totalDeductions,
        taxableIncome,
        baseTax: tax,
        surcharge,
        surchargeRate: surchargeRate * 100,
        cess,
        totalTax,
        breakdown,
        effectiveRate: (totalTax / grossIncome) * 100
    };
}

function formatINR(num) {
    return '₹' + num.toLocaleString('en-IN', { maximumFractionDigits: 0 });
}

function formatINR2(num) {
    return '₹' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function calculateHRAExemption(basic, hraReceived, rentPaid, isMetro) {
    const hraExempt1 = hraReceived;
    const hraExempt2 = rentPaid - (0.10 * basic);
    const hraExempt3 = basic * (isMetro ? 0.5 : 0.4);
    return Math.min(hraExempt1, hraExempt2, hraExempt3);
}

document.getElementById('taxCalcForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    // Monthly to Annual
    const monthlyBasic = parseFloat(data.basic) || 0;
    const monthlyHRA = parseFloat(data.hra) || 0;
    const monthlyConveyance = parseFloat(data.conveyance) || 0;
    const monthlyMedical = parseFloat(data.medical) || 0;
    const monthlySpecial = parseFloat(data.special) || 0;
    const monthlyOther = parseFloat(data.other) || 0;
    
    const grossAnnual = (monthlyBasic + monthlyHRA + monthlyConveyance + monthlyMedical + monthlySpecial + monthlyOther) * 12;
    
    // Deductions (Annual)
    const ded80C = Math.min(parseFloat(data.ded_80c) || 0, 150000);
    const ded80CCD1B = Math.min(parseFloat(data.ded_80ccd1b) || 0, 50000);
    const ded80D = Math.min(parseFloat(data.ded_80d) || 0, 100000);
    const ded24B = Math.min(parseFloat(data.ded_24b) || 0, 200000);
    const ded80E = parseFloat(data.ded_80e) || 0;
    const dedPT = Math.min(parseFloat(data.ded_pt) || 0, 2500);
    
    // HRA Exemption Calculation
    const basicAnnual = monthlyBasic * 12;
    const hraReceivedAnnual = monthlyHRA * 12;
    const rentPaidAnnual = (parseFloat(data.rent_paid) || 0) * 12;
    const isMetro = data.city_type === 'metro';
    const hraExemption = calculateHRAExemption(basicAnnual, hraReceivedAnnual, rentPaidAnnual, isMetro);
    const hraExemptionCapped = Math.min(hraExemption, parseFloat(data.ded_hra) || 0);
    
    // Total Deductions (Old Regime)
    const oldDeductions = {
        'Standard Deduction': 50000,
        'Section 80C': Math.min(ded80C, 150000),
        'Section 80CCD(1B)': Math.min(ded80CCD1B, 50000),
        'Section 80D': Math.min(ded80D, 100000),
        'Section 24(b)': Math.min(ded24B, 200000),
        'Section 80E': ded80E,
        'Professional Tax': dedPT,
        'HRA Exemption': hraExemptionCapped
    };
    
    const oldTotalDeductions = Object.values(oldDeductions).reduce((a, b) => a + b, 0);
    
    // Old Regime Calculation
    const oldResult = calculateTotalTax(grossAnnual, oldTotalDeductions, 'old');
    
    // New Regime Calculation (only standard deduction)
    const newResult = calculateTotalTax(grossAnnual, STD_DEDUCTION_NEW, 'new');
    
    // Savings
    const savings = oldResult.totalTax - newResult.totalTax;
    const savingsPct = grossAnnual > 0 ? ((savings / grossAnnual) * 100).toFixed(1) : 0;
    const betterRegime = savings > 0 ? 'Old Regime' : (savings < 0 ? 'New Regime' : 'Both Equal');
    
    // Display Results
    showResults(oldResult, newResult, savings, savingsPct, betterRegime, grossAnnual, oldDeductions);
});

function showResults(oldResult, newResult, savings, savingsPct, betterRegime, grossIncome, oldDeductions) {
    document.getElementById('resultsSection').classList.remove('d-none');
    document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
    
    // Old Regime
    document.getElementById('oldTotalTax').textContent = formatINR(oldResult.totalTax);
    document.getElementById('oldRateBar').style.width = Math.min(oldResult.effectiveRate * 5, 100) + '%';
    document.getElementById('oldRateText').textContent = oldResult.effectiveRate.toFixed(2) + '%';
    
    // Old Breakdown Table
    const oldTbody = document.querySelector('#oldBreakdownTable tbody');
    oldTbody.innerHTML = '';
    oldResult.breakdown.forEach(b => {
        const row = document.createElement('tr');
        row.innerHTML = `<td>${b.range}</td><td class="text-end">${formatINR(b.tax)}</td>`;
        oldTbody.appendChild(row);
    });
    // Add surcharge, cess, total rows
    const oldTotalRows = [
        { label: 'Base Tax', value: oldResult.baseTax },
        { label: 'Surcharge (' + oldResult.surchargeRate.toFixed(1) + '%)', value: oldResult.surcharge },
        { label: 'Health & Education Cess (4%)', value: oldResult.cess },
        { label: 'Total Tax', value: oldResult.totalTax, bold: true }
    ];
    oldTotalRows.forEach(r => {
        const row = document.createElement('tr');
        if (r.bold) row.classList.add('fw-bold');
        row.innerHTML = `<td>${r.label}</td><td class="text-end">${formatINR(r.value)}</td>`;
        oldTbody.appendChild(row);
    });
    
    // Old Deductions
    const oldDedDiv = document.getElementById('oldDeductionsList');
    oldDedDiv.innerHTML = '';
    Object.entries(oldDeductions).forEach(([key, val]) => {
        if (val > 0) {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between small';
            div.innerHTML = `<span>${key}</span><span class="fw-bold">${formatINR(val)}</span>`;
            oldDedDiv.appendChild(div);
        });
    
    // New Regime
    document.getElementById('newTotalTax').textContent = formatINR(newResult.totalTax);
    document.getElementById('newRateBar').style.width = Math.min(newResult.effectiveRate * 5, 100) + '%';
    document.getElementById('newRateText').textContent = newResult.effectiveRate.toFixed(2) + '%';
    
    const newTbody = document.querySelector('#newBreakdownTable tbody');
    newTbody.innerHTML = '';
    newResult.breakdown.forEach(b => {
        const row = document.createElement('tr');
        row.innerHTML = `<td>${b.range}</td><td class="text-end">${formatINR(b.tax)}</td>`;
        newTbody.appendChild(row);
    });
    
    const newTotalRows = [
        { label: 'Base Tax', value: newResult.baseTax },
        { label: 'Surcharge (' + newResult.surchargeRate.toFixed(1) + '%)', value: newResult.surcharge },
        { label: 'Health & Education Cess (4%)', value: newResult.cess },
        { label: 'Total Tax', value: newResult.totalTax, bold: true }
    ];
    newTotalRows.forEach(r => {
        const row = document.createElement('tr');
        if (r.bold) row.classList.add('fw-bold');
        row.innerHTML = `<td>${r.label}</td><td class="text-end">${formatINR(r.value)}</td>`;
        newTbody.appendChild(row);
    });
    
    document.getElementById('newDeductionsList').innerHTML = `
        <div class="d-flex justify-content-between small">
            <span>Standard Deduction</span><span class="fw-bold">${formatINR(STD_DEDUCTION_NEW)}</span>
        </div>
    `;
    
    // Savings
    document.getElementById('savingsAmount').textContent = formatINR(Math.abs(savings));
    document.getElementById('savingsPercent').textContent = Math.abs(savingsPct).toFixed(1) + '%';
    document.getElementById('betterRegime').textContent = betterRegime;
    document.getElementById('savingsFill').style.width = Math.min(Math.abs(savingsPct) * 5, 100) + '%';
    
    const recText = document.getElementById('recommendationText');
    if (savings > 0) {
        recText.innerHTML = `<i class="fas fa-arrow-up text-success me-1"></i><strong>Old Regime saves ₹${formatINR(savings)}</strong> — Recommended: <strong>Old Regime</strong>`;
        recText.className = 'text-success';
    } else if (savings < 0) {
        recText.innerHTML = `<i class="fas fa-arrow-down text-primary me-1"></i><strong>New Regime saves ₹${formatINR(Math.abs(savings))}</strong> — Recommended: <strong>New Regime</strong>`;
        recText.className = 'text-primary';
    } else {
        recText.innerHTML = 'Both regimes result in the same tax. You can choose either.';
        recText.className = 'text-muted';
    }
    
    // Animate bars
    setTimeout(() => {
        document.getElementById('oldRateBar').style.width = Math.min(oldResult.effectiveRate * 5, 100) + '%';
        document.getElementById('newRateBar').style.width = Math.min(newResult.effectiveRate * 5, 100) + '%';
        document.getElementById('savingsFill').style.width = Math.min(Math.abs(savingsPct) * 5, 100) + '%';
    }, 100);
}

function resetForm() {
    document.getElementById('taxCalcForm').reset();
    document.getElementById('resultsSection').classList.add('d-none');
    // Restore pre-filled salary values
    setTimeout(() => {
        <?php if (!empty($currentSalary)): ?>
        document.querySelector('[name="basic"]').value = '<?= $currentSalary['basic'] ?>';
        document.querySelector('[name="hra"]').value = '<?= $currentSalary['hra'] ?>';
        document.querySelector('[name="conveyance"]').value = '<?= $currentSalary['conveyance'] ?>';
        document.querySelector('[name="medical"]').value = '<?= $currentSalary['medical'] ?>';
        document.querySelector('[name="special"]').value = '<?= $currentSalary['special'] ?>';
        document.querySelector('[name="other"]').value = '<?= $currentSalary['other'] ?>';
        <?php endif; ?>
    }, 100);
}

// Initialize with pre-filled values
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($currentSalary)): ?>
    document.querySelector('[name="basic"]').value = '<?= $currentSalary['basic'] ?>';
    document.querySelector('[name="hra"]').value = '<?= $currentSalary['hra'] ?>';
    document.querySelector('[name="conveyance"]').value = '<?= $currentSalary['conveyance'] ?>';
    document.querySelector('[name="medical"]').value = '<?= $currentSalary['medical'] ?>';
    document.querySelector('[name="special"]').value = '<?= $currentSalary['special'] ?>';
    document.querySelector('[name="other"]').value = '<?= $currentSalary['other'] ?>';
    <?php endif; ?>
});
</script>