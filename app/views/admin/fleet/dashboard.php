<?php
/** @var array $stats */
/** @var array $trips */
/** @var array $perAssociate */
$stats = $stats ?? [];
$trips = $trips ?? [];
$perAssociate = $perAssociate ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$statusColors = ['scheduled' => 'info', 'in_progress' => 'warning', 'completed' => 'success', 'cancelled' => 'secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-bus me-2"></i>Sunday Mega Site-Visit Fleet Manager</h4>
        <p class="text-muted mb-0">Cabs, pickups, associates &amp; on-spot token conversion — live counter</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/admin/site-visits/fleet/trips" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Schedule Trip</a>
        <a href="<?= $base ?>/admin/site-visits/fleet/vehicles" class="btn btn-outline-secondary btn-sm"><i class="fas fa-car me-1"></i>Vehicles</a>
        <a href="<?= $base ?>/admin/site-visits" class="btn btn-outline-secondary btn-sm"><i class="fas fa-list me-1"></i>All Visits</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
            <div class="fw-bold fs-3 text-primary"><?= (int)($stats['trips_today'] ?? 0) ?></div>
            <div class="text-muted small">Trips Today</div>
        </div></div>
    </div>
    <div class="col-md-2">
        <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
            <div class="fw-bold fs-3 text-info"><?= (int)($stats['trips_sunday'] ?? 0) ?></div>
            <div class="text-muted small">Trips Next Sunday</div>
        </div></div>
    </div>
    <div class="col-md-2">
        <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
            <div class="fw-bold fs-3"><?= (int)($stats['visited'] ?? 0) ?></div>
            <div class="text-muted small">Visited (30d)</div>
        </div></div>
    </div>
    <div class="col-md-2">
        <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
            <div class="fw-bold fs-3 text-success"><?= (int)($stats['tokens'] ?? 0) ?></div>
            <div class="text-muted small">On-Spot Tokens (30d)</div>
        </div></div>
    </div>
    <div class="col-md-2">
        <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
            <div class="fw-bold fs-3 text-warning"><?= (float)($stats['conversion'] ?? 0) ?>%</div>
            <div class="text-muted small">Conversion</div>
        </div></div>
    </div>
    <div class="col-md-2">
        <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
            <div class="fw-bold fs-3 text-secondary"><?= (int)($stats['vehicles'] ?? 0) ?></div>
            <div class="text-muted small">Active Vehicles</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header"><span><i class="fas fa-route me-2"></i>Upcoming Trips</span></div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($trips)): ?>
                    <div class="text-center text-muted py-4">No upcoming trips. Schedule the Sunday fleet now.</div>
                <?php else: ?>
                <div class="table-responsive"><table class="table table-hover mb-0">
                    <thead class="table-light"><tr>
                        <th>Date</th><th>Pickup → Colony</th><th>Vehicle / Driver</th><th>Associate</th><th>Plan</th><th>Status</th><th></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($trips as $t): ?>
                        <tr>
                            <td class="fw-semibold"><?= date('d M Y (D)', strtotime($t['trip_date'])) ?></td>
                            <td><div class="small"><?= htmlspecialchars($t['pickup_point'] ?: '—') ?></div>
                                <small class="text-muted">→ <?= htmlspecialchars($t['colony_name'] ?: '—') ?></small></td>
                            <td><div class="small fw-medium"><?= htmlspecialchars($t['vehicle_name'] ?: '—') ?> <?= htmlspecialchars($t['vehicle_no'] ?: '') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($t['driver_name'] ?: '') ?> <?= htmlspecialchars($t['driver_phone'] ?: '') ?></small></td>
                            <td><?= htmlspecialchars($t['associate_name'] ?: '—') ?></td>
                            <td><span class="badge bg-light text-dark border"><?= (int)$t['customers_planned'] ?> planned</span></td>
                            <td><span class="badge bg-<?= $statusColors[$t['status']] ?? 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $t['status'])) ?></span></td>
                            <td>
                                <?php if ($t['status'] !== 'completed' && $t['status'] !== 'cancelled'): ?>
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#outcomeModal<?= (int)$t['id'] ?>">Close</button>
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
    <div class="col-lg-4">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header"><span><i class="fas fa-trophy me-2"></i>Associate Conversion (30d)</span></div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($perAssociate)): ?>
                    <div class="text-center text-muted py-4 small">No completed trips yet.</div>
                <?php else: ?>
                <div class="table-responsive"><table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Associate</th><th class="text-end">Trips</th><th class="text-end">Visited</th><th class="text-end">Tokens</th><th class="text-end">Conv.</th></tr></thead>
                    <tbody>
                    <?php foreach ($perAssociate as $a):
                        $conv = ((int)$a['visited'] > 0) ? round(((int)$a['tokens'] * 100) / (int)$a['visited'], 1) : 0; ?>
                        <tr>
                            <td><?= htmlspecialchars($a['associate_name'] ?: ('#' . $a['associate_id'])) ?></td>
                            <td class="text-end"><?= (int)$a['trips'] ?></td>
                            <td class="text-end"><?= (int)$a['visited'] ?></td>
                            <td class="text-end fw-bold text-success"><?= (int)$a['tokens'] ?></td>
                            <td class="text-end"><?= $conv ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php foreach ($trips as $t): if (($t['status'] ?? '') === 'completed' || ($t['status'] ?? '') === 'cancelled') continue; ?>
<div class="modal fade" id="outcomeModal<?= (int)$t['id'] ?>" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?= $base ?>/admin/site-visits/fleet/trips/<?= (int)$t['id'] ?>/outcome">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <div class="modal-header"><h6 class="modal-title">Close Trip — <?= date('d M Y', strtotime($t['trip_date'])) ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body row g-3">
                <div class="col-6"><label class="form-label small">Customers Visited</label>
                    <input type="number" min="0" name="customers_visited" class="form-control" value="<?= (int)$t['customers_planned'] ?>"></div>
                <div class="col-6"><label class="form-label small">On-Spot Tokens</label>
                    <input type="number" min="0" name="tokens_collected" class="form-control" value="0"></div>
                <div class="col-12"><label class="form-label small">Outcome Notes</label>
                    <textarea name="outcome_notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success btn-sm">Close &amp; Count Conversion</button>
            </div>
        </form>
    </div></div>
</div>
<?php endforeach; ?>
