<?php
/**
 * Admin Township Central CCTV Surveillance & Camera Uptime Audit Desk
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
            <h3 class="fw-bold mb-1"><i class="fas fa-video text-primary me-2"></i>Township CCTV Surveillance &amp; Uptime Audit Desk</h3>
            <p class="text-muted small mb-0">Central NVR systems, 24/7 camera online uptime, HDD retention days &amp; footage export audit</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newCctvModal">
            <i class="fas fa-shield-alt me-1"></i> Log CCTV Audit
        </button>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Surveillance Cameras</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= (int)($summary['total_cameras'] ?? 0) ?></div>
                <div class="text-muted small mt-1"><?= (int)($summary['total_nvrs_audited'] ?? 0) ?> central NVR stations audited</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Online Cameras</div>
                <div class="fs-4 fw-bold text-success mt-1"><?= (int)($summary['total_online'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Average Uptime: <?= number_format((float)($summary['avg_uptime_pct'] ?? 100), 1) ?>%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-danger border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Offline / Faulty Cameras</div>
                <div class="fs-4 fw-bold text-danger mt-1"><?= (int)($summary['total_offline'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Requires cable / lens technician check</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Footage Exports</div>
                <div class="fs-4 fw-bold text-info mt-1"><?= (int)($summary['total_footage_requests'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Police / resident incident clips</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="/admin/township-cctv" class="row g-2 align-items-center">
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
                    <a href="/admin/township-cctv" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0"><i class="fas fa-list-alt text-primary me-2"></i>NVR Station Audits &amp; Camera Online Status Register</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Audit Date</th>
                        <th>Colony &amp; NVR Unit</th>
                        <th>Cameras (Total / Online)</th>
                        <th>Uptime Rate</th>
                        <th>HDD Storage</th>
                        <th>Night IR Health</th>
                        <th>Offline Locations</th>
                        <th>Technician</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-video fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                No CCTV audits recorded yet. Click "Log CCTV Audit" to track camera uptime.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <?php
                            $uptimeVal = (float)$l['uptime_percentage'];
                            $uptimeBadge = $uptimeVal >= 95 ? 'bg-success' : ($uptimeVal >= 80 ? 'bg-warning text-dark' : 'bg-danger');
                            $hddBadge = $l['hdd_storage_status'] === 'healthy' ? 'bg-success' : 'bg-warning text-dark';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($l['audit_date']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= htmlspecialchars($l['colony_name'] ?? 'Colony #' . $l['colony_id']) ?></span><br>
                                    <span class="small fw-semibold text-dark"><?= htmlspecialchars($l['nvr_identifier']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= (int)$l['cameras_online_count'] ?> / <?= (int)$l['total_cameras_installed'] ?></span>
                                    <?php if ((int)$l['cameras_offline_count'] > 0): ?>
                                        <span class="badge bg-danger ms-1"><?= (int)$l['cameras_offline_count'] ?> Offline</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $uptimeBadge ?>"><?= number_format($uptimeVal, 1) ?>%</span>
                                </td>
                                <td>
                                    <span class="badge <?= $hddBadge ?> text-capitalize"><?= str_replace('_', ' ', $l['hdd_storage_status']) ?></span><br>
                                    <span class="small text-muted"><?= (int)$l['recording_retention_days'] ?> Days Retention</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $l['night_vision_ir_status']) ?></span>
                                </td>
                                <td>
                                    <span class="small text-danger fw-semibold"><?= htmlspecialchars($l['offline_camera_locations'] ?: 'None (100% Online)') ?></span>
                                </td>
                                <td>
                                    <span class="small fw-semibold"><?= htmlspecialchars($l['cctv_technician_name']) ?></span><br>
                                    <span class="text-muted small"><?= htmlspecialchars($l['technician_phone'] ?? '') ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="/admin/township-cctv/print?id=<?= $l['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print CCTV Audit Slip">
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

<!-- Modal: New CCTV Audit -->
<div class="modal fade" id="newCctvModal" tabindex="-1" aria-labelledby="newCctvModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/township-cctv/store" class="modal-content border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="newCctvModalLabel"><i class="fas fa-video me-2"></i>Record Central CCTV System Audit</h5>
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
                        <label class="form-label small fw-semibold">Audit Date</label>
                        <input type="date" name="audit_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">NVR Identifier</label>
                        <input type="text" name="nvr_identifier" class="form-control" value="Main Control Room NVR-1 (32 Ch)">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Cameras Installed *</label>
                        <input type="number" name="total_cameras_installed" class="form-control" value="32" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Cameras Online *</label>
                        <input type="number" name="cameras_online_count" class="form-control" value="30" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Retention (Days)</label>
                        <input type="number" name="recording_retention_days" class="form-control" value="30">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Footage Exports (Today)</label>
                        <input type="number" name="footage_exported_count" class="form-control" value="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">NVR HDD Storage Status</label>
                        <select name="hdd_storage_status" class="form-select">
                            <option value="healthy">Healthy (Loop Overwriting Normal)</option>
                            <option value="warning_space_low">Warning: Low Disk Space</option>
                            <option value="hdd_fault">HDD Read/Write Fault Alert</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Night IR LED Health</label>
                        <select name="night_vision_ir_status" class="form-select">
                            <option value="all_functional">All Cameras IR Functional</option>
                            <option value="minor_led_failure">Minor LED Dimming (1-2 Cams)</option>
                            <option value="maintenance_needed">IR Maintenance Required</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Technician Full Name *</label>
                        <input type="text" name="cctv_technician_name" class="form-control" value="Ashok Kumar" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Technician Phone</label>
                        <input type="text" name="technician_phone" class="form-control" value="+91 9839332211">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Offline Camera Locations / Channel IDs</label>
                        <input type="text" name="offline_camera_locations" class="form-control" placeholder="e.g. Cam #14: Sector B Outer Wall, Cam #22: Tube-well">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Technician Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="CAT6 cabling verified, PoE switch ports operating normally."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i> Save CCTV Audit</button>
            </div>
        </form>
    </div>
</div>
