<?php
/**
 * Admin Township Borewell & Potable Water Supply Log Desk
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
            <h3 class="fw-bold mb-1"><i class="fas fa-water text-primary me-2"></i>Township Borewell &amp; Potable Water Supply Desk</h3>
            <p class="text-muted small mb-0">Daily pumping hours, reservoir tank levels, electricity consumption &amp; BIS TDS potable water compliance</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newLogModal">
            <i class="fas fa-plus-circle me-1"></i> Log Pumping Run
        </button>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Water Pumped Today</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= number_format((float)($summary['total_water_kl'] ?? 0), 1) ?> <span class="fs-6 text-muted">kL</span></div>
                <div class="text-muted small mt-1"><?= number_format((float)($summary['total_water_kl'] ?? 0) * 1000) ?> Litres</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Pump Run Hours</div>
                <div class="fs-4 fw-bold text-info mt-1"><?= number_format((float)($summary['total_run_hours'] ?? 0), 1) ?> <span class="fs-6 text-muted">hrs</span></div>
                <div class="text-muted small mt-1">Power: <?= number_format((float)($summary['total_power_kwh'] ?? 0), 1) ?> kWh</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-success border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Average Water TDS</div>
                <div class="fs-4 fw-bold text-success mt-1"><?= (int)($summary['avg_tds'] ?? 0) ?> <span class="fs-6 text-muted">PPM</span></div>
                <div class="text-muted small mt-1">Avg pH: <?= number_format((float)($summary['avg_ph'] ?? 7.2), 1) ?> (Potable: &lt; 500)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-<?= ($summary['alerts_count'] ?? 0) > 0 ? 'danger' : 'secondary' ?> border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Maintenance Alerts</div>
                <div class="fs-4 fw-bold text-<?= ($summary['alerts_count'] ?? 0) > 0 ? 'danger' : 'secondary' ?> mt-1"><?= (int)($summary['alerts_count'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Pump vibration / breakdown</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="/admin/township-water-supply" class="row g-2 align-items-center">
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
                    <a href="/admin/township-water-supply" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0"><i class="fas fa-list-alt text-primary me-2"></i>Recent Water Supply &amp; Pumping Operations</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date &amp; Shift</th>
                        <th>Colony</th>
                        <th>Pump Station</th>
                        <th>Hours &amp; Power</th>
                        <th>Volume (kL)</th>
                        <th>OHT Level</th>
                        <th>TDS &amp; pH</th>
                        <th>Pump Health</th>
                        <th>Operator</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="fas fa-water fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                No pumping logs recorded yet. Click "Log Pumping Run" to record daily water supply.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <?php
                            $tdsVal = (int)$l['tds_ppm'];
                            $tdsBadge = $tdsVal <= 300 ? 'bg-success' : ($tdsVal <= 500 ? 'bg-info' : 'bg-danger');
                            $healthBadge = $l['pump_health'] === 'optimal' ? 'bg-success' : 'bg-warning text-dark';
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-semibold text-dark"><?= htmlspecialchars($l['log_date']) ?></span><br>
                                    <span class="badge bg-light text-dark text-capitalize border"><?= htmlspecialchars($l['shift']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-primary"><?= htmlspecialchars($l['colony_name'] ?? 'Colony #' . $l['colony_id']) ?></span>
                                </td>
                                <td>
                                    <span class="small fw-semibold"><?= htmlspecialchars($l['pump_number']) ?></span><br>
                                    <span class="text-muted small"><?= $l['pump_start_time'] ? substr($l['pump_start_time'], 0, 5) : '--' ?> to <?= $l['pump_stop_time'] ? substr($l['pump_stop_time'], 0, 5) : '--' ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?= number_format((float)$l['run_hours'], 1) ?> hrs</span><br>
                                    <span class="text-muted small"><?= number_format((float)$l['power_units_kwh'], 1) ?> kWh</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?= number_format((float)$l['water_pumped_kl'], 1) ?> kL</span><br>
                                    <span class="text-muted small"><?= number_format((float)$l['water_pumped_kl'] * 1000) ?> L</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                            <div class="progress-bar bg-info" style="width: <?= (int)$l['oht_level_pct'] ?>%;"></div>
                                        </div>
                                        <span class="small fw-semibold"><?= (int)$l['oht_level_pct'] ?>%</span>
                                    </div>
                                    <span class="text-muted small"><?= number_format((float)$l['line_pressure_bar'], 2) ?> bar</span>
                                </td>
                                <td>
                                    <span class="badge <?= $tdsBadge ?>"><?= $tdsVal ?> PPM</span>
                                    <span class="badge bg-secondary">pH <?= number_format((float)$l['ph_level'], 1) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $healthBadge ?> text-capitalize"><?= str_replace('_', ' ', $l['pump_health']) ?></span>
                                </td>
                                <td>
                                    <span class="fw-semibold small"><?= htmlspecialchars($l['operator_name']) ?></span><br>
                                    <span class="text-muted small"><?= htmlspecialchars($l['operator_phone'] ?? '') ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="/admin/township-water-supply/print?id=<?= $l['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Print Log Slip">
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

<!-- Modal: New Water Log -->
<div class="modal fade" id="newLogModal" tabindex="-1" aria-labelledby="newLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/township-water-supply/store" class="modal-content border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="newLogModalLabel"><i class="fas fa-water me-2"></i>Record Borewell Pumping Run</h5>
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
                        <label class="form-label small fw-semibold">Date</label>
                        <input type="date" name="log_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Shift</label>
                        <select name="shift" class="form-select">
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="evening">Evening</option>
                            <option value="night">Night</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Pump Station / Number</label>
                        <input type="text" name="pump_number" class="form-control" value="Borewell Pump #1">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Start Time</label>
                        <input type="time" name="pump_start_time" class="form-control" value="06:00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Stop Time</label>
                        <input type="time" name="pump_stop_time" class="form-control" value="10:00">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Run Hours</label>
                        <input type="number" step="0.1" name="run_hours" class="form-control" value="4.0" placeholder="Auto / Manual">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Water Pumped (kL)</label>
                        <input type="number" step="0.5" name="water_pumped_kl" class="form-control" value="60.0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Power Consumption (kWh)</label>
                        <input type="number" step="0.5" name="power_units_kwh" class="form-control" value="28.0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">OHT Level (%)</label>
                        <input type="number" min="0" max="100" name="oht_level_pct" class="form-control" value="85">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Water TDS (PPM)</label>
                        <input type="number" name="tds_ppm" class="form-control" value="240" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">pH Level</label>
                        <input type="number" step="0.1" name="ph_level" class="form-control" value="7.2">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Chlorine Dosing (g)</label>
                        <input type="number" step="1" name="chlorine_dose_gm" class="form-control" value="50">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Line Pressure (Bar)</label>
                        <input type="number" step="0.1" name="line_pressure_bar" class="form-control" value="2.5">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Pump Attendant / Operator *</label>
                        <input type="text" name="operator_name" class="form-control" value="Suresh Yadav" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Operator Contact</label>
                        <input type="text" name="operator_phone" class="form-control" value="+91 9839123456">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Pump Mechanical Health</label>
                        <select name="pump_health" class="form-select">
                            <option value="optimal">Optimal (Smooth Operation)</option>
                            <option value="minor_vibration">Minor Motor Vibration</option>
                            <option value="low_yield">Low Borewell Yield</option>
                            <option value="needs_servicing">Scheduled AMC Servicing Due</option>
                            <option value="breakdown">Breakdown / Tripped</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Shift Remarks / Observations</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Water distribution pressure normal, overhead tank filled to capacity."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i> Save Pumping Log</button>
            </div>
        </form>
    </div>
</div>
