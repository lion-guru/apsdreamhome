<?php
/**
 * Associate Portal - Plot Size Converter
 */
$page_title = $page_title ?? 'Plot Size Converter - Associate Portal';
$result = $result ?? null;
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf_token = $_SESSION['csrf_token'] ?? '';
$initVal = (float)($result['input'] ?? ($_POST['value'] ?? 1000));
$fromUnit = $result['from'] ?? ($_POST['from_unit'] ?? 'sqft');
$toUnit = $result['to'] ?? ($_POST['to_unit'] ?? 'sqyd');

$units = [
    'sqft'    => 'Square Feet (Sq. Ft)',
    'sqyd'    => 'Square Yards (Gaj / Sq. Yd)',
    'biswa'   => 'Biswa (1,350 Sq. Ft)',
    'bigha'   => 'Bigha (UP: 27,000 Sq. Ft)',
    'sqm'     => 'Square Meters (Sq. M)',
    'acre'    => 'Acre (43,560 Sq. Ft)',
    'hectare' => 'Hectare (1,07,639 Sq. Ft)',
];
?>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?>/associate/tools">Tools</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Plot Size Converter</li>
                </ol>
            </nav>
            <h2 class="h4 mb-0 text-dark fw-bold"><i class="fas fa-ruler-combined me-2 text-teal" style="color: #0d9488;"></i> Plot & Land Area Converter</h2>
            <small class="text-muted">Instant conversion between Sq.Ft, Gaj, Biswa, Bigha, and Acres</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/associate/tools" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> All Tools
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Input Column -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-exchange-alt me-2 text-primary"></i> Area Input & Units</h5>
                </div>
                <div class="card-body p-4 pt-1">
                    <!-- Quick Presets -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted mb-2">Quick Standard Plot Sizes</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-plot" data-val="800" data-unit="sqft">800 sqft</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-plot" data-val="1000" data-unit="sqft">1,000 sqft</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-plot" data-val="1200" data-unit="sqft">1,200 sqft</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-plot" data-val="100" data-unit="sqyd">100 Gaj</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-plot" data-val="1" data-unit="biswa">1 Biswa</button>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill preset-plot" data-val="1" data-unit="bigha">1 Bigha</button>
                        </div>
                    </div>

                    <form id="plotForm" method="POST" action="<?= $base ?>/associate/tools/plot-converter">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Value to Convert</label>
                            <div class="input-group">
                                <input type="number" id="inputValue" name="value" class="form-control form-control-lg" placeholder="e.g. 1000" 
                                       value="<?= htmlspecialchars((string)$initVal) ?>" required min="0.001" step="any">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">From Unit</label>
                            <select id="selectFrom" name="from_unit" class="form-select form-select-lg">
                                <?php foreach ($units as $key => $lbl): ?>
                                    <option value="<?= $key ?>" <?= $key === $fromUnit ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Primary Target Unit</label>
                            <select id="selectTo" name="to_unit" class="form-select">
                                <?php foreach ($units as $key => $lbl): ?>
                                    <option value="<?= $key ?>" <?= $key === $toUnit ? 'selected' : '' ?>><?= $lbl ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary py-2 fw-bold text-white shadow-sm" style="background-color: #0d9488; border-color: #0d9488;">
                                <i class="fas fa-sync-alt me-2"></i> Convert & Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Result Grid Column -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-success fw-bold"><i class="fas fa-th-large me-2"></i> All Area Conversions at a Glance</h5>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">Live Multi-Unit Matrix</span>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Primary Target Card -->
                        <div class="text-center p-3 rounded-4 mb-4" style="background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); border: 1px solid #99f6e4;">
                            <span class="text-muted text-uppercase small fw-bold d-block mb-1">Target Result (<span id="resPrimaryLabel">Gaj</span>)</span>
                            <h2 class="display-5 fw-bold text-teal mb-0" id="resPrimaryValue" style="color: #0f766e;">0</h2>
                            <small class="text-muted" id="resInputEcho">from 1,000 Sq. Ft</small>
                        </div>

                        <!-- All Units Grid -->
                        <div class="row g-3" id="unitsGrid">
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="text-muted small d-block">Square Feet (Sq. Ft)</span>
                                    <h4 class="fw-bold mb-0 text-dark" id="grid_sqft">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="text-muted small d-block">Square Yards (Gaj)</span>
                                    <h4 class="fw-bold mb-0 text-teal" id="grid_sqyd" style="color: #0d9488;">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="text-muted small d-block">Biswa (1,350 sqft)</span>
                                    <h4 class="fw-bold mb-0 text-dark" id="grid_biswa">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="text-muted small d-block">Bigha (UP: 27,000 sqft)</span>
                                    <h4 class="fw-bold mb-0 text-dark" id="grid_bigha">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="text-muted small d-block">Square Meters (Sq. M)</span>
                                    <h4 class="fw-bold mb-0 text-dark" id="grid_sqm">0</h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="text-muted small d-block">Acre (43,560 sqft)</span>
                                    <h4 class="fw-bold mb-0 text-dark" id="grid_acre">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client Sharing Action -->
                    <div class="pt-3 border-top mt-4">
                        <button type="button" class="btn btn-success w-100 py-2 fw-bold text-white rounded-pill shadow-sm" id="btnShareWhatsApp">
                            <i class="fab fa-whatsapp me-2 fs-5"></i> Share Plot Area Breakdown on WhatsApp
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function () {
    const inputVal = document.getElementById('inputValue');
    const selectFrom = document.getElementById('selectFrom');
    const selectTo = document.getElementById('selectTo');

    const resPrimaryValue = document.getElementById('resPrimaryValue');
    const resPrimaryLabel = document.getElementById('resPrimaryLabel');
    const resInputEcho = document.getElementById('resInputEcho');

    const sqftFactor = {
        'sqft': 1,
        'sqyd': 9,
        'sqm': 10.7639,
        'acre': 43560,
        'hectare': 107639,
        'bigha': 27000,
        'biswa': 1350
    };

    const unitLabels = {
        'sqft': 'Sq. Feet',
        'sqyd': 'Gaj (Sq. Yd)',
        'biswa': 'Biswa',
        'bigha': 'Bigha',
        'sqm': 'Sq. Meters',
        'acre': 'Acres',
        'hectare': 'Hectares'
    };

    function calculateLive() {
        const val = parseFloat(inputVal.value) || 0;
        const from = selectFrom.value;
        const to = selectTo.value;

        if (val <= 0) {
            resPrimaryValue.textContent = '0';
            resInputEcho.textContent = 'Enter area above';
            return;
        }

        // Convert to sqft first
        const inSqft = val * (sqftFactor[from] || 1);

        // Convert to primary target
        const primaryTarget = inSqft / (sqftFactor[to] || 1);
        resPrimaryValue.textContent = primaryTarget >= 10 ? Number(primaryTarget.toFixed(2)).toLocaleString('en-IN') : Number(primaryTarget.toFixed(4));
        resPrimaryLabel.textContent = unitLabels[to] || to;
        resInputEcho.textContent = 'Converted from ' + Number(val).toLocaleString('en-IN') + ' ' + (unitLabels[from] || from);

        // Update grid
        Object.keys(sqftFactor).forEach(function(u) {
            const el = document.getElementById('grid_' + u);
            if (el) {
                const converted = inSqft / sqftFactor[u];
                el.textContent = converted >= 10 ? Number(converted.toFixed(2)).toLocaleString('en-IN') : Number(converted.toFixed(4));
            }
        });
    }

    inputVal.addEventListener('input', calculateLive);
    selectFrom.addEventListener('change', calculateLive);
    selectTo.addEventListener('change', calculateLive);

    document.querySelectorAll('.preset-plot').forEach(function(btn) {
        btn.addEventListener('click', function() {
            inputVal.value = this.dataset.val;
            selectFrom.value = this.dataset.unit;
            calculateLive();
        });
    });

    const btnShare = document.getElementById('btnShareWhatsApp');
    if (btnShare) {
        btnShare.addEventListener('click', function() {
            const v = inputVal.value;
            const from = unitLabels[selectFrom.value] || selectFrom.value;
            const sqft = document.getElementById('grid_sqft')?.textContent || '0';
            const sqyd = document.getElementById('grid_sqyd')?.textContent || '0';
            const biswa = document.getElementById('grid_biswa')?.textContent || '0';
            const bigha = document.getElementById('grid_bigha')?.textContent || '0';

            const text = "Namaste! 📐 *APS Dream Home — Plot Size Conversion Chart*\n\n" +
                "• *Given Area:* " + v + " " + from + "\n\n" +
                "📊 *Multi-Unit Area Breakdown:*\n" +
                "• *Square Feet:* " + sqft + " sqft\n" +
                "• *Gaj (Sq. Yards):* " + sqyd + " Gaj\n" +
                "• *Biswa (UP):* " + biswa + " Biswa\n" +
                "• *Bigha (UP):* " + bigha + " Bigha\n\n" +
                "Hamare townships mein sabhi standard dimensions ke plots uplabdh hain. Site visit aur booking ke liye sampark karein! 📞";

            window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(text), '_blank');
        });
    }

    calculateLive();
});
</script>
