<?php
$colonies      = $colonies ?? [];
$logs          = $logs ?? [];
$summary       = $summary ?? [];
$currentColony = $current_colony ?? null;
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">
                <i class="fas fa-fire-extinguisher text-danger me-2"></i>Township Fire Hydrant Network &amp; UP Fire NOC Desk
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">ERP</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/colonies">Colonies</a></li>
                    <li class="breadcrumb-item active">Fire Safety Desk</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/colonies<?= $currentColony ? '/' . $currentColony : '' ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Colony 360°
            </a>
            <button class="btn btn-danger btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#newFireModal">
                <i class="fas fa-plus-circle me-1"></i>Log Fire Safety Inspection
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

    <!-- Colony Filter -->
    <?php if (!empty($colonies)): ?>
    <div class="mb-3">
        <form method="GET" class="d-flex align-items-center gap-2 flex-wrap">
            <label class="fw-semibold text-muted small text-uppercase">Filter by Colony:</label>
            <select name="colony_id" class="form-select form-select-sm" style="max-width:280px;" onchange="this.form.submit()">
                <option value="">— All Colonies —</option>
                <?php foreach ($colonies as $col): ?>
                    <option value="<?= (int)$col['id'] ?>" <?= $currentColony == $col['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($col['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <?php endif; ?>

    <!-- Today's Metric Ribbon -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #dc2626;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Inspections Today</span>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-fire"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($summary['total_inspections'] ?? 0) ?></h3>
                    <small class="text-muted">Fire Zones Audited</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #f59e0b;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Avg Line Pressure</span>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['avg_line_pressure'] ?? 0.0, 1) ?> <span class="fs-6 fw-normal text-muted">Bar</span></h3>
                    <small class="text-muted">Static Network Pressure</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #16a34a;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Valves Tested</span>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-faucet"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">
                        <?= (int)($summary['total_valves_healthy'] ?? 0) ?><span class="fs-6 fw-normal text-muted"> / <?= (int)($summary['total_valves_tested'] ?? 0) ?></span>
                    </h3>
                    <small class="text-muted">Healthy / Tested Hydrant Valves</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #0284c7;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">NOC Compliant</span>
                        <div class="rounded-circle bg-info bg-opacity-10 p-2 text-info d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-certificate"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= (int)($summary['compliant_zones'] ?? 0) ?></h3>
                    <small class="text-muted">UP Fire NOC Valid Zones</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Inspection Logs Table -->
    <div class="card border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold mb-0"><i class="fas fa-list text-danger me-2"></i>Fire Safety Inspection Logs</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($logs)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-fire-extinguisher fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No fire safety inspections recorded yet</h5>
                    <p class="text-muted small">Log your first hydrant network audit to begin tracking compliance.</p>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#newFireModal">
                        <i class="fas fa-plus me-1"></i>Log First Inspection
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="fireLogsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Colony</th>
                                <th>Fire Zone</th>
                                <th>Pressure (Bar)</th>
                                <th>Jockey Pump</th>
                                <th>Electric Pump</th>
                                <th>Valves H/T</th>
                                <th>NOC Status</th>
                                <th>Fire Officer</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <?php
                                $nocClass = 'text-success';
                                $nocLabel = 'Valid & Compliant';
                                switch ($log['up_fire_noc_status'] ?? '') {
                                    case 'renewal_due_30d':  $nocClass='text-warning'; $nocLabel='Renewal Due 30d'; break;
                                    case 'renewal_applied':  $nocClass='text-info';    $nocLabel='Renewal Applied';  break;
                                    case 'provisional_noc':  $nocClass='text-primary'; $nocLabel='Provisional NOC';  break;
                                    case 'expired':          $nocClass='text-danger';  $nocLabel='NOC Expired';      break;
                                }
                                $jpClass = match($log['jockey_pump_status'] ?? '') {
                                    'auto_standby'         => 'text-success',
                                    'running_pressurizing' => 'text-warning',
                                    'manual_mode'          => 'text-info',
                                    default                => 'text-danger',
                                };
                                $epClass = match($log['main_electric_pump_status'] ?? '') {
                                    'operational_ready' => 'text-success',
                                    'service_due'       => 'text-warning',
                                    default             => 'text-danger',
                                };
                            ?>
                            <tr>
                                <td><strong><?= date('d M Y', strtotime($log['inspection_date'])) ?></strong></td>
                                <td><?= htmlspecialchars($log['colony_name'] ?? '') ?></td>
                                <td><code><?= htmlspecialchars($log['fire_zone_identifier']) ?></code></td>
                                <td><span class="badge bg-info bg-opacity-15 text-info"><?= number_format((float)$log['static_line_pressure_bar'], 1) ?> Bar</span></td>
                                <td><small class="<?= $jpClass ?>"><?= ucwords(str_replace('_', ' ', $log['jockey_pump_status'] ?? '')) ?></small></td>
                                <td><small class="<?= $epClass ?>"><?= ucwords(str_replace('_', ' ', $log['main_electric_pump_status'] ?? '')) ?></small></td>
                                <td>
                                    <span class="badge bg-success bg-opacity-15 text-success"><?= (int)$log['hydrant_valves_healthy'] ?></span>
                                    <span class="text-muted">/ <?= (int)$log['hydrant_valves_tested'] ?></span>
                                </td>
                                <td><small class="fw-semibold <?= $nocClass ?>"><?= $nocLabel ?></small></td>
                                <td>
                                    <small>
                                        <?= htmlspecialchars($log['fire_officer_name'] ?? '—') ?><br>
                                        <span class="text-muted"><?= htmlspecialchars($log['fire_officer_contact'] ?? '') ?></span>
                                    </small>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/admin/township-fire-safety/<?= (int)$log['id'] ?>/print"
                                       class="btn btn-outline-danger btn-sm" target="_blank" title="Print NOC Certificate">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ====== Log Fire Safety Inspection Modal ====== -->
<div class="modal fade" id="newFireModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-fire-extinguisher me-2"></i>Log Fire Hydrant Network Inspection</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/township-fire-safety/store">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <?php if ($currentColony): ?>
                    <input type="hidden" name="colony_id" value="<?= (int)$currentColony ?>">
                <?php endif; ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Colony & Date -->
                        <?php if (!$currentColony): ?>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Township Colony <span class="text-danger">*</span></label>
                            <select name="colony_id" class="form-select" required id="fireColonySelect">
                                <option value="">— Select Colony —</option>
                                <?php foreach ($colonies as $col): ?>
                                    <option value="<?= (int)$col['id'] ?>"><?= htmlspecialchars($col['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Inspection Date <span class="text-danger">*</span></label>
                            <input type="date" name="inspection_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fire Zone / Ring Main Identifier <span class="text-danger">*</span></label>
                            <input type="text" name="fire_zone_identifier" class="form-control" placeholder="e.g. Ring Main Zone A – Sector 3" required>
                        </div>

                        <!-- Pressure & Pump Status -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Static Line Pressure (Bar)</label>
                            <input type="number" step="0.01" name="static_line_pressure_bar" class="form-control" value="7.00" min="0" max="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jockey Pump Status</label>
                            <select name="jockey_pump_status" class="form-select">
                                <option value="auto_standby">Auto Standby ✅</option>
                                <option value="running_pressurizing">Running (Pressurizing)</option>
                                <option value="manual_mode">Manual Mode</option>
                                <option value="fault_tripped">Fault / Tripped ❌</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Main Electric Pump</label>
                            <select name="main_electric_pump_status" class="form-select">
                                <option value="operational_ready">Operational Ready ✅</option>
                                <option value="service_due">Service Due</option>
                                <option value="phase_trip">Phase Trip ⚠️</option>
                                <option value="breakdown">Breakdown ❌</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Diesel Engine Backup</label>
                            <select name="diesel_engine_backup_status" class="form-select">
                                <option value="operational_ready">Operational Ready ✅</option>
                                <option value="battery_weak">Battery Weak</option>
                                <option value="fuel_low">Fuel Low</option>
                                <option value="cranking_failure">Cranking Failure ❌</option>
                            </select>
                        </div>

                        <!-- Water Storage & Hydrant Valves -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fire Water Storage (kL)</label>
                            <input type="number" step="0.01" name="fire_water_storage_kl" class="form-control" value="100.00" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Hydrant Valves Tested</label>
                            <input type="number" name="hydrant_valves_tested" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Hydrant Valves Healthy</label>
                            <input type="number" name="hydrant_valves_healthy" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Hose Boxes Equipped</label>
                            <input type="number" name="hose_boxes_equipped" class="form-control" value="0" min="0">
                        </div>

                        <!-- Extinguishers & Drill -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Extinguishers Checked</label>
                            <input type="number" name="extinguishers_checked" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fire Drill Conducted?</label>
                            <select name="fire_drill_conducted" class="form-select">
                                <option value="">No</option>
                                <option value="1">Yes ✅</option>
                            </select>
                        </div>

                        <!-- UP Fire NOC -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">UP Fire NOC Status</label>
                            <select name="up_fire_noc_status" class="form-select">
                                <option value="valid_compliant">Valid &amp; Compliant ✅</option>
                                <option value="renewal_due_30d">Renewal Due in 30 Days</option>
                                <option value="renewal_applied">Renewal Applied</option>
                                <option value="provisional_noc">Provisional NOC</option>
                                <option value="expired">Expired ❌</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">NOC Certificate Number</label>
                            <input type="text" name="noc_certificate_number" class="form-control" placeholder="UP/FIRE/GKP/2024/XXXX">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">NOC Expiry Date</label>
                            <input type="date" name="noc_expiry_date" class="form-control">
                        </div>

                        <!-- Officer Details -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fire Safety Officer Name</label>
                            <input type="text" name="fire_officer_name" class="form-control" placeholder="Chief Fire Safety Officer">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fire Officer Contact</label>
                            <input type="tel" name="fire_officer_contact" class="form-control" placeholder="98XXXXXXXX">
                        </div>

                        <!-- Defects & Remarks -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Defects Observed</label>
                            <textarea name="defects_observed" class="form-control" rows="2" placeholder="List any faulty valves, leaks, pump issues..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Inspector Remarks</label>
                            <textarea name="remarks" class="form-control" rows="2" placeholder="General inspection notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-semibold">
                        <i class="fas fa-save me-1"></i>Save Fire Safety Inspection Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
