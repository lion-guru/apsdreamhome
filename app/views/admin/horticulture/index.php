<?php
/**
 * Admin Township Central Park Horticulture & Green Canopy Tree Census Desk View
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
            <h3 class="fw-bold mb-1"><i class="fas fa-seedling text-success me-2"></i>Township Green Canopy &amp; Park Horticulture Desk</h3>
            <p class="text-muted small mb-0">Central park landscaping, tree plantation census, sapling survival rates &amp; Miyawaki green cover audit</p>
        </div>
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#newHorticultureModal">
            <i class="fas fa-tree me-1"></i> Log Park Census / Gardening
        </button>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Total Trees Planted</div>
                <div class="fs-4 fw-bold text-success mt-1"><?= number_format((int)($summary['total_trees_planted'] ?? 0)) ?></div>
                <div class="text-muted small mt-1"><?= (int)($summary['total_parks_audited'] ?? 0) ?> parks / green corridors audited</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Green Canopy Area</div>
                <div class="fs-4 fw-bold text-info mt-1"><?= number_format((float)($summary['total_green_area_sqft'] ?? 0)) ?> <span class="fs-6 text-muted">sq.ft</span></div>
                <div class="text-muted small mt-1">Dedicated parklands &amp; avenues</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Sapling Survival Rate</div>
                <div class="fs-4 fw-bold text-primary mt-1"><?= number_format((float)($summary['avg_survival_rate'] ?? 92.5), 1) ?>%</div>
                <div class="text-muted small mt-1">UP Town Planning standard &gt; 80%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-secondary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Gardening Workforce</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= (int)($summary['total_gardeners'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Active horticulturists on site</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="/admin/township-horticulture" class="row g-2 align-items-center">
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
                    <a href="/admin/township-horticulture" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0"><i class="fas fa-leaf text-success me-2"></i>Park Horticulture Logs &amp; Tree Plantation Register</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Colony &amp; Park Name</th>
                        <th>Green Area (sqft)</th>
                        <th>Trees Count</th>
                        <th>Species Planted</th>
                        <th>Survival Rate</th>
                        <th>Watering Method</th>
                        <th>Task Status</th>
                        <th>Supervisor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="fas fa-tree fs-2 d-block mb-2 text-success opacity-50"></i>
                                No horticulture census logs recorded yet. Click "Log Park Census / Gardening" to record green assets.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($l['census_date']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= htmlspecialchars($l['colony_name'] ?? 'Colony #' . $l['colony_id']) ?></span><br>
                                    <span class="small fw-semibold text-dark"><?= htmlspecialchars($l['park_name']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= number_format((float)$l['green_area_sqft']) ?></span> sq.ft
                                </td>
                                <td>
                                    <span class="badge bg-success fs-6"><?= number_format((int)$l['trees_planted_count']) ?> Trees</span>
                                </td>
                                <td>
                                    <span class="small text-muted"><?= htmlspecialchars($l['tree_species_summary']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px; width: 60px;">
                                            <div class="progress-bar bg-success" style="width: <?= (float)$l['survival_rate_pct'] ?>%;"></div>
                                        </div>
                                        <span class="small fw-bold"><?= number_format((float)$l['survival_rate_pct'], 1) ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $l['watering_method']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info-emphasis text-capitalize"><?= str_replace('_', ' ', $l['maintenance_tasks']) ?></span>
                                </td>
                                <td>
                                    <span class="small fw-semibold"><?= htmlspecialchars($l['horticulture_supervisor']) ?></span><br>
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

<!-- Modal: New Horticulture Log -->
<div class="modal fade" id="newHorticultureModal" tabindex="-1" aria-labelledby="newHortModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/township-horticulture/store" class="modal-content border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="newHortModalLabel"><i class="fas fa-tree me-2"></i>Record Park Horticulture &amp; Tree Census</h5>
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
                        <input type="date" name="census_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Park / Green Area (sqft)</label>
                        <input type="number" step="100" name="green_area_sqft" class="form-control" value="15000">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Park / Avenue Name</label>
                        <input type="text" name="park_name" class="form-control" value="Central Amrit Sarovar Park">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Trees Planted Count *</label>
                        <input type="number" name="trees_planted_count" class="form-control" value="120" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Survival Rate (%)</label>
                        <input type="number" step="0.5" name="survival_rate_pct" class="form-control" value="94.0">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Tree Species Planted / Preserved</label>
                        <input type="text" name="tree_species_summary" class="form-control" value="Neem, Gulmohar, Peepal, Amaltas, Bougainvillea, Champa">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Watering Method</label>
                        <select name="watering_method" class="form-select">
                            <option value="automated_sprinkler">Automated Sprinkler System</option>
                            <option value="tractor_tanker">Tractor Water Tanker</option>
                            <option value="manual_hose">Manual Hose Pipe</option>
                            <option value="drip_irrigation">Drip Irrigation</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Fertilizer / Vermicompost (kg)</label>
                        <input type="number" step="1" name="fertilizer_used_kg" class="form-control" value="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Active Gardeners (Headcount)</label>
                        <input type="number" name="gardener_headcount" class="form-control" value="4">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Maintenance Task Carried Out</label>
                        <select name="maintenance_tasks" class="form-select">
                            <option value="routine_watering">Routine Daily Watering</option>
                            <option value="grass_cutting_mowing">Lawn Mowing &amp; De-weeding</option>
                            <option value="sapling_plantation">New Sapling Plantation Drive</option>
                            <option value="pest_control_spray">Organic Neem Oil Spray</option>
                            <option value="hedges_pruning">Hedge &amp; Canopy Pruning</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Horticulture Supervisor *</label>
                        <input type="text" name="horticulture_supervisor" class="form-control" value="Kamlesh Maurya" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Supervisor Contact</label>
                        <input type="text" name="supervisor_contact" class="form-control" value="+91 9839887766">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Observations &amp; Landscaping Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Central lawn fertilized, flowering borders pruned, saplings staked with bamboo supports."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-1"></i> Save Horticulture Census</button>
            </div>
        </form>
    </div>
</div>
