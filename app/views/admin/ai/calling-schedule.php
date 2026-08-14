<?php
$page_title = $page_title ?? 'Call Scheduling';
$todayScheduled = $todayScheduled ?? [];
$todayCount = $todayCount ?? 0;
$pendingToday = $pendingToday ?? 0;
$completedToday = $completedToday ?? 0;
$upcoming = $upcoming ?? [];
$recentCompleted = $recentCompleted ?? [];
$totalScheduled = $totalScheduled ?? 0;
$totalPending = $totalPending ?? 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>Call Scheduling</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-agents/sessions" class="btn btn-outline-primary btn-sm"><i class="fas fa-history me-1"></i>All Sessions</a>
            <a href="<?= BASE_URL ?>/admin/voice-agents" class="btn btn-outline-primary btn-sm"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-calendar-day"></i></span></div>
                    <div><div class="aps-cp-stat-label">Today's Calls</div><div class="aps-cp-stat-value"><?= $todayCount ?></div><div class="aps-cp-stat-meta">Pending: <?= $pendingToday ?> | Done: <?= $completedToday ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-clock"></i></span></div>
                    <div><div class="aps-cp-stat-label">Upcoming</div><div class="aps-cp-stat-value"><?= count($upcoming) ?></div><div class="aps-cp-stat-meta">Total pending: <?= $totalPending ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                    <div><div class="aps-cp-stat-label">Completed Today</div><div class="aps-cp-stat-value text-success"><?= $completedToday ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-secondary rounded-pill p-2"><i class="fas fa-list"></i></span></div>
                    <div><div class="aps-cp-stat-label">Total Scheduled</div><div class="aps-cp-stat-value"><?= $totalScheduled ?></div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-7">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-calendar-day me-2"></i>Today's Schedule (<?= date('d M Y') ?>)</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($todayScheduled)): ?>
                        <div class="text-center text-muted py-4"><i class="fas fa-calendar fa-2x mb-2"></i><p>No calls scheduled for today</p></div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Time</th><th>Lead</th><th>Phone</th><th>Priority</th><th>Attempts</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($todayScheduled as $s): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($s['scheduled_time']) ?></strong></td>
                                        <td><?= htmlspecialchars($s['lead_name'] ?? 'N/A') ?></td>
                                        <td><code class="small"><?= htmlspecialchars($s['phone']) ?></code></td>
                                        <td><span class="aps-cp-badge badge bg-<?= $s['priority'] === 'urgent' ? 'danger' : ($s['priority'] === 'high' ? 'warning' : ($s['priority'] === 'medium' ? 'info' : 'secondary')) ?>"><?= ucfirst(htmlspecialchars($s['priority'])) ?></span></td>
                                        <td><?= $s['attempt_count'] ?> / <?= $s['max_attempts'] ?></td>
                                        <td><span class="aps-cp-badge badge bg-<?= $s['status'] === 'completed' ? 'success' : ($s['status'] === 'failed' ? 'danger' : ($s['status'] === 'processing' ? 'info' : 'warning')) ?>"><?= ucfirst(htmlspecialchars($s['status'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-clock me-2"></i>Upcoming Calls</div>
                <div class="aps-cp-card-body" class="style-82023">
                    <?php if (empty($upcoming)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No upcoming calls</div>
                    <?php else: ?>
                        <?php foreach ($upcoming as $u): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                <div><strong class="small"><?= htmlspecialchars($u['lead_name'] ?? 'N/A') ?></strong><br><small class="text-muted"><?= htmlspecialchars($u['scheduled_date']) ?> at <?= htmlspecialchars($u['scheduled_time']) ?></small></div>
                                <span class="aps-cp-badge badge bg-<?= $u['priority'] === 'urgent' ? 'danger' : 'info' ?>"><?= ucfirst(htmlspecialchars($u['priority'])) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-check-circle me-2"></i>Recently Completed</div>
                <div class="aps-cp-card-body" class="style-68333">
                    <?php if (empty($recentCompleted)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No completed calls</div>
                    <?php else: ?>
                        <?php foreach ($recentCompleted as $rc): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div><strong class="small"><?= htmlspecialchars($rc['lead_name'] ?? 'N/A') ?></strong><br><small class="text-muted"><?= $rc['duration_seconds'] ? round($rc['duration_seconds']/60,1).'m' : '-' ?></small></div>
                                <span class="aps-cp-badge badge bg-<?= ($rc['customer_response'] ?? '') === 'interested' ? 'success' : 'secondary' ?>"><?= ucfirst(htmlspecialchars($rc['customer_response'] ?? '-')) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
