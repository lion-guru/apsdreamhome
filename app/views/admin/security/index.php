<?php $page_title = $page_title ?? 'Security Dashboard';
$blockedCount = $blockedCount ?? 0;
$activeBlocks = $activeBlocks ?? 0;
$failed24h = $failed24h ?? 0;
$failed7d = $failed7d ?? 0;
$tfaEnabled = $tfaEnabled ?? 0;
$totalUsers = $totalUsers ?? 0;
$recentEvents = $recentEvents ?? [];
$topIPs = $topIPs ?? [];
$recentBlocked = $recentBlocked ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-shield-alt me-2 text-danger"></i>Security Dashboard</h2>
        <div>
            <button onclick="location.reload()" class="btn btn-outline-primary"><i class="fas fa-sync me-1"></i>Refresh</button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-danger rounded-pill p-2"><i class="fas fa-ban"></i></span></div>
                        <div><div class="aps-cp-stat-label">Blocked IPs</div><div class="aps-cp-stat-value"><?= $blockedCount ?></div><div class="aps-cp-stat-meta">Active: <?= $activeBlocks ?></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-exclamation-triangle"></i></span></div>
                        <div><div class="aps-cp-stat-label">Failed Logins (24h)</div><div class="aps-cp-stat-value"><?= $failed24h ?></div><div class="aps-cp-stat-meta">7 days: <?= $failed7d ?></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-user-shield"></i></span></div>
                        <div><div class="aps-cp-stat-label">2FA Enabled</div><div class="aps-cp-stat-value"><?= $tfaEnabled ?></div><div class="aps-cp-stat-meta">of <?= $totalUsers ?> users</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-users"></i></span></div>
                        <div><div class="aps-cp-stat-label">Total Users</div><div class="aps-cp-stat-value"><?= $totalUsers ?></div><div class="aps-cp-stat-meta"><?= $totalUsers > 0 ? round($tfaEnabled/$totalUsers*100,1) : 0 ?>% 2FA adoption</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Recent Security Events</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($recentEvents)): ?>
                        <div class="text-center text-muted py-4"><i class="fas fa-check-circle fa-2x mb-2 text-success"></i><p>No recent security events</p></div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>User</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th></tr></thead>
                                <tbody>
                                <?php foreach ($recentEvents as $ev): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($ev['name'] ?? 'System') ?></strong></td>
                                        <td><span class="aps-cp-badge badge bg-<?= in_array($ev['action'] ?? '', ['login_failed','blocked']) ? 'danger' : (in_array($ev['action'] ?? '', ['login']) ? 'success' : 'info') ?>"><?= htmlspecialchars($ev['action'] ?? '') ?></span></td>
                                        <td class="text-muted small"><?= htmlspecialchars(mb_substr($ev['details'] ?? '', 0, 80)) ?></td>
                                        <td><code><?= htmlspecialchars($ev['ip_address'] ?? '') ?></code></td>
                                        <td class="text-muted small"><?= htmlspecialchars($ev['created_at'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-network-wired me-2"></i>Top Offending IPs (7d)</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($topIPs)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-check-circle me-1 text-success"></i>No failed attempts</div>
                    <?php else: ?>
                        <?php foreach ($topIPs as $ip): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <code class="small"><?= htmlspecialchars($ip['ip_address'] ?? '') ?></code>
                                <span class="badge bg-danger"><?= $ip['cnt'] ?> attempts</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-ban me-2"></i>Recently Blocked IPs</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($recentBlocked)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No blocked IPs</div>
                    <?php else: ?>
                        <?php foreach ($recentBlocked as $bl): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div><code class="small"><?= htmlspecialchars($bl['ip_address'] ?? '') ?></code><br><small class="text-muted"><?= htmlspecialchars($bl['reason'] ?? 'No reason') ?></small></div>
                                <span class="aps-cp-badge badge bg-<?= ($bl['unblocked_at'] ?? null) ? 'secondary' : 'danger' ?>"><?= ($bl['unblocked_at'] ?? null) ? 'Unblocked' : 'Active' ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
