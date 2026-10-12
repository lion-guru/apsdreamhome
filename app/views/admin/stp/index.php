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
                <i class="fas fa-vial text-primary me-2"></i>Township Central STP Effluent & UPPCB Compliance Desk
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">ERP</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/colonies">Colonies</a></li>
                    <li class="breadcrumb-item active">STP Effluent Desk</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/colonies<?= $currentColony ? '/' . $currentColony : '' ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Colony 360°
            </a>
            <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newStpModal">
                <i class="fas fa-plus-circle me-1"></i>Log STP Effluent Run
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
                        <span class="text-muted small fw-semibold text-uppercase">Raw Inflow</span>
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-water"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['total_inflow_kl'] ?? 0, 1) ?> <span class="fs-6 fw-normal text-muted">kL</span></h3>
                    <small class="text-muted">Total Sewage Received</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #10b981;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Treated Water</span>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-recycle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-success"><?= number_format($summary['total_treated_kl'] ?? 0, 1) ?> <span class="fs-6 fw-normal text-muted">kL</span></h3>
                    <small class="text-muted">Recycled for Horticulture</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #6366f1;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Avg Outlet BOD</span>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-microscope"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['avg_outlet_bod'] ?? 0, 1) ?> <span class="fs-6 fw-normal text-muted">mg/L</span></h3>
                    <small class="text-success fw-semibold"><i class="fas fa-check me-1"></i>UPPCB Standard &lt; 10</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #14b8a6;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Compliant Plants</span>
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-certificate"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['compliant_runs'] ?? 0) ?> <span class="fs-6 fw-normal text-muted">/ <?= number_format($summary['total_runs'] ?? 0) ?></span></h3>
                    <small class="text-muted">100% UPPCB Discharge Norms</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body p-3">
            <form method="GET" action="<?= BASE_URL ?>/admin/township-stp" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <label class="input-group-text bg-light border-0"><i class="fas fa-city text-primary"></i></label>
                        <select name="colony_id" class="form-select border-0 bg-light" onchange="this.form.submit()">
                            <option value="">-- All Township Colonies --</option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($currentColony == $c['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?>
                                </option>
                            <?php endEmpty: endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php if ($currentColony): ?>
                        <a href="<?= BASE_URL ?>/admin/township-stp" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times me-1"></i>Clear Filter
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-list text-primary me-2"></i>STP Effluent Pumping & Laboratory Logs
            </h5>
            <span class="badge bg-light text-dark border"><?= count($logs) ?> Records Loaded</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Colony & Plant Code</th>
                            <th>Inflow / Treated (kL)</th>
                            <th>Effluent Quality (BOD / COD / TSS)</th>
                            <th>pH & Chlorine</th>
                            <th>Reuse Destination</th>
                            <th>UPPCB Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-vial fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-0">No STP effluent treatment logs recorded yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $row): ?>
                                <tr>
                                    <td class="ps-3 text-nowrap">
                                        <div class="fw-bold text-dark"><?= date('d M Y', strtotime($row['log_date'])) ?></div>
                                        <small class="text-muted"><i class="fas fa-fan me-1"></i><?= number_format($row['aeration_blower_hours'], 1) ?>h Blowers</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['colony_name'] ?? 'Colony #' . $row['colony_id']) ?></div>
                                        <span class="badge bg-light text-dark border"><?= htmlspecialchars($row['stp_plant_code']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= number_format($row['raw_sewage_inflow_kl'], 1) ?> kL In</div>
                                        <div class="text-success small fw-bold"><?= number_format($row['treated_water_output_kl'], 1) ?> kL Out</div>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <strong>BOD:</strong> <?= $row['outlet_bod_mg_l'] !== null ? number_format($row['outlet_bod_mg_l'], 1) . ' mg/L' : 'N/A' ?>
                                            <?php if ($row['inlet_bod_mg_l']): ?>
                                                <span class="text-muted">(In: <?= number_format($row['inlet_bod_mg_l'], 0) ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="small text-muted">
                                            <strong>COD:</strong> <?= $row['outlet_cod_mg_l'] !== null ? number_format($row['outlet_cod_mg_l'], 1) : 'N/A' ?> | 
                                            <strong>TSS:</strong> <?= $row['outlet_tss_mg_l'] !== null ? number_format($row['outlet_tss_mg_l'], 1) : 'N/A' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small"><strong>pH:</strong> <?= $row['outlet_ph'] !== null ? number_format($row['outlet_ph'], 2) : 'N/A' ?></div>
                                        <div class="small text-muted"><strong>Cl:</strong> <?= $row['chlorine_dosing_ppm'] !== null ? number_format($row['chlorine_dosing_ppm'], 2) . ' ppm' : 'N/A' ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <?= ucwords(str_replace('_', ' ', $row['water_reuse_purpose'])) ?>
                                        </span>
                                        <?php if ((float)$row['sludge_cake_disposed_kg'] > 0): ?>
                                            <div class="text-muted small"><?= number_format($row['sludge_cake_disposed_kg'], 1) ?> kg Sludge</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match ($row['uppcb_compliance_status']) {
                                            'fully_compliant' => 'bg-success',
                                            'minor_deviation' => 'bg-warning text-dark',
                                            'critical_violation' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucwords(str_replace('_', ' ', $row['uppcb_compliance_status'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 text-nowrap">
                                        <a href="<?= BASE_URL ?>/admin/township-stp/<?= $row['id'] ?>/print" target="_blank" class="btn btn-sm btn-outline-primary" title="Print Official UPPCB Compliance Certificate">
                                            <i class="fas fa-print me-1"></i>Certificate
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

<!-- Modal: New STP Effluent Run Log -->
<div class="modal fade" id="newStpModal" tabindex="-1" aria-labelledby="newStpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="newStpModalLabel">
                    <i class="fas fa-vial me-2"></i>Record Township STP Effluent Run & Lab Test
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/admin/township-stp/store" method="POST">
                <?= \App\Core\Security\CSRF::createTokenField() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Township Colony <span class="text-danger">*</span></label>
                            <select name="colony_id" class="form-select" required>
                                <option value="">-- Select Colony --</option>
                                <?php foreach ($colonies as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= ($currentColony == $c['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Log Date <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">STP Plant Code <span class="text-danger">*</span></label>
                            <input type="text" name="stp_plant_code" class="form-control" placeholder="STP-PLANT-01" value="STP-PLANT-01 (MBBR)" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Raw Sewage Inflow (kL)</label>
                            <input type="number" step="0.1" name="raw_sewage_inflow_kl" class="form-control" placeholder="e.g. 85.0" value="75.0" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Treated Water Output (kL)</label>
                            <input type="number" step="0.1" name="treated_water_output_kl" class="form-control" placeholder="e.g. 70.0" value="68.0" required>
                        </div>

                        <div class="col-12">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mt-2">
                                <i class="fas fa-flask text-primary me-2"></i>Laboratory Effluent Quality Parameters (UPPCB Benchmarks)
                            </h6>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Inlet Raw BOD (mg/L)</label>
                            <input type="number" step="0.1" name="inlet_bod_mg_l" class="form-control" placeholder="250.0" value="220.0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Outlet Treated BOD (mg/L) <span class="text-success">&lt;10</span></label>
                            <input type="number" step="0.1" name="outlet_bod_mg_l" class="form-control" placeholder="e.g. 8.2" value="7.5" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Inlet Raw COD (mg/L)</label>
                            <input type="number" step="0.1" name="inlet_cod_mg_l" class="form-control" placeholder="450.0" value="400.0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Outlet Treated COD (mg/L) <span class="text-success">&lt;50</span></label>
                            <input type="number" step="0.1" name="outlet_cod_mg_l" class="form-control" placeholder="e.g. 32.0" value="28.0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Outlet TSS (mg/L) <span class="text-success">&lt;20</span></label>
                            <input type="number" step="0.1" name="outlet_tss_mg_l" class="form-control" placeholder="e.g. 11.0" value="9.5">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Outlet pH (6.5 - 8.5)</label>
                            <input type="number" step="0.01" name="outlet_ph" class="form-control" placeholder="e.g. 7.35" value="7.40">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Residual Chlorine (ppm)</label>
                            <input type="number" step="0.1" name="chlorine_dosing_ppm" class="form-control" placeholder="e.g. 1.2" value="1.0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Blower Hours (hrs)</label>
                            <input type="number" step="0.1" name="aeration_blower_hours" class="form-control" placeholder="18.0" value="20.0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Treated Water Recycling Destination</label>
                            <select name="water_reuse_purpose" class="form-select">
                                <option value="gardening_horticulture" selected>Park & Avenue Gardening (Horticulture)</option>
                                <option value="toilet_dual_flush">Township Dual-Plumbing Toilet Flush</option>
                                <option value="road_washing_dust_suppression">Road Washing & Dust Suppression</option>
                                <option value="groundwater_recharge">Groundwater Recharge Filter Shafts</option>
                                <option value="overspill_drain">Nagar Nigam Storm Drain Discharge</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Dewatered Sludge Cake Disposed (kg)</label>
                            <input type="number" step="0.1" name="sludge_cake_disposed_kg" class="form-control" placeholder="e.g. 45.0" value="35.0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">STP Plant Operator Name</label>
                            <input type="text" name="operator_name" class="form-control" placeholder="Sunil Kumar">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Operator Contact Number</label>
                            <input type="text" name="operator_phone" class="form-control" placeholder="+91 9876543210">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Engineering Remarks & Chemical Dosage</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="Poly-electrolyte dosage added to secondary clarifier, blowers operating at optimal DO 2.5 mg/L."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                        <i class="fas fa-save me-1"></i>Save STP Effluent Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
