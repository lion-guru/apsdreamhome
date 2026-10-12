<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
$mode = $mode ?? 'create';
$master = $master ?? null;
$data = $data ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
    <style>
        .calc-result { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: .375rem; padding: 1rem; margin-top: 1rem; }
        .calc-row { display: flex; justify-content: space-between; padding: .25rem 0; }
        .calc-row.total { border-top: 1px solid #dee2e6; margin-top: .5rem; padding-top: .75rem; font-weight: 600; font-size: 1.1rem; color: #6B4EE6; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-<?= $mode === 'create' ? 'plus' : 'edit' ?> me-2 text-primary"></i><?= htmlspecialchars($page_title) ?></h1>
        <a href="<?= $base ?>/admin/property-tax" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <form method="POST" id="taxForm" class="needs-validation" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
        
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tax Authority <span class="text-danger">*</span></label>
                                <input type="text" name="tax_authority" class="form-control" required value="<?= htmlspecialchars($data['tax_authority'] ?? '') ?>" placeholder="e.g., Gorakhpur Municipal Corporation">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ward / Zone</label>
                                <input type="text" name="ward_zone" class="form-control" value="<?= htmlspecialchars($data['ward_zone'] ?? '') ?>" placeholder="e.g., Ward 12, Zone 3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tax Type <span class="text-danger">*</span></label>
                                <select name="tax_type" class="form-select" required>
                                    <?php foreach ($taxTypes as $t): ?>
                                        <option value="<?= $t ?>" <?= ($data['tax_type'] ?? '') == $t ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $t)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Assessment Year <span class="text-danger">*</span></label>
                                <select name="assessment_year" class="form-select" required>
                                    <?php foreach ($years as $y): ?>
                                        <option value="<?= $y ?>" <?= ($data['assessment_year'] ?? date('Y')) == $y ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Property Category</label>
                                <select name="property_category" class="form-select">
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c ?>" <?= ($data['property_category'] ?? '') == $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Colony</label>
                                <select name="colony_id" class="form-select" id="colonySelect">
                                    <option value="">Select Colony (Optional)</option>
                                    <?php foreach ($colonies as $c): ?>
                                        <option value="<?= $c['id'] ?>" <?= ($data['colony_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Plot</label>
                                <select name="plot_id" class="form-select" id="plotSelect">
                                    <option value="">Select Plot (Optional)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Tax Calculation Parameters</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Rate Unit</label>
                                <select name="rate_unit" class="form-select" id="rateUnit">
                                    <?php foreach ($rateUnits as $r): ?>
                                        <option value="<?= $r ?>" <?= ($data['rate_unit'] ?? '') == $r ? 'selected' : '' ?>><?= ucwords(str_replace('_', ' ', $r)) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tax Rate</label>
                                <input type="number" step="0.0001" name="tax_rate" class="form-control" id="taxRate" value="<?= htmlspecialchars($data['tax_rate'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Built-up Area (sq ft)</label>
                                <input type="number" step="0.01" name="built_up_area" class="form-control" id="builtUpArea" value="<?= htmlspecialchars($data['built_up_area'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Land Area (sq ft)</label>
                                <input type="number" step="0.01" name="land_area" class="form-control" id="landArea" value="<?= htmlspecialchars($data['land_area'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Annual Rental Value</label>
                                <input type="number" step="0.01" name="annual_rental_value" class="form-control" id="annualRentalValue" value="<?= htmlspecialchars($data['annual_rental_value'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Capital Value</label>
                                <input type="number" step="0.01" name="capital_value" class="form-control" id="capitalValue" value="<?= htmlspecialchars($data['capital_value'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Rebate %</label>
                                <input type="number" step="0.01" min="0" max="100" name="rebate_percent" class="form-control" id="rebatePercent" value="<?= htmlspecialchars($data['rebate_percent'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Surcharge %</label>
                                <input type="number" step="0.01" min="0" name="surcharge_percent" class="form-control" id="surchargePercent" value="<?= htmlspecialchars($data['surcharge_percent'] ?? 0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Penalty Rate %/month</label>
                                <input type="number" step="0.01" min="0" name="penalty_rate_per_month" class="form-control" value="<?= htmlspecialchars($data['penalty_rate_per_month'] ?? 1.0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Max Penalty %</label>
                                <input type="number" step="0.01" min="0" name="max_penalty_percent" class="form-control" value="<?= htmlspecialchars($data['max_penalty_percent'] ?? 25.0) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($data['due_date'] ?? date('Y-m-d', strtotime('+30 days'))) ?>">
                            </div>
                        </div>

                        <div class="calc-result" id="calcResult">
                            <h6 class="mb-3"><i class="fas fa-rupee-sign me-1"></i>Calculated Tax Breakdown</h6>
                            <div class="calc-row"><span>Base Tax:</span><span id="baseTax">₹0.00</span></div>
                            <div class="calc-row"><span>Rebate:</span><span id="rebateAmt" class="text-success">-₹0.00</span></div>
                            <div class="calc-row"><span>Surcharge:</span><span id="surchargeAmt" class="text-danger">+₹0.00</span></div>
                            <div class="calc-row total"><span>Net Tax:</span><span id="netTax">₹0.00</span></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Status & Notes</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft" <?= ($data['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= ($data['status'] ?? '') == 'published' ? 'selected' : '' ?>>Published</option>
                                    <option value="demand_generated" <?= ($data['status'] ?? '') == 'demand_generated' ? 'selected' : '' ?>>Demand Generated</option>
                                    <option value="closed" <?= ($data['status'] ?? '') == 'closed' ? 'selected' : '' ?>>Closed</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($data['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 100px;">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-save me-2"></i>Actions</h5></div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-3 btn-lg"><i class="fas fa-save me-1"></i> <?= $mode === 'create' ? 'Create Assessment' : 'Update Assessment' ?></button>
                        <a href="<?= $base ?>/admin/property-tax" class="btn btn-outline-secondary w-100 mb-3"><i class="fas fa-times me-1"></i> Cancel</a>
                        <hr>
                        <h6 class="text-muted small">Quick Help</h6>
                        <ul class="small text-muted mb-0">
                            <li>Rate Unit "per_sqft" uses built-up + land area</li>
                            <li>"percent_arv" uses Annual Rental Value</li>
                            <li>"percent_cv" uses Capital Value</li>
                            <li>"flat" uses Tax Rate as fixed amount</li>
                            <li>Rebate reduces base tax</li>
                            <li>Surcharge adds to base tax</li>
                            <li>Penalty calculated per month after due date</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
const plotsByColony = <?= json_encode($plots) ?>;
const colonySelect = document.getElementById('colonySelect');
const plotSelect = document.getElementById('plotSelect');

function updatePlots() {
    const colonyId = colonySelect.value;
    plotSelect.innerHTML = '<option value="">Select Plot (Optional)</option>';
    plotsByColony.filter(p => p.colony_id == colonyId).forEach(p => {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = p.plot_number;
        if ('<?= $data['plot_id'] ?? '' ?>' == p.id) opt.selected = true;
        plotSelect.appendChild(opt);
    });
}
colonySelect.addEventListener('change', updatePlots);
updatePlots();

const calcFields = ['taxRate', 'builtUpArea', 'landArea', 'annualRentalValue', 'capitalValue', 'rebatePercent', 'surchargePercent', 'rateUnit'];
calcFields.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', calculateTax);
});
document.getElementById('rateUnit').addEventListener('change', calculateTax);

function calculateTax() {
    const rate = parseFloat(document.getElementById('taxRate').value) || 0;
    const unit = document.getElementById('rateUnit').value;
    const builtUp = parseFloat(document.getElementById('builtUpArea').value) || 0;
    const land = parseFloat(document.getElementById('landArea').value) || 0;
    const arv = parseFloat(document.getElementById('annualRentalValue').value) || 0;
    const cv = parseFloat(document.getElementById('capitalValue').value) || 0;
    const rebatePct = parseFloat(document.getElementById('rebatePercent').value) || 0;
    const surchargePct = parseFloat(document.getElementById('surchargePercent').value) || 0;

    let base = 0;
    switch (unit) {
        case 'per_sqft': base = (builtUp + land) * rate; break;
        case 'per_sqm': base = ((builtUp + land) / 10.764) * rate; break;
        case 'percent_arv': base = arv * (rate / 100); break;
        case 'percent_cv': base = cv * (rate / 100); break;
        case 'flat': base = rate; break;
    }
    const rebate = base * (rebatePct / 100);
    const surcharge = base * (surchargePct / 100);
    const net = base - rebate + surcharge;

    document.getElementById('baseTax').textContent = '₹' + base.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('rebateAmt').textContent = '-₹' + rebate.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('surchargeAmt').textContent = '+₹' + surcharge.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('netTax').textContent = '₹' + net.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}
calculateTax();

// Bootstrap validation
(function() {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
</body>
</html>