<?php
$colonies = $colonies ?? [];
$logs = $logs ?? [];
$summary = $summary ?? [];
$currentColony = $current_colony ?? null;
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="fas fa-cloud-showers-heavy text-info me-2"></i>Township Rainwater Harvesting & Groundwater Recharge Desk
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">ERP</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/colonies">Colonies</a></li>
                    <li class="breadcrumb-item active">Rainwater Harvesting (RWH) Desk</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/colonies<?= $currentColony ? '/' . $currentColony : '' ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Colony 360°
            </a>
            <button class="btn btn-info btn-sm text-white fw-semibold" data-bs-toggle="modal" data-bs-target="#newRwhModal">
                <i class="fas fa-plus-circle me-1"></i>Log RWH Recharge Pit Audit
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Today's Metric Ribbon -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0284c7;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Recharge Pits Audited</span>
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-water"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['total_pits_audited'] ?? 0) ?></h3>
                    <small class="text-muted">Today's inspections</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #10b981;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Recharge Capacity</span>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format((float)($summary['total_recharge_kl'] ?? 0), 1) ?> kL</h3>
                    <small class="text-muted">Groundwater recharge potential</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #8b5cf6;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Catchment Area</span>
                        <div class="rounded-circle bg-purple bg-opacity-10 p-2 text-purple d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-draw-polygon"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format((float)($summary['total_catchment_sqft'] ?? 0)) ?> sqft</h3>
                    <small class="text-muted">Rooftop & paved catchment</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #14b8a6;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Monsoon Ready Pits</span>
                        <div class="rounded-circle bg-teal bg-opacity-10 p-2 text-teal d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-check-double text-info"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['ready_pits'] ?? 0) ?></h3>
                    <small class="text-success fw-semibold">Desilted & filter cleared</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Colony Filter Toolbar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body p-3">
            <form method="GET" action="<?= BASE_URL ?>/admin/township-rwh" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-city text-muted"></i></span>
                        <select name="colony_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- All Township Colonies --</option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($currentColony == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <?php if ($currentColony): ?>
                        <a href="<?= BASE_URL ?>/admin/township-rwh" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times me-1"></i>Clear Filter
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-info me-2"></i>Rainwater Harvesting Pit Audits & CGWB Compliance</h5>
            <span class="badge bg-light text-muted border">Showing <?= count($logs) ?> logs</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Audit Date</th>
                            <th>Colony</th>
                            <th>Recharge Shaft / Pit</th>
                            <th>Recharge Type</th>
                            <th>Capacity (kL)</th>
                            <th>Filtration Media</th>
                            <th>Water Table Depth</th>
                            <th>Monsoon Score</th>
                            <th>CGWB / UP NOC</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-cloud-rain fa-2x mb-2 text-info opacity-50 d-block"></i>
                                    No rainwater harvesting records found for this selection.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        <?= date('d M Y', strtotime($log['audit_date'])) ?>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($log['colony_name'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border">
                                            <?= htmlspecialchars($log['pit_identifier']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['recharge_type']))) ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">
                                        <?= number_format((float)$log['recharge_capacity_kl'], 1) ?> kL
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($log['filtration_media_status'] === 'clean_gravel_sand') ? 'success' : 'warning' ?> bg-opacity-15 text-<?= ($log['filtration_media_status'] === 'clean_gravel_sand') ? 'success' : 'warning' ?>">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['filtration_media_status']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $log['water_table_depth_ft'] ? number_format((float)$log['water_table_depth_ft'], 1) . ' ft' : '--' ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($log['monsoon_readiness_score'] === 'optimal_ready') ? 'success' : 'danger' ?>">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['monsoon_readiness_score']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($log['up_groundwater_noc_status'] === 'compliant_valid') ? 'success' : 'info' ?> bg-opacity-15 text-<?= ($log['up_groundwater_noc_status'] === 'compliant_valid') ? 'success' : 'info' ?>">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['up_groundwater_noc_status']))) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/admin/township-rwh/<?= (int)$log['id'] ?>/print" target="_blank" class="btn btn-outline-info btn-sm" title="Print Certificate">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log RWH Audit -->
<div class="modal fade" id="newRwhModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/township-rwh/store">
                <?= \App\Core\Security\CSRF::generateTokenField() ?>
                <div class="modal-header bg-info bg-opacity-10">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-cloud-showers-heavy text-info me-2"></i>Log Rainwater Harvesting & Recharge Shaft Audit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Township Colony <span class="text-danger">*</span></label>
                            <select name="colony_id" class="form-select form-select-sm" required>
                                <option value="">-- Select Colony --</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($currentColony == $c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Audit Date <span class="text-danger">*</span></label>
                            <input type="date" name="audit_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pit / Shaft Identifier <span class="text-danger">*</span></label>
                            <input type="text" name="pit_identifier" class="form-control form-control-sm" placeholder="e.g. Central Park Dual Recharge Shaft #1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Recharge Structure Type</label>
                            <select name="recharge_type" class="form-select form-select-sm">
                                <option value="borewell_recharge_shaft">Borewell Recharge Shaft (Dual Bore with Filter)</option>
                                <option value="percolation_tank">Percolation Tank (Granular Sump)</option>
                                <option value="injection_well">Injection Well (Deep Stratum)</option>
                                <option value="catchment_swale">Catchment Bioswale (Green Avenue)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Catchment Area (sq.ft)</label>
                            <input type="number" name="catchment_area_sqft" class="form-control form-control-sm" step="0.01" placeholder="e.g. 25000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Recharge Capacity (kL / day)</label>
                            <input type="number" name="recharge_capacity_kl" class="form-control form-control-sm" step="0.1" placeholder="e.g. 150.0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Water Table Depth (ft)</label>
                            <input type="number" name="water_table_depth_ft" class="form-control form-control-sm" step="0.1" placeholder="e.g. 42.5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Filtration Media Status</label>
                            <select name="filtration_media_status" class="form-select form-select-sm">
                                <option value="clean_gravel_sand">Clean Gravel / Coarse Sand Layer</option>
                                <option value="needs_flushing">Filter Needs Backwashing / Flushing</option>
                                <option value="replaced_charcoal">Activated Charcoal Media Replaced</option>
                                <option value="clogged">Clogged (Urgent Replacement)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Monsoon Readiness Score</label>
                            <select name="monsoon_readiness_score" class="form-select form-select-sm">
                                <option value="optimal_ready">Optimal Ready (100% Prepared for Inflow)</option>
                                <option value="needs_cleaning">Needs Surface Trash Cleaning</option>
                                <option value="urgent_civil_repair">Urgent Silt Chamber Desilting Needed</option>
                                <option value="decommissioned">Decommissioned / Under Reconstruction</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">UP Groundwater Dept NOC Status</label>
                            <select name="up_groundwater_noc_status" class="form-select form-select-sm">
                                <option value="compliant_valid">Compliant & Valid (RERA RWH Approved)</option>
                                <option value="inspection_due">NOC Annual Inspection Due</option>
                                <option value="renewal_applied">Renewal Application Submitted</option>
                                <option value="exempt">Phase 1 Self-Certified</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="desilting_chambers_cleared" value="1" id="desiltCheck" checked>
                                <label class="form-check-label small fw-semibold" for="desiltCheck">Desilting Silt Traps Cleared</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Environmental / Civil Officer Name</label>
                            <input type="text" name="environmental_officer_name" class="form-control form-control-sm" placeholder="e.g. Er. Anoop Singh">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Officer Phone</label>
                            <input type="text" name="officer_contact" class="form-control form-control-sm" placeholder="e.g. +91 98765 12345">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Maintenance Actions Done</label>
                            <input type="text" name="maintenance_actions_done" class="form-control form-control-sm" placeholder="e.g. Desilted silt chamber, washed gravel filter, tested percolation rate">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Engineering Remarks</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Aquifer recharge velocity, surrounding slope gradient notes, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info btn-sm text-white fw-bold">Save RWH Audit</button>
                </div>
            </form>
        </div>
    </div>
</div>
