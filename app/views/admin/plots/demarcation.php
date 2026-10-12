<?php
/**
 * Admin Township Plot Demarcation & Boundary Pillar Geotagging Desk
 */
$page_title = $page_title ?? 'Plot Demarcation & Boundary Pillars';
$demarcations = $demarcations ?? [];
$stats = $stats ?? [];
$colonies = $colonies ?? [];
$plots = $plots ?? [];
$current_colony = $current_colony ?? null;
$current_status = $current_status ?? 'all';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-dark">
                <i class="fas fa-map-marked-alt text-success me-2"></i>Plot Demarcation & Boundary Geotagging Desk
            </h3>
            <p class="text-muted small mb-0">Surveyor corner pegging, 4-corner GPS coordinates, boundary pillar verification & handover certificates.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newDemarcationModal">
                <i class="fas fa-plus-circle me-1"></i>Record Plot Demarcation
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                        <i class="fas fa-draw-polygon fa-lg"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Total Demarcated</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['total_demarcated'] ?? 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3 text-warning">
                        <i class="fas fa-monument fa-lg"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Pillars Erected</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['pillars_erected'] ?? 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                        <i class="fas fa-check-double fa-lg"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Civil Verified</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['verified'] ?? 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                        <i class="fas fa-handshake fa-lg"></i>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Pegs Handed Over</span>
                        <h4 class="fw-bold mb-0 text-dark"><?= number_format($stats['pegs_handed'] ?? 0) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-3">
            <form method="GET" action="/admin/plots/demarcation" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <select name="colony_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Townships / Colonies</option>
                        <?php foreach ($colonies as $col): ?>
                            <option value="<?= $col['id'] ?>" <?= $current_colony == $col['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($col['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" <?= $current_status === 'all' ? 'selected' : '' ?>>All Pillar Statuses</option>
                        <option value="pegs_driven" <?= $current_status === 'pegs_driven' ? 'selected' : '' ?>>Pegs Driven</option>
                        <option value="pillars_erected" <?= $current_status === 'pillars_erected' ? 'selected' : '' ?>>Pillars Erected</option>
                        <option value="verified" <?= $current_status === 'verified' ? 'selected' : '' ?>>Verified by Civil</option>
                        <option value="disputed" <?= $current_status === 'disputed' ? 'selected' : '' ?>>Disputed</option>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <a href="/admin/plots/demarcation" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Demarcations Table -->
    <div class="card border-0 shadow-sm rounded-3 bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Token & Plot</th>
                            <th>Township</th>
                            <th>Surveyor</th>
                            <th>Date</th>
                            <th>4-Corner GPS Coordinates</th>
                            <th>Status</th>
                            <th>Handover</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($demarcations)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-map-pin fa-3x mb-3 text-secondary opacity-50"></i>
                                    <p class="mb-0">No plot demarcations recorded yet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($demarcations as $dem): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark font-monospace"><?= htmlspecialchars($dem['certificate_token']) ?></div>
                                        <div class="badge bg-light text-dark border">Plot: <?= htmlspecialchars($dem['plot_number']) ?> (<?= number_format($dem['area_sqft']) ?> sq.ft)</div>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-semibold"><?= htmlspecialchars($dem['colony_name']) ?></span>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-user-check text-muted me-1"></i><?= htmlspecialchars($dem['surveyor_name']) ?></div>
                                        <small class="text-muted"><i class="fas fa-phone text-muted me-1"></i><?= htmlspecialchars($dem['surveyor_phone']) ?></small>
                                    </td>
                                    <td>
                                        <?= date('d M Y', strtotime($dem['demarcation_date'])) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($dem['corner_nw_lat']) && !empty($dem['corner_ne_lat'])): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                <i class="fas fa-crosshairs me-1"></i>4 Corners Geotagged
                                            </span>
                                            <div class="small text-muted font-monospace mt-1">
                                                NW: <?= round($dem['corner_nw_lat'], 4) ?>, <?= round($dem['corner_nw_lng'], 4) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">Coordinates Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = match($dem['pillar_status']) {
                                            'verified' => 'bg-success',
                                            'pillars_erected' => 'bg-primary',
                                            'pegs_driven' => 'bg-warning text-dark',
                                            'disputed' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $dem['pillar_status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($dem['possession_peg_handed_over']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Handed Over</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border">Pending Handover</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/plots/demarcation/<?= $dem['id'] ?>/print" target="_blank" class="btn btn-outline-primary" title="Print Demarcation Certificate">
                                                <i class="fas fa-print me-1"></i>Certificate
                                            </a>
                                            <?php if ($dem['pillar_status'] !== 'verified'): ?>
                                                <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#verifyModal<?= $dem['id'] ?>" title="Verify Demarcation">
                                                    <i class="fas fa-check-double"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Verify Modal -->
                                        <div class="modal fade" id="verifyModal<?= $dem['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content text-start">
                                                    <form method="POST" action="/admin/plots/demarcation/<?= $dem['id'] ?>/verify">
                                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Verify Plot Demarcation</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Verify boundary pillars for Plot <strong><?= htmlspecialchars($dem['plot_number']) ?></strong> (<?= htmlspecialchars($dem['certificate_token']) ?>)?</p>
                                                            <div class="mb-3">
                                                                <label class="form-label">Update Status</label>
                                                                <select name="pillar_status" class="form-select">
                                                                    <option value="verified" selected>Verified by Civil Engineer</option>
                                                                    <option value="pillars_erected">Pillars Erected (Awaiting Final Check)</option>
                                                                    <option value="disputed">Boundary Disputed / Re-measure Needed</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Confirm Verification</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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

<!-- New Demarcation Modal -->
<div class="modal fade" id="newDemarcationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="/admin/plots/demarcation/store">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-map-marked-alt me-2"></i>Record Plot Demarcation & Geotags</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Plot <span class="text-danger">*</span></label>
                            <select name="plot_id" class="form-select" required>
                                <option value="">-- Choose Plot --</option>
                                <?php foreach ($plots as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['colony_name']) ?> - Plot #<?= htmlspecialchars($p['plot_number']) ?> (<?= number_format($p['area_sqft']) ?> sq.ft)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Demarcation Date <span class="text-danger">*</span></label>
                            <input type="date" name="demarcation_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Surveyor Name <span class="text-danger">*</span></label>
                            <input type="text" name="surveyor_name" class="form-control" placeholder="e.g. Er. Rajesh Srivastava" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Surveyor Phone <span class="text-danger">*</span></label>
                            <input type="text" name="surveyor_phone" class="form-control" placeholder="10-digit mobile number" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Initial Pillar Status</label>
                            <select name="pillar_status" class="form-select">
                                <option value="pegs_driven" selected>Pegs Driven (Temporary Wooden Pegs)</option>
                                <option value="pillars_erected">Pillars Erected (Concrete Boundary Pillars)</option>
                                <option value="verified">Verified (Approved by Civil Dept)</option>
                                <option value="disputed">Disputed / Adjustment Required</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check">
                                <input type="checkbox" name="possession_peg_handed_over" value="1" class="form-check-input" id="chkPegHandover">
                                <label class="form-check-label fw-semibold" for="chkPegHandover">
                                    Possession Pegs Handed Over to Allottee On-Site
                                </label>
                            </div>
                        </div>

                        <!-- 4 Corner GPS Coordinates -->
                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="fw-bold text-success mb-2">
                                <i class="fas fa-crosshairs me-1"></i>4-Corner GPS Coordinates (WGS84 Lat/Lng)
                            </h6>
                            <p class="text-muted small mb-3">Record high-precision handheld GPS coordinates for boundary pegging.</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Corner 1: North-West (NW)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.00000001" name="corner_nw_lat" class="form-control" placeholder="NW Latitude">
                                <input type="number" step="0.00000001" name="corner_nw_lng" class="form-control" placeholder="NW Longitude">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Corner 2: North-East (NE)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.00000001" name="corner_ne_lat" class="form-control" placeholder="NE Latitude">
                                <input type="number" step="0.00000001" name="corner_ne_lng" class="form-control" placeholder="NE Longitude">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small text-muted">Corner 3: South-West (SW)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.00000001" name="corner_sw_lat" class="form-control" placeholder="SW Latitude">
                                <input type="number" step="0.00000001" name="corner_sw_lng" class="form-control" placeholder="SW Longitude">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Corner 4: South-East (SE)</label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.00000001" name="corner_se_lat" class="form-control" placeholder="SE Latitude">
                                <input type="number" step="0.00000001" name="corner_se_lng" class="form-control" placeholder="SE Longitude">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Survey & Boundary Notes</label>
                            <textarea name="boundary_notes" class="form-control" rows="2" placeholder="e.g. Pegged adjacent to 30ft master sector road, concrete markers driven with red paint."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Demarcation Record</button>
                </div>
            </form>
        </div>
    </div>
</div>
