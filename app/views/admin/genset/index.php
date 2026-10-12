<?php
/**
 * Admin Township Central DG Genset Power Backup & Diesel Audit Desk
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
            <h3 class="fw-bold mb-1"><i class="fas fa-charging-station text-warning me-2"></i>Township Central DG Genset &amp; Power Backup Desk</h3>
            <p class="text-muted small mb-0">Diesel generator run hours, fuel consumption, kWh power output &amp; pilferage prevention audit</p>
        </div>
        <button class="btn btn-warning text-dark fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#newGensetModal">
            <i class="fas fa-gas-pump me-1"></i> Log DG Genset Run
        </button>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">DG Run Hours Today</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= number_format((float)($summary['total_run_hours'] ?? 0), 1) ?> <span class="fs-6 text-muted">hrs</span></div>
                <div class="text-muted small mt-1">Total <?= (int)($summary['total_runs'] ?? 0) ?> operational runs</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-danger border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Diesel Consumed</div>
                <div class="fs-4 fw-bold text-danger mt-1"><?= number_format((float)($summary['total_diesel_litres'] ?? 0), 1) ?> <span class="fs-6 text-muted">Litres</span></div>
                <div class="text-muted small mt-1">Expense: ₹<?= number_format((float)($summary['total_diesel_expense'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Power Generated (Backup)</div>
                <div class="fs-4 fw-bold text-info mt-1"><?= number_format((float)($summary['total_power_kwh'] ?? 0), 1) ?> <span class="fs-6 text-muted">kWh</span></div>
                <div class="text-muted small mt-1">Avg: <?= number_format((float)($summary['avg_efficiency'] ?? 0), 2) ?> kWh / Litre</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-<?= ($summary['alerts_count'] ?? 0) > 0 ? 'danger' : 'secondary' ?> border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Engine Maintenance Alerts</div>
                <div class="fs-4 fw-bold text-<?= ($summary['alerts_count'] ?? 0) > 0 ? 'danger' : 'secondary' ?> mt-1"><?= (int)($summary['alerts_count'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Lube oil / coolant warnings</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="/admin/township-genset" class="row g-2 align-items-center">
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
                    <a href="/admin/township-genset" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0"><i class="fas fa-history text-warning me-2"></i>Central Diesel Generator Run Logs &amp; Fuel Audit</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date &amp; Outage Reason</th>
                        <th>Colony</th>
                        <th>DG Unit &amp; Timing</th>
                        <th>Run Hours</th>
                        <th>Diesel Consumed</th>
                        <th>Total Cost (₹)</th>
                        <th>Power Output</th>
                        <th>Engine Health</th>
                        <th>Operator</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-charging-station fs-2 d-block mb-2 text-warning opacity-50"></i>
                                No DG Genset runs recorded yet. Click "Log DG Genset Run" to log diesel power backup.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <?php
                            $healthBadge = $l['mechanical_status'] === 'optimal' ? 'bg-success' : 'bg-warning text-dark';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($l['log_date']) ?></span><br>
                                    <span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $l['outage_reason']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= htmlspecialchars($l['colony_name'] ?? 'Colony #' . $l['colony_id']) ?></span>
                                </td>
                                <td>
                                    <span class="small fw-semibold"><?= htmlspecialchars($l['genset_number']) ?></span><br>
                                    <span class="text-muted small"><?= $l['start_time'] ? substr($l['start_time'], 0, 5) : '--' ?> to <?= $l['stop_time'] ? substr($l['stop_time'], 0, 5) : '--' ?></span>
                                </td>
                                <td>
                                    <span class="fw-bold"><?= number_format((float)$l['run_hours'], 1) ?> hrs</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger"><?= number_format((float)$l['diesel_consumed_litres'], 1) ?> L</span><br>
                                    <span class="text-muted small">@ ₹<?= number_format((float)$l['diesel_rate_per_litre'], 2) ?>/L</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark">₹<?= number_format((float)$l['diesel_expense_inr'], 2) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-info"><?= number_format((float)$l['power_generated_kwh'], 1) ?> kWh</span><br>
                                    <span class="text-muted small"><?= number_format((float)$l['fuel_efficiency_kwh_per_litre'], 2) ?> kWh/L</span>
                                </td>
                                <td>
                                    <span class="badge <?= $healthBadge ?> text-capitalize"><?= str_replace('_', ' ', $l['mechanical_status']) ?></span>
                                </td>
                                <td>
                                    <span class="small fw-semibold"><?= htmlspecialchars($l['operator_name']) ?></span><br>
                                    <span class="text-muted small"><?= htmlspecialchars($l['operator_phone'] ?? '') ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="/admin/township-genset/print?id=<?= $l['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print DG Run Certificate">
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

<!-- Modal: New Genset Run -->
<div class="modal fade" id="newGensetModal" tabindex="-1" aria-labelledby="newGensetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/township-genset/store" class="modal-content border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="newGensetModalLabel"><i class="fas fa-gas-pump me-2"></i>Log Central DG Genset Run</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <label class="form-label small fw-semibold">Date</label>
                        <input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Outage Reason</label>
                        <select name="outage_reason" class="form-select">
                            <option value="discom_grid_failure">DISCOM Grid Failure</option>
                            <option value="transformer_overload">Transformer Overload</option>
                            <option value="storm_feeder_breakdown">Storm Feeder Breakdown</option>
                            <option value="scheduled_load_shedding">Scheduled Load Shedding</option>
                            <option value="preventive_routine_trial">Preventive Routine Trial</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">DG Unit Identifier</label>
                        <input type="text" name="genset_number" class="form-control" value="Central DG Set #1 (125 kVA)">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Start Time</label>
                        <input type="time" name="start_time" class="form-control" value="18:00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Stop Time</label>
                        <input type="time" name="stop_time" class="form-control" value="20:30">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Run Hours</label>
                        <input type="number" step="0.1" name="run_hours" class="form-control" value="2.5" placeholder="Auto / Manual">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Diesel Consumed (L) *</label>
                        <input type="number" step="0.5" name="diesel_consumed_litres" class="form-control" value="45.0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Diesel Rate / Litre (₹)</label>
                        <input type="number" step="0.1" name="diesel_rate_per_litre" class="form-control" value="90.50">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Power Generated (kWh)</label>
                        <input type="number" step="1" name="power_generated_kwh" class="form-control" value="150.0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Fresh Diesel Added (L)</label>
                        <input type="number" step="1" name="diesel_added_litres" class="form-control" value="0.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Closing Tank Stock (L)</label>
                        <input type="number" step="1" name="diesel_closing_stock_litres" class="form-control" value="210.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Mechanical Health</label>
                        <select name="mechanical_status" class="form-select">
                            <option value="optimal">Optimal (Smooth Operation)</option>
                            <option value="lube_oil_service_due">Lube Oil Service Due (AMC)</option>
                            <option value="low_coolant">Low Radiator Coolant</option>
                            <option value="battery_charger_fault">Battery / Self Starter Warning</option>
                            <option value="breakdown">Breakdown / High Temp Trip</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">DG Attendant / Operator *</label>
                        <input type="text" name="operator_name" class="form-control" value="Manoj Verma" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Operator Contact</label>
                        <input type="text" name="operator_phone" class="form-control" value="+91 9839654321">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Observations / Load Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Streetlights and Phase-1 borewell fed smoothly during 33kV line tripping."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning text-dark fw-semibold"><i class="fas fa-check-circle me-1"></i> Save Genset Log</button>
            </div>
        </form>
    </div>
</div>
