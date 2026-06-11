<?php $pageTitle = $page_title ?? 'Security Monitoring'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
        <span class="badge bg-<?= ($security_data['system_status'] ?? 'disarmed') === 'armed' ? 'danger' : 'warning' ?> fs-6"><?= strtoupper($security_data['system_status'] ?? 'UNKNOWN') ?></span>
    </div>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
        <div class="card-body aps-cp-card-body">
            <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
            <p class="mb-0 text-muted"><?= htmlspecialchars($property['city'] ?? '') ?></p>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-door-open me-2"></i>Door Status</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach (($security_data['door_status'] ?? []) as $door => $status): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= ucfirst(str_replace('_', ' ', $door)) ?></span>
                            <span class="badge bg-<?= $status === 'locked' ? 'success' : 'warning' ?>"><?= ucfirst($status) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-video me-2"></i>Camera Status</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php foreach (($security_data['camera_status'] ?? []) as $cam => $status): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= ucfirst(str_replace('_', ' ', $cam)) ?></span>
                            <span class="badge bg-<?= $status === 'online' ? 'success' : 'danger' ?>"><?= ucfirst($status) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-bell me-2"></i>Recent Alerts</h5></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (!empty($security_data['recent_alerts'])): ?>
                            <?php foreach ($security_data['recent_alerts'] as $a): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><i class="fas fa-exclamation-triangle text-warning me-2"></i><?= htmlspecialchars($a['location'] ?? '') ?></span>
                                    <small class="text-muted"><?= htmlspecialchars($a['timestamp'] ?? '') ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted text-center">No recent alerts</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
