<?php
$colony   = $colony   ?? [];
$existing = $existing ?? null;
$base     = defined('BASE_URL') ? BASE_URL : '';
$e        = $existing ?? [];   // short alias for pre-fill
$colonyId = (int)($colony['id'] ?? 0);

// Helper: get existing value or default
$val = function(string $key, $default = 0) use ($e) {
    return $e[$key] ?? $default;
};
?>
<div class="container-fluid py-4" class="style-12596">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0">
            <i class="fas fa-drafting-compass me-2"></i>
            Land Costing: <?= htmlspecialchars($colony['name'] ?? '') ?>
        </h4>
        <a href="<?= $base ?>/admin/colony-costing" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
    </div>

    <?php if ($existing): ?>
        <div class="alert alert-info py-2 mb-3">
            <i class="fas fa-info-circle me-2"></i>
            Existing costing v<?= (int)$existing['version'] ?> found (<?= $existing['is_approved'] ? 'âœ… Approved' : 'â�³ Pending' ?>).
            Saving will create version <?= (int)$existing['version'] + 1 ?>.
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- â”€â”€ LEFT: Input Form â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
        <div class="col-lg-7">
            <form id="costingForm" method="POST" action="<?= $base ?>/admin/colony-costing/store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="colony_id" value="<?= $colonyId ?>">

                <!-- Costing Label -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-dark text-white py-2">
                        <i class="fas fa-tag me-2"></i>Costing Version Label
                    </div>
                    <div class="card-body">
                        <label class="form-label" for="costing_label">Label / Description</label>
                        <input type="text" id="costing_label" name="costing_label" class="form-control"
                               value="<?= htmlspecialchars($val('costing_label', 'Initial Costing')) ?>"
                               placeholder="e.g. Phase 1 â€” July 2026 Estimate">
                    </div>
                </div>

                <!-- Section 1: Land Acquisition -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white py-2">
                        <i class="fas fa-map me-2"></i>Section 1 â€” Land Acquisition
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="total_land_sqft">Total Land (SqFt) <span class="text-danger">*</span></label>
                                <input type="number" id="total_land_sqft" name="total_land_sqft"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('total_land_sqft', 0) ?>" required>
                                <small class="text-muted">1 Acre = 43,560 SqFt</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="land_purchase_rate">Purchase Rate (â‚¹/SqFt) <span class="text-danger">*</span></label>
                                <input type="number" id="land_purchase_rate" name="land_purchase_rate"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('land_purchase_rate', 0) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="land_registry_cost">Registry / Stamp Duty (â‚¹ Total)</label>
                                <input type="number" id="land_registry_cost" name="land_registry_cost"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('land_registry_cost', 0) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Wastage Deductions -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-warning text-dark py-2">
                        <i class="fas fa-slash me-2"></i>Section 2 â€” Wastage Deductions (% of Total Land)
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label" for="road_wastage_pct">Roads (%)</label>
                                <input type="number" id="road_wastage_pct" name="road_wastage_pct"
                                       class="form-control calc-trigger" min="0" max="50" step="0.01"
                                       value="<?= $val('road_wastage_pct', 15) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="drainage_wastage_pct">Drainage / Nali (%)</label>
                                <input type="number" id="drainage_wastage_pct" name="drainage_wastage_pct"
                                       class="form-control calc-trigger" min="0" max="30" step="0.01"
                                       value="<?= $val('drainage_wastage_pct', 5) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="park_wastage_pct">Park / Green (%)</label>
                                <input type="number" id="park_wastage_pct" name="park_wastage_pct"
                                       class="form-control calc-trigger" min="0" max="30" step="0.01"
                                       value="<?= $val('park_wastage_pct', 5) ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="other_wastage_pct">Other (%)</label>
                                <input type="number" id="other_wastage_pct" name="other_wastage_pct"
                                       class="form-control calc-trigger" min="0" max="20" step="0.01"
                                       value="<?= $val('other_wastage_pct', 0) ?>">
                            </div>
                        </div>
                        <div id="wastage_summary" class="mt-3 p-3 bg-light rounded d-none">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="small text-muted">Total Wastage</div>
                                    <div class="fw-bold text-danger" id="r_total_waste_pct">â€”%</div>
                                </div>
                                <div class="col-4">
                                    <div class="small text-muted">Wasted SqFt</div>
                                    <div class="fw-bold text-danger" id="r_wasted_sqft">â€”</div>
                                </div>
                                <div class="col-4">
                                    <div class="small text-muted">Net Sellable SqFt</div>
                                    <div class="fw-bold text-success" id="r_net_sellable">â€”</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Development Costs -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-success text-white py-2">
                        <i class="fas fa-hard-hat me-2"></i>Section 3 â€” Development Costs (â‚¹/SqFt of Sellable Area)
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="road_dev_cost_sqft">Road Construction (â‚¹/SqFt)</label>
                                <input type="number" id="road_dev_cost_sqft" name="road_dev_cost_sqft"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('road_dev_cost_sqft', 0) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="drainage_dev_cost_sqft">Drainage / Nali (â‚¹/SqFt)</label>
                                <input type="number" id="drainage_dev_cost_sqft" name="drainage_dev_cost_sqft"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('drainage_dev_cost_sqft', 0) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="electricity_cost_sqft">Electricity (â‚¹/SqFt)</label>
                                <input type="number" id="electricity_cost_sqft" name="electricity_cost_sqft"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('electricity_cost_sqft', 0) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="water_pipeline_cost_sqft">Water Pipeline (â‚¹/SqFt)</label>
                                <input type="number" id="water_pipeline_cost_sqft" name="water_pipeline_cost_sqft"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('water_pipeline_cost_sqft', 0) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="boundary_wall_cost_sqft">Boundary Wall (â‚¹/SqFt)</label>
                                <input type="number" id="boundary_wall_cost_sqft" name="boundary_wall_cost_sqft"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('boundary_wall_cost_sqft', 0) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="other_dev_cost_sqft">Other Dev. (â‚¹/SqFt)</label>
                                <input type="number" id="other_dev_cost_sqft" name="other_dev_cost_sqft"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('other_dev_cost_sqft', 0) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Overheads & Pricing -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white py-2">
                        <i class="fas fa-percent me-2"></i>Section 4 â€” Overheads, Commission & Profit
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="legal_approval_cost">Legal / RERA / NOC Cost (â‚¹ Total)</label>
                                <input type="number" id="legal_approval_cost" name="legal_approval_cost"
                                       class="form-control calc-trigger" min="0" step="0.01"
                                       value="<?= $val('legal_approval_cost', 0) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="admin_overhead_pct">Admin Overhead (%)</label>
                                <input type="number" id="admin_overhead_pct" name="admin_overhead_pct"
                                       class="form-control calc-trigger" min="0" max="30" step="0.01"
                                       value="<?= $val('admin_overhead_pct', 5) ?>">
                                <small class="text-muted">Applied on pre-overhead cost</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="marketing_commission_pct">MLM Commission Budget (%)</label>
                                <input type="number" id="marketing_commission_pct" name="marketing_commission_pct"
                                       class="form-control calc-trigger" min="0" max="30" step="0.01"
                                       value="<?= $val('marketing_commission_pct', 20) ?>">
                                <small class="text-muted">% of final selling price reserved for agent commissions</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="target_profit_pct">Target Profit Margin (%)</label>
                                <input type="number" id="target_profit_pct" name="target_profit_pct"
                                       class="form-control calc-trigger" min="0" max="80" step="0.01"
                                       value="<?= $val('target_profit_pct', 20) ?>">
                                <small class="text-muted">% of final selling price as company profit</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Final Price Override -->
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-success text-white py-2">
                        <i class="fas fa-tag me-2"></i>Final Approved Selling Price
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label" for="final_price_sqft">Final Price (â‚¹/SqFt)</label>
                                <input type="number" id="final_price_sqft" name="final_price_sqft"
                                       class="form-control form-control-lg" min="0" step="0.01"
                                       value="<?= $val('final_price_sqft', 0) ?>"
                                       placeholder="Leave 0 to use suggested price">
                                <small class="text-muted">Leave 0 = use system-suggested price. Override here for custom pricing.</small>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <div class="small text-muted">Suggested Price</div>
                                    <div class="h4 text-primary" id="r_suggested_price">â€” â‚¹/SqFt</div>
                                    <div class="small text-muted">Landing Cost: <span id="r_landing_cost">â€”</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-save me-2"></i>Save Land Costing
                </button>
            </form>
        </div>

        <!-- â”€â”€ RIGHT: Live Result Panel â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ -->
        <div class="col-lg-5">
            <div class="card shadow-sm sticky-top" class="style-54247">
                <div class="card-header bg-dark text-white py-2">
                    <i class="fas fa-chart-pie me-2"></i>Live Cost Breakdown
                </div>
                <div class="card-body" id="live_results">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-calculator fa-3x mb-3 d-block opacity-25"></i>
                        Fill in the form to see live calculations
                    </div>
                </div>
            </div>
        </div>
    </div><!-- /row -->
</div>

<script>
(function () {
    var BASE_URL = '<?= $base ?>';
    var calcTimer = null;

    function getFormData() {
        var data = {};
        document.querySelectorAll('.calc-trigger').forEach(function (el) {
            data[el.name] = parseFloat(el.value) || 0;
        });
        return data;
    }

    function fmt(n) {
        return 'â‚¹' + parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtNum(n) {
        return parseFloat(n).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function updateResults(data) {
        if (!data.success) {
            document.getElementById('live_results').innerHTML =
                '<div class="alert alert-danger">' + (data.error || 'Calculation error') + '</div>';
            return;
        }

        // Update summary badges
        var ws = document.getElementById('wastage_summary');
        if (ws) ws.classList.remove('d-none');
        var el = function(id) { return document.getElementById(id); };
        if (el('r_total_waste_pct'))  el('r_total_waste_pct').textContent  = data.wastage.total_pct + '%';
        if (el('r_wasted_sqft'))      el('r_wasted_sqft').textContent      = fmtNum(data.wastage.total_sqft) + ' SqFt';
        if (el('r_net_sellable'))     el('r_net_sellable').textContent     = fmtNum(data.net_sellable_sqft) + ' SqFt';
        if (el('r_suggested_price'))  el('r_suggested_price').textContent  = fmt(data.suggested_price_per_sqft) + '/SqFt';
        if (el('r_landing_cost'))     el('r_landing_cost').textContent     = fmt(data.landing_cost_per_sqft) + '/SqFt';

        // Detailed panel
        document.getElementById('live_results').innerHTML = [
            '<table class="table table-sm table-borderless mb-0">',
            '<tbody>',
            '<tr class="table-light"><th colspan="2">Land</th></tr>',
            '<tr><td>Total Land</td><td class="text-end">' + fmtNum(data.total_land_sqft) + ' SqFt</td></tr>',
            '<tr><td>Purchase Rate</td><td class="text-end">' + fmt(data.land_purchase_rate) + '/SqFt</td></tr>',
            '<tr class="text-danger"><td>Total Wastage</td><td class="text-end">âˆ’' + fmtNum(data.wastage.total_sqft) + ' SqFt (' + data.wastage.total_pct + '%)</td></tr>',
            '<tr class="fw-bold text-success"><td>Net Sellable</td><td class="text-end">' + fmtNum(data.net_sellable_sqft) + ' SqFt</td></tr>',
            '<tr class="table-light"><th colspan="2">Cost Per Sellable SqFt</th></tr>',
            '<tr><td>Land Cost/SqFt</td><td class="text-end">' + fmt(data.land_cost_per_sqft) + '</td></tr>',
            '<tr><td>Development/SqFt</td><td class="text-end">' + fmt(data.dev_cost_per_sqft) + '</td></tr>',
            '<tr><td>Legal/SqFt</td><td class="text-end">' + fmt(data.legal_cost_per_sqft) + '</td></tr>',
            '<tr><td>Admin Overhead/SqFt</td><td class="text-end">' + fmt(data.admin_overhead_per_sqft) + '</td></tr>',
            '<tr class="fw-bold border-top"><td>Landing Cost/SqFt</td><td class="text-end text-primary">' + fmt(data.landing_cost_per_sqft) + '</td></tr>',
            '<tr class="table-light"><th colspan="2">Pricing</th></tr>',
            '<tr><td>Marketing Commission</td><td class="text-end">' + data.marketing_commission_pct + '%</td></tr>',
            '<tr><td>Profit Margin</td><td class="text-end">' + data.target_profit_pct + '%</td></tr>',
            '<tr class="fw-bold fs-5 table-success"><td>Suggested Price</td><td class="text-end text-success">' + fmt(data.suggested_price_per_sqft) + '</td></tr>',
            '<tr class="table-light"><th colspan="2">Project Totals</th></tr>',
            '<tr><td>Total Acquisition Cost</td><td class="text-end">' + fmt(data.total_land_acquisition_cost) + '</td></tr>',
            '<tr><td>Total Landing Cost</td><td class="text-end">' + fmt(data.total_landing_cost) + '</td></tr>',
            '<tr><td>Total Revenue (Projected)</td><td class="text-end">' + fmt(data.total_revenue) + '</td></tr>',
            '<tr><td>Marketing Budget</td><td class="text-end">' + fmt(data.total_marketing_budget) + '</td></tr>',
            '<tr class="fw-bold text-success border-top"><td>Company Profit</td><td class="text-end">' + fmt(data.total_profit) + '</td></tr>',
            '</tbody></table>',
        ].join('');
    }

    function doCalc() {
        var data = getFormData();
        fetch(BASE_URL + '/admin/colony-costing/calculate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(updateResults)
        .catch(function(err) { console.error('Calc error', err); });
    }

    document.querySelectorAll('.calc-trigger').forEach(function (el) {
        el.addEventListener('input', function () {
            clearTimeout(calcTimer);
            calcTimer = setTimeout(doCalc, 400);
        });
    });
})();
</script>
