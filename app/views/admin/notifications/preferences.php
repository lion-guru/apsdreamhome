<?php
/**
 * Admin Notification Preference Center
 * Allows administrators and managers to configure which notification channels
 * (In-App, Browser Web Push, WhatsApp, Email) are active for each business event.
 */
?>

<div class="container-fluid px-4 py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/notifications" class="text-decoration-none">Notification Center</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Preferences</li>
                </ol>
            </nav>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">
                <i class="fas fa-sliders-h text-primary me-2"></i><?= htmlspecialchars($page_heading ?? 'Notification Preferences') ?>
            </h1>
            <p class="text-muted small mb-0">Control how and where you receive system notifications, buyer leads, and transaction alerts</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/admin/notifications" class="btn btn-outline-secondary btn-sm px-3 shadow-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Notifications
            </a>
            <button class="btn btn-primary btn-sm px-3 shadow-sm" type="submit" form="admin-notif-prefs-form">
                <i class="fas fa-save me-1"></i> Save Preferences
            </button>
        </div>
    </div>

    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= htmlspecialchars($flash_success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($flash_error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Two-column Layout -->
    <div class="row g-4">
        <!-- Main Configuration Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-bell me-2 text-warning"></i>Event Delivery Channels</h5>
                        <small class="text-muted">Toggle specific communication channels for each operational event</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <form method="POST" action="<?= BASE_URL ?>/admin/notification-preferences" id="admin-notif-prefs-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 240px;" class="ps-4">Event Type</th>
                                        <th class="text-center" style="width: 110px;">In-App</th>
                                        <th class="text-center" style="width: 110px;">Web Push</th>
                                        <th class="text-center" style="width: 110px;">Email</th>
                                        <th class="text-center pe-4" style="width: 110px;">WhatsApp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($types as $key => $meta): ?>
                                        <?php $p = $prefs[$key] ?? ['in_app' => true, 'push' => true, 'email' => true, 'whatsapp' => false]; ?>
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-3 p-2 bg-light text-primary me-3 text-center" style="width: 38px; height: 38px;">
                                                        <i class="fas <?= htmlspecialchars($meta['icon']) ?>"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark fs-6"><?= htmlspecialchars($meta['title']) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($meta['desc']) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center py-3">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" name="channels[<?= $key ?>][in_app]" value="1" <?= !empty($p['in_app']) ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                            <td class="text-center py-3">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" name="channels[<?= $key ?>][push]" value="1" <?= !empty($p['push']) ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                            <td class="text-center py-3">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" name="channels[<?= $key ?>][email]" value="1" <?= !empty($p['email']) ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                            <td class="text-center pe-4 py-3">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" name="channels[<?= $key ?>][whatsapp]" value="1" <?= !empty($p['whatsapp']) ? 'checked' : '' ?>>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer bg-light p-3 d-flex justify-content-between align-items-center border-0 border-top">
                            <span class="text-muted small"><i class="fas fa-info-circle me-1"></i> Changes take effect immediately across all devices.</span>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                <i class="fas fa-check me-1"></i> Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Cards: Web Push Status & Auto-Archive Policy -->
        <div class="col-lg-4">
            <!-- Web Push Browser Integration Card -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-desktop text-success me-2"></i>Browser Web Push Status</h6>
                </div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-3">Web Push delivers high-priority alerts to your Windows / macOS desktop even when your browser tab is closed.</p>
                    <div class="p-3 bg-light rounded-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-semibold text-secondary">Browser Permission:</span>
                            <span class="badge bg-secondary" id="push-permission-badge">Checking...</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-semibold text-secondary">VAPID Encryption:</span>
                            <span class="badge bg-success">Active (ES256)</span>
                        </div>
                    </div>
                    <button class="btn btn-outline-success btn-sm w-100 fw-semibold" id="btn-request-web-push" type="button" onclick="notificationSystem && notificationSystem.toggleWebPush()">
                        <i class="fas fa-bell me-1"></i> Test / Enable Desktop Push
                    </button>
                </div>
            </div>

            <!-- Automated 90-Day Archive Card -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-0 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-archive text-warning me-2"></i>Automated Notification Archive</h6>
                    <span class="badge bg-light text-dark border">90 Days</span>
                </div>
                <div class="card-body p-3">
                    <p class="small text-muted mb-3">Read notifications older than 90 days are automatically archived to <code class="text-primary">notifications_archive</code> by the master cron runner.</p>
                    
                    <ul class="list-group list-group-flush small mb-3">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Total Archived Records:</span>
                            <strong class="text-dark"><?= number_format($archive_stats['total_archived'] ?? 0) ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Oldest Record:</span>
                            <span class="text-dark"><?= !empty($archive_stats['oldest_record']) ? htmlspecialchars(date('d M Y', strtotime($archive_stats['oldest_record']))) : 'None' ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Cron Schedule:</span>
                            <span class="badge bg-info-subtle text-info">Task #15 (Daily)</span>
                        </li>
                    </ul>

                    <form method="POST" action="<?= BASE_URL ?>/admin/notifications/archive-now" onsubmit="return confirm('Archive all read notifications older than 90 days now?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="days" value="90">
                        <button type="submit" class="btn btn-warning btn-sm w-100 text-dark fw-semibold">
                            <i class="fas fa-bolt me-1"></i> Run 90-Day Archive Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var badge = document.getElementById('push-permission-badge');
    if (badge && 'Notification' in window) {
        if (Notification.permission === 'granted') {
            badge.className = 'badge bg-success';
            badge.textContent = 'Active (Granted)';
        } else if (Notification.permission === 'denied') {
            badge.className = 'badge bg-danger';
            badge.textContent = 'Blocked by Browser';
        } else {
            badge.className = 'badge bg-warning text-dark';
            badge.textContent = 'Click to Allow';
        }
    }
});
</script>
