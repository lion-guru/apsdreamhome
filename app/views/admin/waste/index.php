<?php
/**
 * Admin Township Solid Waste Management & Sanitation Collection Desk View
 */
$logs = $logs ?? [];
$summary = $summary ?? [];
$colonies = $colonies ?? [];
$current_colony = $current_colony ?? null;
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-recycle text-success me-2"></i>Township Solid Waste &amp; Sanitation Desk</h3>
            <p class="text-muted small mb-0">Door-to-door garbage collection, wet/dry waste segregation, society compost pits &amp; drain desilting</p>
        </div>
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#newWasteModal">
            <i class="fas fa-truck-loading me-1"></i> Log Waste Collection Run
        </button>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Waste Processed</div>
                <div class="fs-4 fw-bold text-success mt-1"><?= number_format((float)($summary['total_waste_kg'] ?? 0), 1) ?> <span class="fs-6 text-muted">kg</span></div>
                <div class="text-muted small mt-1"><?= number_format((float)($summary['total_waste_kg'] ?? 0) / 1000, 2) ?> Metric Tonnes</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Wet vs Dry Segregation</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= number_format((float)($summary['total_wet_kg'] ?? 0), 0) ?> <span class="fs-6 text-muted">Wet</span> / <?= number_format((float)($summary['total_dry_kg'] ?? 0), 0) ?> <span class="fs-6 text-muted">Dry</span></div>
                <div class="text-muted small mt-1">Source segregated by residents</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Compost Pit Diverted</div>
                <div class="fs-4 fw-bold text-info mt-1"><?= number_format((float)($summary['total_compost_kg'] ?? 0), 1) ?> <span class="fs-6 text-muted">kg</span></div>
                <div class="text-muted small mt-1">Converted to organic garden manure</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Tractor Disposal Trips</div>
                <div class="fs-4 fw-bold text-warning mt-1"><?= (int)($summary['total_tractor_trips'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Avg <?= (int)($summary['avg_plots_serviced'] ?? 0) ?> plots/houses per shift</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="/admin/township-waste" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">Filter by Township Colony</label>
                    <select name="colony_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- All Colonies --</option>
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $current_colony == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end">
                    <a href="/admin/township-waste" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0"><i class="fas fa-trash-alt text-success me-2"></i>Daily Door-to-Door Waste Collection &amp; Disposal Operations</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date &amp; Shift</th>
                        <th>Colony</th>
                        <th>Plots Serviced</th>
                        <th>Waste Weight (Wet / Dry)</th>
                        <th>Total Waste</th>
                        <th>Compost Diverted</th>
                        <th>Destination</th>
                        <th>Drain Clean Status</th>
                        <th>Sanitation Supervisor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-recycle fs-2 d-block mb-2 text-success opacity-50"></i>
                                No waste collection runs logged yet. Click "Log Waste Collection Run" to track daily sanitation.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($l['collection_date']) ?></span><br>
                                    <span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $l['shift']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= htmlspecialchars($l['colony_name'] ?? 'Colony #' . $l['colony_id']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= (int)$l['plots_serviced_count'] ?></span> plots
                                </td>
                                <td>
                                    <span class="small text-muted"><span class="text-dark fw-semibold"><?= number_format((float)$l['wet_waste_kg'], 1) ?> kg</span> Wet / <span class="text-dark fw-semibold"><?= number_format((float)$l['dry_waste_kg'], 1) ?> kg</span> Dry</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success fs-6"><?= number_format((float)$l['total_waste_kg'], 1) ?> kg</span><br>
                                    <span class="small text-muted"><?= (int)$l['tractor_trips_count'] ?> tractor trip(s)</span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark"><?= number_format((float)$l['compost_pit_diverted_kg'], 1) ?> kg</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $l['disposal_destination']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $l['drain_cleaning_status'] === 'completed' ? 'bg-success' : 'bg-warning text-dark' ?> text-capitalize"><?= str_replace('_', ' ', $l['drain_cleaning_status']) ?></span>
                                </td>
                                <td>
                                    <span class="small fw-semibold"><?= htmlspecialchars($l['sanitation_supervisor']) ?></span><br>
                                    <span class="text-muted small"><?= htmlspecialchars($l['supervisor_contact'] ?? '') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: New Waste Log -->
<div class="modal fade" id="newWasteModal" tabindex="-1" aria-labelledby="newWasteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/township-waste/store" class="modal-content border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="newWasteModalLabel"><i class="fas fa-recycle me-2"></i>Record Daily Sanitation &amp; Waste Collection</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Township Colony *</label>
                        <select name="colony_id" class="form-select" required>
                            <option value="">-- Select Colony --</option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Collection Date</label>
                        <input type="date" name="collection_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Shift</label>
                        <select name="shift" class="form-select">
                            <option value="morning_sweep">Morning Sweep (07:00)</option>
                            <option value="evening_sweep">Evening Sweep (16:00)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Plots Serviced *</label>
                        <input type="number" name="plots_serviced_count" class="form-control" value="85" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Wet Waste (kg) *</label>
                        <input type="number" step="0.5" name="wet_waste_kg" class="form-control" value="120.0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Dry Waste (kg) *</label>
                        <input type="number" step="0.5" name="dry_waste_kg" class="form-control" value="65.0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Compost Pit Diverted (kg)</label>
                        <input type="number" step="0.5" name="compost_pit_diverted_kg" class="form-control" value="80.0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Disposal Destination</label>
                        <select name="disposal_destination" class="form-select">
                            <option value="society_compost_pit">Society Vermicompost Pit</option>
                            <option value="nagar_nigam_designated_dump">Nagar Nigam Dump Yard</option>
                            <option value="recycling_vendor">Recycling Vendor Pickup</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Tractor Trolley Trips</label>
                        <input type="number" name="tractor_trips_count" class="form-control" value="1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Sanitary Workforce Headcount</label>
                        <input type="number" name="sanitation_headcount" class="form-control" value="4">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Storm Drain Desilting Status</label>
                        <select name="drain_cleaning_status" class="form-select">
                            <option value="completed">Completed (Drains Clean &amp; Free Flowing)</option>
                            <option value="in_progress">Desilting In Progress</option>
                            <option value="scheduled">Scheduled Routine Clean</option>
                            <option value="clogged_alert">Clogging Alert (Requires Jetting)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Sanitation Supervisor *</label>
                        <input type="text" name="sanitation_supervisor" class="form-control" value="Rajesh Rawat" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Supervisor Contact</label>
                        <input type="text" name="supervisor_contact" class="form-control" value="+91 9839443322">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Sanitation Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Sector A &amp; B road sweeping finished, dustbins sanitized, compost pit aerated."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-1"></i> Save Sanitation Log</button>
            </div>
        </form>
    </div>
</div>
