<?php $pageTitle = $page_title ?? 'IoT Device Management'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-wifi me-2"></i><?= htmlspecialchars($pageTitle ?? '') ?></h4>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal"><i class="fas fa-plus me-1"></i>Add Device</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-info mb-2"><i class="fas fa-microchip"></i></div>
                    <h5 class="mb-1"><?= count($devices ?? []) ?></h5>
                    <small class="text-muted">Total Devices</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 text-warning mb-2"><i class="fas fa-list"></i></div>
                    <h5 class="mb-1"><?= count($device_types ?? []) ?></h5>
                    <small class="text-muted">Device Types</small>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Devices</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light"><tr><th>ID</th><th>Name</th><th>Type</th><th>Property</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (!empty($devices)): ?>
                            <?php foreach ($devices as $d): ?>
                                <tr>
                                    <td><?= $d['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($d['device_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($d['device_type_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($d['property_title'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($d['location'] ?? '-') ?></td>
                                    <td><span class="badge bg-<?= ($d['status'] ?? 'offline') === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($d['status'] ?? 'offline') ?></span></td>
                                    <td><a href="<?= ($base ?? BASE_URL) ?>iot/device/<?= $d['id'] ?? 0 ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-3">No devices registered</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
