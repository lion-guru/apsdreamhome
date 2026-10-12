<?php
/**
 * Admin Township Security Guard Shift Roster & Night Patrol Incident Desk View
 */
$colonies = $colonies ?? [];
$rosters = $rosters ?? [];
$incidents = $incidents ?? [];
$metrics = $metrics ?? [];
$current_colony = $current_colony ?? null;
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-shield-alt text-primary me-2"></i>Township Security &amp; Gatehouse Patrol Desk</h3>
            <p class="text-muted small mb-0">Manage guard shift rosters, entry boom-barriers, visitor logs &amp; security incident logs across townships</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger shadow-sm" data-bs-toggle="modal" data-bs-target="#newIncidentModal">
                <i class="fas fa-exclamation-triangle me-1"></i> Log Incident
            </button>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#newRosterModal">
                <i class="fas fa-user-shield me-1"></i> Add Guard Shift
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-primary border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Active Guards On Duty</div>
                <div class="fs-4 fw-bold text-dark mt-1"><?= (int)($metrics['active_guards'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Checked-in shifts today</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-info border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Visitor Vehicles Logged</div>
                <div class="fs-4 fw-bold text-info mt-1"><?= (int)($metrics['total_visitors'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Gate pass entries today</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Open Incidents</div>
                <div class="fs-4 fw-bold text-warning mt-1"><?= (int)($metrics['open_incidents'] ?? 0) ?></div>
                <div class="text-muted small mt-1">Pending resolution</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white border-start border-danger border-4 rounded-3">
                <div class="text-muted small text-uppercase fw-semibold">Critical Alerts</div>
                <div class="fs-4 fw-bold text-danger mt-1"><?= (int)($metrics['critical_count'] ?? 0) ?></div>
                <div class="text-muted small mt-1">High/Critical security alerts</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="/admin/township-security" class="row g-2 align-items-center">
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
                    <a href="/admin/township-security" class="btn btn-outline-secondary btn-sm"><i class="fas fa-undo me-1"></i> Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Nav Tabs: Rosters vs Incidents -->
    <ul class="nav nav-tabs mb-3" id="securityTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold" id="rosters-tab" data-bs-toggle="tab" data-bs-target="#rosters-pane" type="button" role="tab"><i class="fas fa-clipboard-list me-1"></i> Guard Shift Rosters (<?= count($rosters) ?>)</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-danger" id="incidents-tab" data-bs-toggle="tab" data-bs-target="#incidents-pane" type="button" role="tab"><i class="fas fa-exclamation-circle me-1"></i> Security Incident Logs (<?= count($incidents) ?>)</button>
        </li>
    </ul>

    <div class="tab-content" id="securityTabsContent">
        <!-- Roster Pane -->
        <div class="tab-pane fade show active" id="rosters-pane" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Date &amp; Shift</th>
                                <th>Colony &amp; Gate</th>
                                <th>Security Guard</th>
                                <th>Agency &amp; Supervisor</th>
                                <th>Status</th>
                                <th>Boom Barrier</th>
                                <th>Visitors</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rosters)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-user-shield fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        No guard shifts scheduled. Click "Add Guard Shift" to schedule entrance security.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rosters as $r): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-semibold text-dark"><?= htmlspecialchars($r['roster_date']) ?></span><br>
                                            <span class="badge bg-light text-dark border"><?= str_replace('_', ' ', $r['shift']) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-primary"><?= htmlspecialchars($r['colony_name'] ?? 'Colony #' . $r['colony_id']) ?></span><br>
                                            <span class="small text-muted"><?= htmlspecialchars($r['gate_name']) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?= htmlspecialchars($r['guard_name']) ?></span><br>
                                            <span class="small text-muted"><i class="fas fa-phone-alt me-1"></i><?= htmlspecialchars($r['guard_phone']) ?> | Aadhaar: ****<?= htmlspecialchars($r['guard_aadhaar_last4']) ?></span>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold"><?= htmlspecialchars($r['agency_name']) ?></span><br>
                                            <span class="small text-muted">Sup: <?= htmlspecialchars($r['supervisor_name']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $r['status'] === 'checked_in' ? 'bg-success' : 'bg-secondary' ?> text-capitalize"><?= str_replace('_', ' ', $r['status']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $r['boom_barrier_status'] === 'functional' ? 'bg-success' : 'bg-warning text-dark' ?> text-capitalize"><?= str_replace('_', ' ', $r['boom_barrier_status']) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark"><?= (int)$r['visitor_vehicles_logged'] ?></span> vehicles
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Incidents Pane -->
        <div class="tab-pane fade" id="incidents-pane" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Token &amp; Time</th>
                                <th>Colony &amp; Location</th>
                                <th>Category</th>
                                <th>Severity</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($incidents)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-shield-alt fs-2 d-block mb-2 text-success opacity-50"></i>
                                        Perimeter all clear! No security incidents or breaches reported.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($incidents as $inc): ?>
                                    <?php
                                    $sevBadge = match($inc['severity']) {
                                        'critical' => 'bg-danger',
                                        'high' => 'bg-warning text-dark',
                                        'medium' => 'bg-info text-dark',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-dark font-monospace"><?= htmlspecialchars($inc['incident_token']) ?></span><br>
                                            <span class="small text-muted"><?= htmlspecialchars($inc['incident_date']) ?> <?= substr($inc['incident_time'], 0, 5) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-primary"><?= htmlspecialchars($inc['colony_name'] ?? 'Colony #' . $inc['colony_id']) ?></span><br>
                                            <span class="small text-muted"><?= htmlspecialchars($inc['location_detail']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border text-capitalize"><?= str_replace('_', ' ', $inc['category']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $sevBadge ?> text-uppercase"><?= htmlspecialchars($inc['severity']) ?></span>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold text-dark"><?= htmlspecialchars(substr($inc['description'], 0, 80)) ?><?= strlen($inc['description']) > 80 ? '...' : '' ?></div>
                                            <?php if (!empty($inc['action_taken'])): ?>
                                                <div class="small text-muted"><i class="fas fa-check text-success me-1"></i>Action: <?= htmlspecialchars($inc['action_taken']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $inc['status'] === 'resolved' ? 'bg-success' : 'bg-warning text-dark' ?> text-capitalize"><?= str_replace('_', ' ', $inc['status']) ?></span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php if ($inc['status'] !== 'resolved'): ?>
                                                <button class="btn btn-sm btn-outline-success" onclick="openResolveModal(<?= $inc['id'] ?>, '<?= htmlspecialchars($inc['incident_token']) ?>')">
                                                    <i class="fas fa-check-circle me-1"></i> Resolve
                                                </button>
                                            <?php else: ?>
                                                <span class="small text-muted"><i class="fas fa-lock me-1"></i>Archived</span>
                                            <?php endif; ?>
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
</div>

<!-- Modal: New Guard Roster -->
<div class="modal fade" id="newRosterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/township-security/roster" class="modal-content border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-user-shield me-2"></i>Schedule Guard Shift Roster</h5>
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
                        <input type="date" name="roster_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Shift</label>
                        <select name="shift" class="form-select">
                            <option value="morning_06_14">Morning (06:00 - 14:00)</option>
                            <option value="evening_14_22">Evening (14:00 - 22:00)</option>
                            <option value="night_22_06">Night (22:00 - 06:00)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Gate Location</label>
                        <input type="text" name="gate_name" class="form-control" value="Main Entrance Gate #1">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Security Agency</label>
                        <input type="text" name="agency_name" class="form-control" value="Purvanchal Security Services">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Guard Full Name *</label>
                        <input type="text" name="guard_name" class="form-control" placeholder="e.g. Ramesh Chandra" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Guard Phone *</label>
                        <input type="text" name="guard_phone" class="form-control" placeholder="+91 9839XXXXXX" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Aadhaar (Last 4 Digits) *</label>
                        <input type="text" name="guard_aadhaar_last4" maxlength="4" class="form-control" placeholder="1234" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Agency Supervisor</label>
                        <input type="text" name="supervisor_name" class="form-control" value="Dharmendra Singh">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Boom Barrier Status</label>
                        <select name="boom_barrier_status" class="form-select">
                            <option value="functional">Functional (Auto RFID / Sensor)</option>
                            <option value="manual_override">Manual Override</option>
                            <option value="under_maintenance">Under AMC Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Visitors Logged</label>
                        <input type="number" name="visitor_vehicles_logged" class="form-control" value="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-check-circle me-1"></i> Save Shift</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: New Incident -->
<div class="modal fade" id="newIncidentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="/admin/township-security/incident" class="modal-content border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Log Security / Patrol Incident</h5>
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
                        <input type="date" name="incident_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Time</label>
                        <input type="time" name="incident_time" class="form-control" value="<?= date('H:i') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Category *</label>
                        <select name="category" class="form-select" required>
                            <option value="unauthorized_trespass">Unauthorized Trespass</option>
                            <option value="vehicle_parking_dispute">Vehicle Parking Dispute</option>
                            <option value="street_light_blackout">Street Light Blackout</option>
                            <option value="stray_cattle_intrusion">Stray Cattle Intrusion</option>
                            <option value="gate_boom_barrier_hit">Boom Barrier Hit</option>
                            <option value="suspicious_activity" selected>Suspicious Activity</option>
                            <option value="other">Other Incident</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Severity Level</label>
                        <select name="severity" class="form-select">
                            <option value="low">Low (Routine Notice)</option>
                            <option value="medium" selected>Medium (Action Required)</option>
                            <option value="high">High (Immediate Response)</option>
                            <option value="critical">Critical (Breach / Alert)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Location Detail *</label>
                        <input type="text" name="location_detail" class="form-control" placeholder="e.g. Sector B Boundary Wall" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-semibold">Incident Description *</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Describe the incident, people involved, time, vehicle numbers..." required></textarea>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Immediate Action Taken</label>
                        <input type="text" name="action_taken" class="form-control" placeholder="e.g. Guard dispatched, warning issued, trespasser escorted out.">
                    </div>
                    <div class="col-md-4 d-flex align-items-center mt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="police_informed" value="1" id="policeInformed">
                            <label class="form-check-label small fw-semibold text-danger" for="policeInformed">
                                UP Police / Dial 112 Informed
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-exclamation-triangle me-1"></i> Submit Incident Log</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Resolve Incident -->
<div class="modal fade" id="resolveIncidentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="/admin/township-security/resolve" class="modal-content border-0 shadow">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="incident_id" id="resolveIncidentId">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Resolve Incident <span id="resolveTokenDisplay"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <label class="form-label small fw-semibold">Resolution Notes &amp; Engineering/Security Remarks *</label>
                <textarea name="resolution_notes" class="form-control" rows="3" placeholder="Detail action completed, repairs made, warning given, or case closed..." required></textarea>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-1"></i> Close &amp; Archive Incident</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(id, token) {
    document.getElementById('resolveIncidentId').value = id;
    document.getElementById('resolveTokenDisplay').textContent = token;
    const modal = new bootstrap.Modal(document.getElementById('resolveIncidentModal'));
    modal.show();
}
</script>
