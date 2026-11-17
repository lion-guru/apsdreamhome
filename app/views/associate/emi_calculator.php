<?php
/**
 * Associate Portal - EMI Calculator
 */
$page_title = $page_title ?? 'EMI Calculator - Associate Portal';
$result = $result ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $_SESSION['csrf_token'] ?? '';
$initPrincipal = (float)($result['principal'] ?? ($_POST['principal'] ?? $_POST['amount'] ?? 1500000));
$initRate = (float)($result['rate'] ?? ($_POST['rate'] ?? 8.5));
$initTenure = (int)($result['tenure_years'] ?? ($_POST['tenure'] ?? 20));
?>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/tools">Tools</a></li>
                    <li class="breadcrumb-item active" aria-current="page">EMI Calculator</li>
                </ol>
            </nav>
            <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-calculator me-2 text-teal" style="color: #0d9488;"></i> Plot & Home Loan EMI Calculator</h2>
            <small class="text-muted">Instant real-time EMI breakdown for client consultations</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/associate/tools" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> All Tools
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-sliders-h me-2 text-primary"></i> Loan Parameters</h5>
                </div>
                <div class="card-body p-4 pt-1">
                    <!-- Quick Presets -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-2">Quick Amount Presets</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-btn" data-amt="500000">₹5 Lakh</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-btn" data-amt="1000000">₹10 Lakh</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-btn" data-amt="1500000">₹15 Lakh</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-btn" data-amt="2500000">₹25 Lakh</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-btn" data-amt="5000000">₹50 Lakh</button>
                        </div>
                    </div>

                    <form id="emiForm" method="POST" action="<?= $base ?>/associate/tools/emi">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold mb-0">Loan Amount (₹)</label>
                                <span class="badge bg-light text-primary fw-bold" id="badgePrincipal">₹<?= number_format($initPrincipal) ?></span>
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light">₹</span>
                                <input type="number" id="inputPrincipal" name="principal" class="form-control" placeholder="e.g. 1500000" 
                                       value="<?= htmlspecialchars((string)$initPrincipal) ?>" required min="50000" step="10000">
                            </div>
                            <input type="range" class="form-range" id="rangePrincipal" min="100000" max="10000000" step="50000" value="<?= $initPrincipal ?>">
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold mb-0">Interest Rate (% p.a.)</label>
                                <span class="badge bg-light text-primary fw-bold" id="badgeRate"><?= $initRate ?>%</span>
                            </div>
                            <div class="input-group mb-2">
                                <input type="number" id="inputRate" name="rate" class="form-control" placeholder="e.g. 8.5" 
                                       value="<?= htmlspecialchars((string)$initRate) ?>" required min="1" max="25" step="0.1">
                                <span class="input-group-text bg-light">%</span>
                            </div>
                            <input type="range" class="form-range" id="rangeRate" min="6" max="18" step="0.25" value="<?= $initRate ?>">
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold mb-0">Loan Tenure (Years)</label>
                                <span class="badge bg-light text-primary fw-bold" id="badgeTenure"><?= $initTenure ?> Years</span>
                            </div>
                            <div class="input-group mb-2">
                                <input type="number" id="inputTenure" name="tenure" class="form-control" placeholder="e.g. 20" 
                                       value="<?= htmlspecialchars((string)$initTenure) ?>" required min="1" max="30">
                                <span class="input-group-text bg-light">Years</span>
                            </div>
                            <input type="range" class="form-range" id="rangeTenure" min="1" max="30" step="1" value="<?= $initTenure ?>">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold text-white shadow-sm" style="background-color: #0d9488; border-color: #0d9488;">
                                <i class="fas fa-calculator me-2"></i> Calculate & Save Plan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Result Column -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-success fw-bold"><i class="fas fa-chart-pie me-2"></i> EMI Summary & Breakdown</h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">Live Calculated</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-center p-3 rounded-4 mb-4" style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); border: 1px solid #99f6e4;">
                            <span class="text-muted text-uppercase small fw-bold d-block mb-1">Monthly EMI Payment</span>
                            <h2 class="display-5 fw-bold text-teal mb-0" id="resEmi" style="color: #0f766e;">₹0</h2>
                            <small class="text-muted" id="resMonthlyRate">Monthly interest rate ~0.71%</small>
                        </div>

                        <!-- Payment Proportion Bar -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Principal (<span id="pctPrincipal">0%</span>)</span>
                                <span>Interest (<span id="pctInterest">0%</span>)</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 12px;">
                                <div class="progress-bar bg-primary" id="barPrincipal" style="width: 50%"></div>
                                <div class="progress-bar bg-warning" id="barInterest" style="width: 50%"></div>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="text-muted"><i class="fas fa-shield-alt text-primary me-2"></i> Principal Amount</span>
                                <span class="fw-bold text-dark" id="resPrincipal">₹0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="text-muted"><i class="fas fa-percentage text-warning me-2"></i> Total Interest Amount</span>
                                <span class="fw-bold text-danger" id="resInterest">₹0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="text-muted"><i class="fas fa-coins text-success me-2"></i> Total Amount Payable</span>
                                <span class="fw-bold text-teal fs-5" id="resTotal" style="color: #0d9488;">₹0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fas fa-calendar-alt text-secondary me-2"></i> Total Months</span>
                                <span class="fw-bold text-secondary" id="resMonths">240 Months</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Client Sharing Action -->
                    <div class="pt-3 border-top mt-auto">
                        <button type="button" class="btn btn-success w-100 py-2 fw-bold text-white rounded-pill shadow-sm" id="btnShareWhatsApp">
                            <i class="fab fa-whatsapp me-2 fs-5"></i> Share Quote with Client on WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
    const inputPrincipal = document.getElementById('inputPrincipal');
    const rangePrincipal = document.getElementById('rangePrincipal');
    const badgePrincipal = document.getElementById('badgePrincipal');

    const inputRate = document.getElementById('inputRate');
    const rangeRate = document.getElementById('rangeRate');
    const badgeRate = document.getElementById('badgeRate');

    const inputTenure = document.getElementById('inputTenure');
    const rangeTenure = document.getElementById('rangeTenure');
    const badgeTenure = document.getElementById('badgeTenure');

    const resEmi = document.getElementById('resEmi');
    const resPrincipal = document.getElementById('resPrincipal');
    const resInterest = document.getElementById('resInterest');
    const resTotal = document.getElementById('resTotal');
    const resMonths = document.getElementById('resMonths');
    const resMonthlyRate = document.getElementById('resMonthlyRate');
    const pctPrincipal = document.getElementById('pctPrincipal');
    const pctInterest = document.getElementById('pctInterest');
    const barPrincipal = document.getElementById('barPrincipal');
    const barInterest = document.getElementById('barInterest');

    function formatInr(val) {
        return '₹' + Math.round(val).toLocaleString('en-IN');
    }

    function calculateLive() {
        const principal = parseFloat(inputPrincipal.value) || 0;
        const annualRate = parseFloat(inputRate.value) || 0;
        const tenureYears = parseInt(inputTenure.value, 10) || 0;

        badgePrincipal.textContent = formatInr(principal);
        badgeRate.textContent = annualRate + '%';
        badgeTenure.textContent = tenureYears + ' Years';

        if (principal <= 0 || annualRate <= 0 || tenureYears <= 0) {
            resEmi.textContent = '₹0';
            resPrincipal.textContent = formatInr(0);
            resInterest.textContent = formatInr(0);
            resTotal.textContent = formatInr(0);
            resMonths.textContent = '0 Months';
            return;
        }

        const monthlyRate = (annualRate / 100) / 12;
        const months = tenureYears * 12;
        const emi = (principal * monthlyRate * Math.pow(1 + monthlyRate, months)) / (Math.pow(1 + monthlyRate, months) - 1);
        const totalPayment = emi * months;
        const totalInterest = totalPayment - principal;

        resEmi.textContent = formatInr(emi);
        resPrincipal.textContent = formatInr(principal);
        resInterest.textContent = formatInr(totalInterest);
        resTotal.textContent = formatInr(totalPayment);
        resMonths.textContent = months + ' Months (' + tenureYears + ' Yrs)';
        resMonthlyRate.textContent = 'Monthly interest rate: ' + (monthlyRate * 100).toFixed(3) + '%';

        const pPct = Math.round((principal / totalPayment) * 100);
        const iPct = 100 - pPct;
        pctPrincipal.textContent = pPct + '%';
        pctInterest.textContent = iPct + '%';
        barPrincipal.style.width = pPct + '%';
        barInterest.style.width = iPct + '%';
    }

    // Sync input <-> range
    inputPrincipal.addEventListener('input', function() {
        rangePrincipal.value = inputPrincipal.value;
        calculateLive();
    });
    rangePrincipal.addEventListener('input', function() {
        inputPrincipal.value = rangePrincipal.value;
        calculateLive();
    });

    inputRate.addEventListener('input', function() {
        rangeRate.value = inputRate.value;
        calculateLive();
    });
    rangeRate.addEventListener('input', function() {
        inputRate.value = rangeRate.value;
        calculateLive();
    });

    inputTenure.addEventListener('input', function() {
        rangeTenure.value = inputTenure.value;
        calculateLive();
    });
    rangeTenure.addEventListener('input', function() {
        inputTenure.value = rangeTenure.value;
        calculateLive();
    });

    // Preset buttons
    document.querySelectorAll('.preset-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const amt = this.dataset.amt;
            inputPrincipal.value = amt;
            rangePrincipal.value = amt;
            calculateLive();
        });
    });

    // WhatsApp Share Quote
    const btnShare = document.getElementById('btnShareWhatsApp');
    if (btnShare) {
        btnShare.addEventListener('click', function() {
            const p = inputPrincipal.value;
            const r = inputRate.value;
            const t = inputTenure.value;
            const emi = resEmi.textContent;
            const tot = resTotal.textContent;

            const text = "Namaste! 🏠 *APS Dream Home — Loan EMI Estimate*\n\n" +
                "• *Loan Amount:* " + formatInr(p) + "\n" +
                "• *Interest Rate:* " + r + "% p.a.\n" +
                "• *Tenure:* " + t + " Years (" + (t * 12) + " Months)\n\n" +
                "💰 *Estimated Monthly EMI:* " + emi + "/month\n" +
                "📊 *Total Payable Amount:* " + tot + "\n\n" +
                "Hamare township plots aur housing projects par flexible loan aur registry suvidha uplabdh hai. Adhik jankari ke liye mujhse sampark karein! 📞";

            window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank');
        });
    }

    // Initial calculation
    calculateLive();
});
</script>
