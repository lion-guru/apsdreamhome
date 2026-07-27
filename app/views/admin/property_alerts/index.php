<?php
$page_title = $page_title ?? 'Property Alert Subscriptions';
$page_heading = $page_heading ?? 'Property Alerts';
$content = $content ?? '';
$stats = $stats ?? [];
$subscriptions = $subscriptions ?? [];
$recent_notifications = $recent_notifications ?? [];
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Property Alert Subscriptions</h2>
            <p class="text-muted mb-0">Customers subscribed to property alerts and notification history</p>
        </div>
        <a href="<?= BASE_URL ?>/property-alerts/subscribe" target="_blank" class="btn btn-primary">
            <i class="fas fa-external-link-alt me-1"></i> View Public Page
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Total Subscriptions</p>
                            <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                            <i class="fas fa-bell fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Active</p>
                            <h3 class="mb-0 text-success"><?= number_format($stats['active'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 text-success rounded p-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Daily Alerts</p>
                            <h3 class="mb-0 text-info"><?= number_format($stats['daily'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 text-info rounded p-3">
                            <i class="fas fa-calendar-day fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small">Notifications Sent</p>
                            <h3 class="mb-0 text-warning"><?= number_format($stats['notifications_sent'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                            <i class="fas fa-paper-plane fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="mb-3"><i class="fas fa-bolt text-warning me-2"></i>Instant Alerts</h6>
                    <h4><?= number_format($stats['instant'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="mb-3"><i class="fas fa-calendar-week text-info me-2"></i>Weekly Alerts</h6>
                    <h4><?= number_format($stats['weekly'] ?? 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="mb-3"><i class="fas fa-chart-pie text-primary me-2"></i>Top Property Types</h6>
                    <?php if (!empty($stats['top_property_types'])): ?>
                        <?php foreach (array_slice($stats['top_property_types'], 0, 3) as $pt): ?>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><?= htmlspecialchars($pt['property_type'] ?? 'N/A') ?></span>
                                <span class="badge bg-primary"><?= $pt['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small mb-0">No data yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Active Subscriptions</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Listing</th>
                            <th>Location</th>
                            <th>Price Range</th>
                            <th>Frequency</th>
                            <th>Channels</th>
                            <th>Notified</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($subscriptions)): ?>
                            <tr><td colspan="12" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No subscriptions yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($subscriptions as $sub): ?>
                                <tr>
                                    <td><?= $sub['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($sub['name'] ?? 'N/A') ?></strong>
                                        <?php if ($sub['user_id']): ?>
                                            <br><small class="text-muted">UID: <?= $sub['user_id'] ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-muted"></i> <?= htmlspecialchars($sub['email']) ?>
                                        <?php if ($sub['phone']): ?>
                                            <br><i class="fas fa-phone text-muted"></i> <?= htmlspecialchars($sub['phone']) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($sub['property_type'] ?? 'any') ?></span></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($sub['listing_type'] ?? 'any') ?></span></td>
                                    <td>
                                        <?= htmlspecialchars($sub['city'] ?? 'any city') ?>
                                        <?php if ($sub['state']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($sub['state']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($sub['min_price'] || $sub['max_price']): ?>
                                            <?= $sub['min_price'] ? '₹' . number_format($sub['min_price']) : '₹0' ?>
                                            -
                                            <?= $sub['max_price'] ? '₹' . number_format($sub['max_price']) : '∞' ?>
                                        <?php else: ?>
                                            <span class="text-muted">Any</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $sub['frequency'] === 'instant' ? 'warning' : ($sub['frequency'] === 'daily' ? 'info' : 'secondary') ?>">
                                            <?= ucfirst($sub['frequency']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($sub['notify_email']): ?><i class="fas fa-envelope text-primary" title="Email"></i><?php endif; ?>
                                        <?php if ($sub['notify_sms']): ?><i class="fas fa-sms text-info ms-1" title="SMS"></i><?php endif; ?>
                                        <?php if ($sub['notify_whatsapp']): ?><i class="fab fa-whatsapp text-success ms-1" title="WhatsApp"></i><?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= $sub['total_notifications'] ?? 0 ?></strong>
                                        <?php if ($sub['last_notified_at']): ?>
                                            <br><small class="text-muted"><?= date('M j', strtotime($sub['last_notified_at'])) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" <?= $sub['is_active'] ? 'checked' : '' ?>
                                                   onchange="toggleSub(<?= $sub['id'] ?>, this.checked ? 1 : 0)">
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="testMatch(<?= $sub['id'] ?>)" title="Test match">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <a href="<?= BASE_URL ?>/admin/property-alerts/delete?id=<?= $sub['id'] ?>" class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Delete this subscription?')" title="Delete">
                                            <i class="fas fa-trash"></i>
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

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Notifications</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Subscriber</th>
                            <th>Property ID</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_notifications)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No notifications sent yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_notifications as $n): ?>
                                <tr>
                                    <td><small><?= date('M j, H:i', strtotime($n['created_at'])) ?></small></td>
                                    <td><?= htmlspecialchars($n['sub_name'] ?? 'Unknown') ?></td>
                                    <td><?= $n['property_id'] ?></td>
                                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($n['channel']) ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= $n['status'] === 'sent' ? 'success' : ($n['status'] === 'failed' ? 'danger' : 'warning') ?>">
                                            <?= ucfirst($n['status']) ?>
                                        </span>
                                    </td>
                                    <td><small class="text-muted"><?= htmlspecialchars(substr($n['message'] ?? '', 0, 60)) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSub(id, active) {
    const fd = new FormData();
    fd.append('id', id);
    fd.append('active', active);
    fetch('<?= BASE_URL ?>/admin/property-alerts/toggle', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => { if (!d.success) alert('Update failed'); });
}
function testMatch(id) {
    fetch('<?= BASE_URL ?>/admin/property-alerts/test-match?id=' + id)
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert('Found ' + d.count + ' matching properties for this subscription');
            } else {
                alert('Error: ' + (d.error || 'Unknown'));
            }
        });
}
</script>

<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
