<?php $pageTitle = $pageTitle ?? 'IoT Analytics'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-microchip me-2"></i>IoT Device Analytics</h4>
        <button class="btn btn-outline-primary btn-sm" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-wifi"></i></div>
                    <h5 class="mb-1"><?= count($devices ?? []) ?></h5>
                    <small class="text-muted">Total Devices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-success mb-2"><i class="fas fa-plug"></i></div>
                    <h5 class="mb-1"><?= count(array_filter($devices ?? [], fn($d) => ($d['status'] ?? '') === 'online')) ?></h5>
                    <small class="text-muted">Online</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning mb-2"><i class="fas fa-database"></i></div>
                    <h5 class="mb-1"><?= number_format($telemetry['total_readings'] ?? 0) ?></h5>
                    <small class="text-muted">Telemetry Readings</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-danger mb-2"><i class="fas fa-exclamation-triangle"></i></div>
                    <h5 class="mb-1"><?= number_format($telemetry['alerts'] ?? 0) ?></h5>
                    <small class="text-muted">Alerts</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Devices</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Device Name</th><th>Type</th><th>Location</th><th>Status</th><th>Last Reading</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($devices)): ?>
                            <?php foreach ($devices as $d): ?>
                                <tr>
                                    <td><?= $d['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($d['name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($d['type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($d['location'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($d['status'] ?? 'offline') === 'online' ? 'success' : 'secondary' ?>"><?= ucfirst($d['status'] ?? 'offline') ?></span></td>
                                    <td><?= htmlspecialchars($d['last_reading'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No IoT devices registered</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
