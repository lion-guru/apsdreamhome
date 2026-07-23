<?php
$stats = $stats ?? [];
$devices = $devices ?? [];
$automations = $automations ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-microchip me-2 text-primary"></i>IoT Smart Property</h2>
    <div>
        <a href="<?= BASE_URL ?>/admin/iot/devices" class="btn btn-outline-primary me-2"><i class="fas fa-server me-1"></i> Devices</a>
        <a href="<?= BASE_URL ?>/admin/iot/automations" class="btn btn-outline-success"><i class="fas fa-robot me-1"></i> Automations</a>
    </div>
</div>

<div class="row">
    <div class="col-md-2 mb-3"><div class="card bg-primary text-white"><div class="card-body"><h6 class="small text-uppercase">Total Devices</h6><h3><?= $stats['total_devices'] ?? 0 ?></h3></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-success text-white"><div class="card-body"><h6 class="small text-uppercase">Online</h6><h3><?= $stats['online_devices'] ?? 0 ?></h3></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-warning text-white"><div class="card-body"><h6 class="small text-uppercase">Offline</h6><h3><?= $stats['offline_devices'] ?? 0 ?></h3></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-danger text-white"><div class="card-body"><h6 class="small text-uppercase">Fault</h6><h3><?= $stats['fault_devices'] ?? 0 ?></h3></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-info text-white"><div class="card-body"><h6 class="small text-uppercase">Automations</h6><h3><?= $stats['total_automations'] ?? 0 ?></h3></div></div></div>
    <div class="col-md-2 mb-3"><div class="card bg-dark text-white"><div class="card-body"><h6 class="small text-uppercase">Readings</h6><h3><?= number_format($stats['total_readings'] ?? 0) ?></h3></div></div></div>
</div>

<div class="row">
    <div class="col-lg-7 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between"><h6 class="mb-0">Recent Devices</h6><a href="<?= BASE_URL ?>/admin/iot/devices" class="small">View all</a></div>
            <div class="card-body p-0">
                <?php if (empty($devices)): ?>
                    <p class="text-muted text-center py-3">No devices registered.</p>
                <?php else: ?>
                    <table class="table mb-0"><tbody>
                    <?php foreach ($devices as $d): ?>
                        <tr>
                            <td><i class="fas fa-microchip me-2 text-muted"></i><strong><?= htmlspecialchars($d['name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($d['location'] ?? '') ?></small></td>
                            <td class="text-end"><span class="badge bg-<?= match($d['status'] ?? 'offline') { 'online' => 'success', 'fault' => 'danger', 'configuring' => 'warning', default => 'secondary' } ?>"><?= ucfirst($d['status'] ?? 'offline') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between"><h6 class="mb-0">Active Automations</h6><a href="<?= BASE_URL ?>/admin/iot/automations" class="small">View all</a></div>
            <div class="card-body p-0">
                <?php if (empty($automations)): ?>
                    <p class="text-muted text-center py-3">No automations yet.</p>
                <?php else: ?>
                    <table class="table mb-0"><tbody>
                    <?php foreach ($automations as $a): ?>
                        <tr><td><strong><?= htmlspecialchars($a['name']) ?></strong><br><small class="text-muted"><?= ucfirst($a['trigger_type']) ?> → <?= ucfirst($a['action_type']) ?></small></td>
                        <td class="text-end"><span class="badge bg-<?= ($a['is_active'] ?? 0) ? 'success' : 'secondary' ?>"><?= ($a['is_active'] ?? 0) ? 'On' : 'Off' ?></span></td></tr>
                    <?php endforeach; ?>
                    </tbody></table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white"><i class="fas fa-plus me-1"></i> Quick Actions</div>
            <div class="card-body d-grid gap-2 d-md-block">
                <a href="<?= BASE_URL ?>/admin/iot/catalog" class="btn btn-outline-primary"><i class="fas fa-th-list me-1"></i> Device Catalog</a>
                <a href="<?= BASE_URL ?>/admin/iot/device/form" class="btn btn-outline-primary"><i class="fas fa-plus me-1"></i> Register Device</a>
                <a href="<?= BASE_URL ?>/admin/iot/automation/form" class="btn btn-outline-success"><i class="fas fa-plus me-1"></i> New Automation</a>
            </div>
        </div>
    </div>
</div>
