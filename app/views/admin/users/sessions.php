<?php $layout = "admin/layouts/admin"; $active_page = "index"; ?>
<?php $csrf = $_SESSION['csrf_token'] ?? ''; ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Active Sessions: <?= htmlspecialchars($user['name'] ?? 'User') ?></h1>
        <p class="text-muted mb-0">
            <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>Back to User
            </a> &middot;
            <strong><?= htmlspecialchars($user['email'] ?? '') ?></strong> (<?= ucfirst($user['role'] ?? 'user') ?>)
        </p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-danger" onclick="revokeAllSessions(<?= $user['id'] ?>)" id="revokeAllBtn">
            <i class="fas fa-sign-out-alt me-2"></i>Revoke All Sessions
        </button>
    </div>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo e($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Sessions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4">#</th>
                        <th class="border-0">Device / Browser</th>
                        <th class="border-0">IP Address</th>
                        <th class="border-0">Location</th>
                        <th class="border-0">Login Time</th>
                        <th class="border-0">Last Activity</th>
                        <th class="border-0">Current</th>
                        <th class="border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($sessions)): ?>
                    <?php foreach ($sessions as $index => $session): ?>
                    <?php
                        $isCurrent = isset($_SESSION['session_id']) && $_SESSION['session_id'] === $session['session_token'];
                        $ua = json_decode($session['user_agent'] ?? '{}', true);
                        $browser = $ua['browser'] ?? 'Unknown';
                        $os = $ua['os'] ?? 'Unknown';
                        $device = $ua['device'] ?? $session['device_type'] ?? 'desktop';
                        $isActive = !empty($session['is_active']);
                    ?>
                    <tr>
                        <td class="ps-4"><?= $index + 1 ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-<?= $device === 'mobile' ? 'mobile-alt' : ($device === 'tablet' ? 'tablet-alt' : 'desktop') ?> me-2 text-muted"></i>
                                <div>
                                    <strong><?= htmlspecialchars($browser) ?></strong> / <?= htmlspecialchars($os) ?>
                                    <br><small class="text-muted"><?= htmlspecialchars($device) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($session['ip_address'] ?? 'Unknown') ?></td>
                        <td>
                            <?php if (!empty($session['location'])): ?>
                            <small><?= htmlspecialchars($session['location']) ?></small>
                            <?php else: ?>
                            <span class="text-muted">Unknown</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y H:i', strtotime($session['created_at'] ?? 'now')) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($session['last_activity'] ?? 'now')) ?></td>
                        <td>
                            <?php if ($isCurrent && $isActive): ?>
                            <span class="badge bg-success"><i class="fas fa-circle me-1"></i>Current Session</span>
                            <?php elseif ($isActive): ?>
                            <span class="badge bg-info">Active</span>
                            <?php else: ?>
                            <span class="badge bg-secondary">Expired</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <?php if (!$isCurrent && $isActive): ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="revokeSession(<?= $session['id'] ?>, this)" title="Revoke Session">
                                <i class="fas fa-times"></i> Revoke
                            </button>
                            <?php elseif ($isCurrent): ?>
                            <span class="text-muted small">Current</span>
                            <?php else: ?>
                            <span class="text-muted small">Expired</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-sign-in-alt fa-3x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No active sessions</h5>
                            <p class="text-muted mb-0">This user has no active login sessions.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF = '<?= $csrf ?>';

function revokeSession(sessionId, btn) {
    if (!confirm('Revoke this session? User will be logged out from this device.')) return;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch(BASE_URL + '/admin/users/sessions/' + sessionId + '/revoke', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent(CSRF)
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('Session revoked', 'success');
            btn.closest('tr').remove();
        } else {
            showToast(d.message || 'Failed', 'danger');
        }
    }).catch(() => showToast('Network error', 'danger')).finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-times"></i> Revoke';
    });
}

function revokeAllSessions(userId) {
    if (!confirm('Revoke ALL sessions for this user? They will be logged out from all devices.')) return;
    
    const btn = document.getElementById('revokeAllBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Revoking...';
    
    fetch(BASE_URL + '/admin/users/' + userId + '/sessions/revoke-all', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent(CSRF)
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast('All sessions revoked', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(d.message || 'Failed', 'danger');
        }
    }).catch(() => showToast('Network error', 'danger')).finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sign-out-alt me-2"></i>Revoke All Sessions';
    });
}
</script>