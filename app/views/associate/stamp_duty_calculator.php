<?php
/**
 * Associate Portal - Stamp Duty Calculator
 */
$page_title = $page_title ?? 'Stamp Duty Calculator - Associate Portal';
$result = $result ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $_SESSION['csrf_token'] ?? '';
$initVal = (float)($result['property_value'] ?? ($_POST['property_value'] ?? 2500000));
$initState = $_POST['state'] ?? 'Uttar Pradesh';
$initGender = $_POST['gender'] ?? 'male';
?>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/tools">Tools</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Stamp Duty Calculator</li>
                </ol>
            </nav>
            <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-stamp me-2 text-teal" style="color: #0d9488;"></i> Stamp Duty & Registration Calculator</h2>
            <small class="text-muted">Calculate legal registry charges and stamp duty across states</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/associate/tools" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> All Tools
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-file-contract me-2 text-primary"></i> Property & Registration Details</h5>
                </div>
                <div class="card-body p-4 pt-1">
                    <!-- Quick Presets -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-2">Quick Property Value Presets</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-sd" data-amt="1000000">₹10 Lakh</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-sd" data-amt="2500000">₹25 Lakh</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-sd" data-amt="5000000">₹50 Lakh</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-sd" data-amt="10000000">₹1 Crore</button>
                        </div>
                    </div>

                    <form id="sdForm" method="POST" action="<?= $base ?>/associate/tools/stamp-duty">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-bold mb-0">Property Value / Circle Rate Value (₹)</label>
                                <span class="badge bg-light text-primary fw-bold" id="badgeVal">₹<?= number_format($initVal) ?></span>
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light">₹</span>
                                <input type="number" id="inputVal" name="property_value" class="form-control" placeholder="e.g. 2500000" 
                                       value="<?= htmlspecialchars((string)$initVal) ?>" required min="50000" step="10000">
                            </div>
                            <input type="range" class="form-range" id="rangeVal" min="200000" max="20000000" step="50000" value="<?= $initVal ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">State / Jurisdiction</label>
                            <select id="selectState" name="state" class="form-select">
                                <?php
                                $states = [
                                    'Uttar Pradesh' => 'Uttar Pradesh (UP Standard: 7% / 6% Female)',
                                    'Maharashtra'   => 'Maharashtra (6% / 5% Female)',
                                    'Delhi'         => 'Delhi (6% / 4% Female)',
                                    'Karnataka'     => 'Karnataka (5.6%)',
                                    'Tamil Nadu'    => 'Tamil Nadu (7%)',
                                    'Gujarat'       => 'Gujarat (4.9%)',
                                    'Rajasthan'     => 'Rajasthan (6% / 5% Female)',
                                ];
                                foreach ($states as $st => $lbl): ?>
                                    <option value="<?= $st ?>" <?= $st === $initState ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Primary Buyer Gender</label>
                            <div class="d-flex gap-4 p-3 bg-light rounded-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male" 
                                           <?= $initGender === 'male' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="genderMale">Male</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female"
                                           <?= $initGender === 'female' ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold text-success" for="genderFemale">
                                        Female (1% Concession in UP/Delhi/Raj)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold text-white shadow-sm" style="background-color: #0d9488; border-color: #0d9488;">
                                <i class="fas fa-calculator me-2"></i> Calculate & Save Estimate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Result -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-success fw-bold"><i class="fas fa-receipt me-2"></i> Registration Charges Breakdown</h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">Live Calculated</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="text-center p-3 rounded-4 mb-4" style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); border: 1px solid #99f6e4;">
                            <span class="text-muted text-uppercase small fw-bold d-block mb-1">Total Legal & Registry Cost</span>
                            <h2 class="display-5 fw-bold text-teal mb-0" id="resTotal" style="color: #0f766e;">₹0</h2>
                            <small class="text-muted" id="resRateNote">Combined stamp duty + registry charges</small>
                        </div>

                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="text-muted"><i class="fas fa-home text-primary me-2"></i> Property Consideration Value</span>
                                <span class="fw-bold text-dark" id="resPropVal">₹0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="text-muted"><i class="fas fa-stamp text-warning me-2"></i> Stamp Duty (<span id="resDutyRate">7%</span>)</span>
                                <span class="fw-bold text-danger" id="resDutyAmt">₹0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-bottom">
                                <span class="text-muted"><i class="fas fa-file-invoice text-info me-2"></i> Registration Fee (1% Fixed)</span>
                                <span class="fw-bold text-info" id="resRegFee">₹0</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span class="text-muted"><i class="fas fa-gift text-success me-2"></i> Female Buyer Rebate Benefit</span>
                                <span class="fw-bold text-success" id="resSavings">₹0</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Client Sharing Action -->
                    <div class="pt-3 border-top mt-auto">
                        <button type="button" class="btn btn-success w-100 py-2 fw-bold text-white rounded-pill shadow-sm" id="btnShareWhatsApp">
                            <i class="fab fa-whatsapp me-2 fs-5"></i> Share Registry Quote on WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
    const inputVal = document.getElementById('inputVal');
    const rangeVal = document.getElementById('rangeVal');
    const badgeVal = document.getElementById('badgeVal');
    const selectState = document.getElementById('selectState');
    const radioMale = document.getElementById('genderMale');
    const radioFemale = document.getElementById('genderFemale');

    const resTotal = document.getElementById('resTotal');
    const resPropVal = document.getElementById('resPropVal');
    const resDutyRate = document.getElementById('resDutyRate');
    const resDutyAmt = document.getElementById('resDutyAmt');
    const resRegFee = document.getElementById('resRegFee');
    const resSavings = document.getElementById('resSavings');
    const resRateNote = document.getElementById('resRateNote');

    const rates = {
        'Uttar Pradesh': { male: 7, female: 6 },
        'Maharashtra': { male: 6, female: 5 },
        'Delhi': { male: 6, female: 4 },
        'Karnataka': { male: 5.6, female: 5.6 },
        'Tamil Nadu': { male: 7, female: 7 },
        'Gujarat': { male: 4.9, female: 4.9 },
        'Rajasthan': { male: 6, female: 5 }
    };

    function formatInr(val) {
        return '₹' + Math.round(val).toLocaleString('en-IN');
    }

    function calculateLive() {
        const val = parseFloat(inputVal.value) || 0;
        const state = selectState.value || 'Uttar Pradesh';
        const gender = radioFemale.checked ? 'female' : 'male';

        badgeVal.textContent = formatInr(val);

        if (val <= 0) {
            resTotal.textContent = '₹0';
            resPropVal.textContent = formatInr(0);
            resDutyAmt.textContent = formatInr(0);
            resRegFee.textContent = formatInr(0);
            resSavings.textContent = '₹0';
            return;
        }

        const stateRates = rates[state] || { male: 7, female: 6 };
        const dutyRate = stateRates[gender] ?? 7;
        const stampDuty = (val * dutyRate) / 100;
        const regFee = val * 0.01; // 1%
        const total = stampDuty + regFee;

        const maxRate = Math.max(stateRates.male, stateRates.female);
        const savings = (gender === 'female' && dutyRate < maxRate) ? (val * (maxRate - dutyRate) / 100) : 0;

        resPropVal.textContent = formatInr(val);
        resDutyRate.textContent = dutyRate + '%';
        resDutyAmt.textContent = formatInr(stampDuty);
        resRegFee.textContent = formatInr(regFee);
        resTotal.textContent = formatInr(total);
        resSavings.textContent = savings > 0 ? ('Saved ' + formatInr(savings) + ' (1% Rebate)') : 'Standard Rate';
        resRateNote.textContent = state + ' • ' + dutyRate + '% Stamp Duty + 1% Registry Fee';
    }

    inputVal.addEventListener('input', function() {
        rangeVal.value = inputVal.value;
        calculateLive();
    });
    rangeVal.addEventListener('input', function() {
        inputVal.value = rangeVal.value;
        calculateLive();
    });
    selectState.addEventListener('change', calculateLive);
    radioMale.addEventListener('change', calculateLive);
    radioFemale.addEventListener('change', calculateLive);

    document.querySelectorAll('.preset-sd').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const amt = this.dataset.amt;
            inputVal.value = amt;
            rangeVal.value = amt;
            calculateLive();
        });
    });

    const btnShare = document.getElementById('btnShareWhatsApp');
    if (btnShare) {
        btnShare.addEventListener('click', function() {
            const p = inputVal.value;
            const s = selectState.value;
            const r = resDutyRate.textContent;
            const sd = resDutyAmt.textContent;
            const reg = resRegFee.textContent;
            const tot = resTotal.textContent;

            const text = "Namaste! 📜 *APS Dream Home — Property Registry Charges Estimate*\n\n" +
                "• *Property Value:* " + formatInr(p) + "\n" +
                "• *State:* " + s + "\n" +
                "• *Stamp Duty (" + r + "):* " + sd + "\n" +
                "• *Registration Fee (1%):* " + reg + "\n\n" +
                "💰 *Total Registration Cost:* " + tot + "\n\n" +
                "APS Dream Home ke sabhi township plots par transparent legal registry suvidha uplabdh hai. Adhik jankari ke liye call karein! 📞";

            window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank');
        });
    }

    calculateLive();
});
</script>
