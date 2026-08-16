<?php $pageTitle = $page_title ?? 'Device Control'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-sliders-h me-2"></i><?= htmlspecialchars($pageTitle ?? '') ?></h4>
        <a href="<?= ($base ?? BASE_URL) ?>iot/devices" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Devices</a>
    </div>
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Device Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm table-responsive">
                        <tr><th>Name</th><td><?= htmlspecialchars($device['device_name'] ?? '-') ?></td></tr>
                        <tr><th>Type</th><td><?= htmlspecialchars($device['device_type_name'] ?? '-') ?></td></tr>
                        <tr><th>Location</th><td><?= htmlspecialchars($device['location'] ?? '-') ?></td></tr>
                        <tr><th>Property</th><td><?= htmlspecialchars($device['property_title'] ?? '-') ?></td></tr>
                        <tr><th>Status</th><td><span class="badge bg-<?= ($device['status'] ?? 'offline') === 'active' ? 'success' : 'danger' ?>"><?= ucfirst($device['status'] ?? 'offline') ?></span></td></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Device History</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Command</th><th>User</th><th>Time</th></tr></thead>
                            <tbody>
                                <?php if (!empty($device_history)): ?>
                                    <?php foreach ($device_history as $h): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($h['command'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($h['user_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($h['created_at'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">No history available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
