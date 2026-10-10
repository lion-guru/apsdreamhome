<?php
/** @var array $vehicles */
$vehicles = $vehicles ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$types = ['innova' => 'Innova', 'bolero' => 'Bolero', 'scorpio' => 'Scorpio', 'ertiga' => 'Ertiga', 'traveller' => 'Traveller', 'other' => 'Other'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-car me-2"></i>Fleet Vehicles &amp; Drivers</h4>
        <p class="text-muted mb-0">Cabs for Sunday site-visit pickups</p>
    </div>
    <a href="<?= $base ?>/admin/site-visits/fleet" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Fleet Dashboard</a>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="aps-cp-card mb-4">
            <div class="aps-cp-card-header"><span><i class="fas fa-plus me-2"></i>Add Vehicle</span></div>
            <div class="aps-cp-card-body">
                <form method="POST" action="<?= $base ?>/admin/site-visits/fleet/vehicles/store" class="row g-3">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="col-12"><label class="form-label small">Vehicle Name</label>
                        <input type="text" name="vehicle_name" class="form-control form-control-sm" placeholder="e.g. Innova White" required></div>
                    <div class="col-6"><label class="form-label small">Vehicle No.</label>
                        <input type="text" name="vehicle_no" class="form-control form-control-sm" placeholder="UP53 AB 1234" required></div>
                    <div class="col-6"><label class="form-label small">Type</label>
                        <select name="vehicle_type" class="form-select form-select-sm">
                            <?php foreach ($types as $k => $l): ?><option value="<?= $k ?>"><?= $l ?></option><?php endforeach; ?>
                        </select></div>
                    <div class="col-4"><label class="form-label small">Capacity</label>
                        <input type="number" min="1" max="30" name="capacity" class="form-control form-control-sm" value="6"></div>
                    <div class="col-8"><label class="form-label small">Driver Name</label>
                        <input type="text" name="driver_name" class="form-control form-control-sm"></div>
                    <div class="col-12"><label class="form-label small">Driver Phone</label>
                        <input type="text" name="driver_phone" class="form-control form-control-sm" placeholder="10-digit mobile"></div>
                    <div class="col-12"><button class="btn btn-primary btn-sm w-100"><i class="fas fa-plus me-1"></i>Add to Fleet</button></div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header"><span><i class="fas fa-warehouse me-2"></i>Fleet (<?= count($vehicles) ?>)</span></div>
            <div class="aps-cp-card-body p-0">
                <?php if (empty($vehicles)): ?>
                    <div class="text-center text-muted py-4">No vehicles yet. Add the first cab.</div>
                <?php else: ?>
                <div class="table-responsive"><table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Vehicle</th><th>Type</th><th>Capacity</th><th>Driver</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($vehicles as $v): ?>
                        <tr class="<?= empty($v['is_active']) ? 'table-secondary' : '' ?>">
                            <td><div class="fw-semibold"><?= htmlspecialchars($v['vehicle_name']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($v['vehicle_no']) ?></small></td>
                            <td><?= htmlspecialchars(ucfirst($v['vehicle_type'] ?? '')) ?></td>
                            <td><?= (int)$v['capacity'] ?> seats</td>
                            <td><div class="small"><?= htmlspecialchars($v['driver_name'] ?: '—') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($v['driver_phone'] ?: '') ?></small></td>
                            <td><span class="badge bg-<?= !empty($v['is_active']) ? 'success' : 'secondary' ?>"><?= !empty($v['is_active']) ? 'Active' : 'Inactive' ?></span></td>
                            <td>
                                <form method="POST" action="<?= $base ?>/admin/site-visits/fleet/vehicles/<?= (int)$v['id'] ?>/toggle" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button class="btn btn-sm btn-outline-secondary" title="Toggle active">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
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
