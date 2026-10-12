<?php
/**
 * Customer Construction Permit Application View
 */
$plots = $plots ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="d-flex align-items-center mb-3">
                <a href="/customer/construction-approvals" class="btn btn-outline-secondary btn-sm me-3">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </a>
                <h4 class="fw-bold mb-0">Apply for Township Construction Excavation Permit</h4>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form method="POST" action="/customer/construction/apply">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">

                        <h5 class="fw-bold text-primary mb-3">1. Select Plot & Booking</h5>
                        <div class="mb-4">
                            <label for="plot_select" class="form-label fw-semibold">Your Registered / Allotted Plot <span class="text-danger">*</span></label>
                            <select name="plot_id" id="plot_select" class="form-select" required>
                                <option value="">Select your township plot...</option>
                                <?php foreach ($plots as $p): ?>
                                    <option value="<?= (int)$p['plot_id'] ?>" data-booking="<?= (int)$p['booking_id'] ?>">
                                        Plot #<?= htmlspecialchars($p['plot_number']) ?> — <?= htmlspecialchars($p['colony_name']) ?> (<?= number_format((float)$p['area_sqft'], 0) ?> sq.ft, Facing: <?= htmlspecialchars($p['facing'] ?? 'N/A') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="booking_id" id="booking_id" value="">
                        </div>

                        <h5 class="fw-bold text-primary mb-3">2. Architectural Building Specifications</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="building_type" class="form-label fw-semibold">Building Category <span class="text-danger">*</span></label>
                                <select name="building_type" id="building_type" class="form-select" required>
                                    <option value="residential_g1">Residential (G+1 Floor)</option>
                                    <option value="residential_g2">Residential (G+2 Floors)</option>
                                    <option value="duplex_villa">Duplex Villa</option>
                                    <option value="commercial_shop">Commercial Shop / Office</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="ground_coverage_sqft" class="form-label fw-semibold">Ground Footprint (sq.ft) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="ground_coverage_sqft" name="ground_coverage_sqft" placeholder="e.g. 800" required>
                            </div>
                            <div class="col-md-4">
                                <label for="total_builtup_sqft" class="form-label fw-semibold">Total Built-up (sq.ft) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="total_builtup_sqft" name="total_builtup_sqft" placeholder="e.g. 1500" required>
                            </div>
                        </div>

                        <h5 class="fw-bold text-primary mb-3">3. Boundary Setbacks Compliance (in Feet)</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="front_setback_ft" class="form-label fw-semibold">Front Setback (ft) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control" id="front_setback_ft" name="front_setback_ft" value="10.0" required>
                                <div class="form-text small">Min 10.0 ft from road border.</div>
                            </div>
                            <div class="col-md-3">
                                <label for="rear_setback_ft" class="form-label fw-semibold">Rear Setback (ft) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" class="form-control" id="rear_setback_ft" name="rear_setback_ft" value="5.0" required>
                                <div class="form-text small">Min 5.0 ft for rear ventilation.</div>
                            </div>
                            <div class="col-md-3">
                                <label for="side_setback_left_ft" class="form-label fw-semibold">Left Side Setback (ft)</label>
                                <input type="number" step="0.1" class="form-control" id="side_setback_left_ft" name="side_setback_left_ft" value="3.0">
                                <div class="form-text small">Min 3.0 ft recommended.</div>
                            </div>
                            <div class="col-md-3">
                                <label for="side_setback_right_ft" class="form-label fw-semibold">Right Side Setback (ft)</label>
                                <input type="number" step="0.1" class="form-control" id="side_setback_right_ft" name="side_setback_right_ft" value="3.0">
                                <div class="form-text small">Min 3.0 ft recommended.</div>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="rainwater_harvesting_pit" name="rainwater_harvesting_pit" value="1" checked>
                            <label class="form-check-label fw-semibold" for="rainwater_harvesting_pit">
                                Mandatory Rainwater Harvesting (RWH) Recharge Pit included in drawing
                            </label>
                            <div class="form-text">Compulsory under UP Ground Water Directorate & GDA township bylaws.</div>
                        </div>

                        <h5 class="fw-bold text-primary mb-3">4. Architect / Structural Consultant (Optional)</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="architect_name" class="form-label">Architect / Engineer Name</label>
                                <input type="text" class="form-control" id="architect_name" name="architect_name" placeholder="e.g. Ar. Sunil Verma">
                            </div>
                            <div class="col-md-6">
                                <label for="architect_council_reg" class="form-label">Council of Architecture Reg. / COA No.</label>
                                <input type="text" class="form-control" id="architect_council_reg" name="architect_council_reg" placeholder="e.g. CA/2021/11823">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/customer/construction-approvals" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-paper-plane me-1"></i>Submit Application for Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('plot_select').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    document.getElementById('booking_id').value = opt.getAttribute('data-booking') || '';
});
</script>
