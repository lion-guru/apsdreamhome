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
                <i class="fas fa-lightbulb text-warning me-2"></i>Township Central Street Lighting & Feeder Pillar Uptime Desk
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">ERP</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/colonies">Colonies</a></li>
                    <li class="breadcrumb-item active">Street Lighting Desk</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/colonies<?= $currentColony ? '/' . $currentColony : '' ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back to Colony 360°
            </a>
            <button class="btn btn-warning btn-sm text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#newLightingModal">
                <i class="fas fa-plus-circle me-1"></i>Log Feeder Pillar Audit
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
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #f59e0b;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Pillars Audited</span>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2 text-warning d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-bolt"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['total_audits'] ?? 0) ?></h3>
                    <small class="text-muted">Today's checks</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #10b981;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Poles Functional</span>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 text-success d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['total_functional'] ?? 0) ?></h3>
                    <small class="text-muted">Of <?= number_format($summary['total_poles'] ?? 0) ?> Total Poles</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #ef4444;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Poles Faulty</span>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format($summary['total_faulty'] ?? 0) ?></h3>
                    <small class="text-danger">Action ticket required</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-left:4px solid #3b82f6;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted small fw-semibold text-uppercase">Average Uptime</span>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?= number_format((float)($summary['avg_uptime'] ?? 100.0), 1) ?>%</h3>
                    <small class="text-muted">Illumination benchmark</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Colony Filter Toolbar -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
        <div class="card-body p-3">
            <form method="GET" action="<?= BASE_URL ?>/admin/township-lighting" class="row g-2 align-items-center">
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
                        <a href="<?= BASE_URL ?>/admin/township-lighting" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="fas fa-times me-1"></i>Clear Filter
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-list text-primary me-2"></i>Street Lighting Audits & MCB Logs</h5>
            <span class="badge bg-light text-muted border">Showing <?= count($logs) ?> logs</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Audit Date</th>
                            <th>Colony</th>
                            <th>Feeder Pillar</th>
                            <th>Timer Mode</th>
                            <th>Poles Functional / Total</th>
                            <th>Uptime %</th>
                            <th>Phase Balance</th>
                            <th>Electrician</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-lightbulb fa-2x mb-2 text-warning opacity-50 d-block"></i>
                                    No street lighting audit records found for this selection.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        <?= date('d M Y', strtotime($log['audit_date'])) ?>
                                        <div class="text-muted small">
                                            <?= $log['on_time'] ? date('h:i A', strtotime($log['on_time'])) : '--:--' ?> - 
                                            <?= $log['off_time'] ? date('h:i A', strtotime($log['off_time'])) : '--:--' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($log['colony_name'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-dark border">
                                            <?= htmlspecialchars($log['feeder_pillar_code']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['timer_mode']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= (int)$log['poles_functional'] ?></strong> / <?= (int)$log['total_poles_connected'] ?>
                                        <?php if ((int)$log['poles_faulty'] > 0): ?>
                                            <span class="badge bg-danger ms-1"><?= (int)$log['poles_faulty'] ?> Faulty</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $up = (float)$log['uptime_percent']; ?>
                                        <span class="badge bg-<?= ($up >= 95) ? 'success' : (($up >= 80) ? 'warning' : 'danger') ?>">
                                            <?= number_format($up, 1) ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($log['phase_balance_status'] === 'balanced_ryb') ? 'success' : 'danger' ?> bg-opacity-15 text-<?= ($log['phase_balance_status'] === 'balanced_ryb') ? 'success' : 'danger' ?>">
                                            <?= htmlspecialchars(str_replace('_', ' ', ucfirst($log['phase_balance_status']))) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($log['electrician_name'] ?: 'Duty Electrician') ?>
                                        <?php if (!empty($log['electrician_phone'])): ?>
                                            <div class="text-muted small"><?= htmlspecialchars($log['electrician_phone']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="<?= BASE_URL ?>/admin/township-lighting/<?= (int)$log['id'] ?>/print" target="_blank" class="btn btn-outline-primary btn-sm" title="Print Certificate">
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

<!-- Modal: Log Street Lighting Audit -->
<div class="modal fade" id="newLightingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/township-lighting/store">
                <?= \App\Core\Security\CSRF::generateTokenField() ?>
                <div class="modal-header bg-warning bg-opacity-10">
                    <h5 class="modal-title fw-bold text-dark"><i class="fas fa-lightbulb text-warning me-2"></i>Log Street Lighting & Feeder Pillar Audit</h5>
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
                            <label class="form-label small fw-semibold">Feeder Pillar Identifier <span class="text-danger">*</span></label>
                            <input type="text" name="feeder_pillar_code" class="form-control form-control-sm" placeholder="e.g. Main Boulevard Pillar FP-1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Timer Switching Mode</label>
                            <select name="timer_mode" class="form-select form-select-sm">
                                <option value="astronomical_timer">Astronomical Timer (Auto Dawn/Dusk)</option>
                                <option value="photocell_sensor">Photocell Lux Sensor</option>
                                <option value="manual_mcb_switch">Manual MCB Switch</option>
                                <option value="smart_iot">Smart IoT Feeder Gateway</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Total Poles Connected <span class="text-danger">*</span></label>
                            <input type="number" name="total_poles_connected" id="totalPolesInput" class="form-control form-control-sm" min="1" placeholder="e.g. 45" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Functional Poles</label>
                            <input type="number" name="poles_functional" id="functionalPolesInput" class="form-control form-control-sm" min="0" placeholder="e.g. 42">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Faulty / Blackout Poles</label>
                            <input type="number" name="poles_faulty" id="faultyPolesInput" class="form-control form-control-sm" min="0" placeholder="e.g. 3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Switch ON Time</label>
                            <input type="time" name="on_time" class="form-control form-control-sm" value="18:15">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Switch OFF Time</label>
                            <input type="time" name="off_time" class="form-control form-control-sm" value="05:45">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Phase Balance Status (R-Y-B)</label>
                            <select name="phase_balance_status" class="form-select form-select-sm">
                                <option value="balanced_ryb">Optimal Balanced (R-Y-B normal)</option>
                                <option value="phase_r_trip">Phase R Trip (Red Phase MCB Tripped)</option>
                                <option value="phase_y_trip">Phase Y Trip (Yellow Phase MCB Tripped)</option>
                                <option value="phase_b_trip">Phase B Trip (Blue Phase MCB Tripped)</option>
                                <option value="voltage_fluctuation">Voltage Fluctuation Warning</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Armoured Cable Health</label>
                            <select name="cable_health" class="form-select form-select-sm">
                                <option value="healthy_underground">Healthy Underground Cable</option>
                                <option value="minor_joint_fault">Minor Joint Insulation Needed</option>
                                <option value="cut_repair_needed">Underground Cable Cut - Urgent Repair</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Faulty Pole Identification & Blackout Notes</label>
                            <input type="text" name="faulty_pole_numbers" class="form-control form-control-sm" placeholder="e.g. Pole #12 (LED module burnt), Pole #27 (loose fuse)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Duty Electrician Name</label>
                            <input type="text" name="electrician_name" class="form-control form-control-sm" placeholder="e.g. Ramesh Kumar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Electrician Contact Phone</label>
                            <input type="text" name="electrician_phone" class="form-control form-control-sm" placeholder="e.g. +91 98765 43210">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Engineering Remarks</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Lux meter reading, tree branches trimming near fixtures, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold">Save Lighting Audit</button>
                </div>
            </form>
        </div>
    </div>
</div>
