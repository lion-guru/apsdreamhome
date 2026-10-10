<?php
/** @var array $trips */
/** @var array $vehicles */
/** @var array $colonies */
/** @var array $associates */
$trips = $trips ?? []; $vehicles = $vehicles ?? []; $colonies = $colonies ?? []; $associates = $associates ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$pickups = ['Railway Station', 'Golghar', 'Medical Road', 'Betiahata', 'Taramandal', 'Naukayan'];
$statusColors = ['scheduled' => 'info', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-route me-2"></i>Fleet Trips</h4>
        <p class="text-muted mb-0">Schedule Sunday pickups, assign cabs &amp; associates</p>
    </div>
    <a href="<?= $base ?>/admin/site-visits/fleet" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Fleet Dashboard</a>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header"><span><i class="fas fa-calendar-plus me-2"></i>Schedule Trip</span></div>
            <div class="aps-cp-card-body">
                <form method="POST" action="<?= $base ?>/admin/site-visits/fleet/trips/store" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="col-6"><label class="form-label small">Trip Date</label>
                        <input type="date" name="trip_date" class="form-control form-control-sm" value="<?= date('Y-m-d', strtotime('next sunday')) ?>" required></div>
                    <div class="col-6"><label class="form-label small">Vehicle</label>
                        <select name="vehicle_id" class="form-select form-select-sm">
                            <option value="0">— No vehicle —</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['vehicle_name'] . ' (' . $v['vehicle_no'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-6"><label class="form-label small">Driver (override)</label>
                        <input type="text" name="driver_name" class="form-control form-control-sm" placeholder="auto from vehicle"></div>
                    <div class="col-6"><label class="form-label small">Driver Phone</label>
                        <input type="text" name="driver_phone" class="form-control form-control-sm" placeholder="auto from vehicle"></div>
                    <div class="col-12"><label class="form-label small">Pickup Point</label>
                        <input type="text" name="pickup_point" class="form-control form-control-sm" list="pickupList" placeholder="e.g. Railway Station" required>
                        <datalist id="pickupList">
                            <?php foreach ($pickups as $p): ?><option value="<?= htmlspecialchars($p) ?>"><?php endforeach; ?>
                        </datalist></div>
                    <div class="col-12"><label class="form-label small">Colony</label>
                        <select name="colony_id" class="form-select form-select-sm">
                            <option value="0">— Select —</option>
                            <?php foreach ($colonies as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-12"><label class="form-label small">Associate</label>
                        <select name="associate_id" class="form-select form-select-sm">
                            <option value="0">— Select —</option>
                            <?php foreach ($associates as $a): ?>
                                <option value="<?= (int)$a['id'] ?>"><?= htmlspecialchars($a['name']) ?> (<?= htmlspecialchars($a['phone'] ?? '') ?>)</option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-6"><label class="form-label small">Customers Planned</label>
                        <input type="number" min="0" name="customers_planned" class="form-control form-control-sm" value="0"></div>
                    <div class="col-12"><label class="form-label small">Notes</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea></div>
                    <div class="col-12"><button class="btn btn-primary btn-sm w-100"><i class="fas fa-check me-1"></i>Schedule</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header"><span><i class="fas fa-list me-2"></i>All Trips (<?= count($trips) ?>)</span></div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($trips)): ?>
                    <div class="text-center text-muted py-4">No trips scheduled yet.</div>
                <?php else: ?>
                <div class="table-responsive"><table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Route</th><th>Cab &amp; Associate</th><th>Funnel</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($trips as $t):
                        $conv = ((int)$t['customers_visited'] > 0) ? round(((int)$t['tokens_collected'] * 100) / (int)$t['customers_visited'], 1) : 0; ?>
                        <tr>
                            <td class="fw-semibold"><?= date('d M Y', strtotime($t['trip_date'])) ?></td>
                            <td><div class="small"><?= htmlspecialchars($t['pickup_point'] ?: '—') ?> → <?= htmlspecialchars($t['colony_name'] ?: '—') ?></div></td>
                            <td><div class="small"><?= htmlspecialchars($t['vehicle_name'] ?: 'No vehicle') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['associate_name'] ?: '—') ?></small></td>
                            <td>
                                <span class="badge bg-light text-dark border" title="Planned"><?= (int)$t['customers_planned'] ?> plan</span>
                                <span class="badge bg-info" title="Visited"><?= (int)$t['customers_visited'] ?> visit</span>
                                <span class="badge bg-success" title="Tokens"><?= (int)$t['tokens_collected'] ?> token</span>
                                <?php if ($t['status'] === 'completed'): ?><span class="badge bg-warning text-dark"><?= $conv ?>%</span><?php endif; ?>
                            </td>
                            <td><span class="badge bg-<?= $statusColors[$t['status']] ?? 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $t['status'])) ?></span></td>
                            <td>
                                <?php if (!in_array($t['status'], ['completed', 'cancelled'], true)): ?>
                                <form method="POST" action="<?= $base ?>/admin/site-visits/fleet/trips/<?= (int)$t['id'] ?>/status" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                        <option value="">Set…</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
