<?php
$page_title = $page_title ?? 'AI Calling Dashboard';
$totalCalls = $totalCalls ?? 0;
$completedCalls = $completedCalls ?? 0;
$failedCalls = $failedCalls ?? 0;
$successRate = $successRate ?? 0;
$avgDuration = $avgDuration ?? 0;
$totalExtracted = $totalExtracted ?? 0;
$callsToday = $callsToday ?? 0;
$callsThisWeek = $callsThisWeek ?? 0;
$interestedCount = $interestedCount ?? 0;
$hotLeads = $hotLeads ?? 0;
$weekLabels = $weekLabels ?? [];
$weekData = $weekData ?? [];
$recentCalls = $recentCalls ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-phone-alt me-2 text-primary"></i>AI Calling Dashboard</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-agents/schedule" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar me-1"></i>Schedule</a>
            <a href="<?= BASE_URL ?>/admin/voice-agents/sessions" class="btn btn-outline-primary btn-sm"><i class="fas fa-list me-1"></i>Sessions</a>
            <a href="<?= BASE_URL ?>/admin/voice-agents/extracted-leads" class="btn btn-primary btn-sm"><i class="fas fa-user-plus me-1"></i>Extracted Leads</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-phone"></i></span></div>
                    <div><div class="aps-cp-stat-label">Total Calls</div><div class="aps-cp-stat-value"><?= number_format($totalCalls) ?></div><div class="aps-cp-stat-meta">Today: <?= $callsToday ?> | Week: <?= $callsThisWeek ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                    <div><div class="aps-cp-stat-label">Success Rate</div><div class="aps-cp-stat-value text-<?= $successRate > 60 ? 'success' : 'warning' ?>"><?= $successRate ?>%</div><div class="aps-cp-stat-meta"><?= $completedCalls ?> completed / <?= $failedCalls ?> failed</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-clock"></i></span></div>
                    <div><div class="aps-cp-stat-label">Avg Duration</div><div class="aps-cp-stat-value"><?= round($avgDuration) ?>s</div><div class="aps-cp-stat-meta"><?= round($avgDuration/60,1) ?> min</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-user-plus"></i></span></div>
                    <div><div class="aps-cp-stat-label">Leads Extracted</div><div class="aps-cp-stat-value"><?= number_format($totalExtracted) ?></div><div class="aps-cp-stat-meta">Hot: <?= $hotLeads ?> | Interested: <?= $interestedCount ?></div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-chart-bar me-2"></i>Calls This Week (14-day trend)</div>
                <div class="aps-cp-card-body"><canvas id="weeklyChart" height="100"></canvas></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-chart-pie me-2"></i>Call Outcomes</div>
                <div class="aps-cp-card-body">
                    <div class="mb-2"><div class="d-flex justify-content-between"><small>Completed</small><small class="text-success"><?= $completedCalls ?></small></div><div class="progress" class="style-32124"><div class="progress-bar bg-success" class="style-8771"></div></div></div>
                    <div class="mb-2"><div class="d-flex justify-content-between"><small>Failed</small><small class="text-danger"><?= $failedCalls ?></small></div><div class="progress" class="style-32124"><div class="progress-bar bg-danger" class="style-92356"></div></div></div>
                    <div class="mb-2"><div class="d-flex justify-content-between"><small>Interested</small><small class="text-primary"><?= $interestedCount ?></small></div><div class="progress" class="style-32124"><div class="progress-bar bg-primary" class="style-5652"></div></div></div>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-history me-2"></i>Recent Calls</div>
        <div class="aps-cp-card-body">
            <?php if (empty($recentCalls)): ?>
                <div class="text-center text-muted py-4"><i class="fas fa-phone fa-2x mb-2"></i><p>No calls recorded yet</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>Lead</th><th>Phone</th><th>Duration</th><th>Response</th><th>Sentiment</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentCalls as $c): ?>
                            <tr>
                                <td>#<?= $c['id'] ?></td>
                                <td><?= htmlspecialchars($c['lead_name'] ?? 'N/A') ?></td>
                                <td><code><?= htmlspecialchars($c['phone']) ?></code></td>
                                <td><?= $c['duration_seconds'] > 0 ? round($c['duration_seconds']/60,1).'m' : '-' ?></td>
                                <td><span class="aps-cp-badge badge bg-<?= $c['customer_response'] === 'interested' ? 'success' : ($c['customer_response'] === 'dnd' ? 'danger' : ($c['customer_response'] === 'callback' ? 'warning' : 'secondary')) ?>"><?= ucfirst(htmlspecialchars($c['customer_response'] ?? 'N/A')) ?></span></td>
                                <td><span class="text-<?= $c['sentiment'] === 'positive' ? 'success' : ($c['sentiment'] === 'negative' ? 'danger' : 'muted') ?>"><?= ucfirst(htmlspecialchars($c['sentiment'] ?? '-')) ?></span></td>
                                <td><span class="aps-cp-badge badge bg-<?= $c['status'] === 'completed' ? 'success' : ($c['status'] === 'failed' ? 'danger' : ($c['status'] === 'in_progress' ? 'info' : 'secondary')) ?>"><?= ucfirst(htmlspecialchars($c['status'])) ?></span></td>
                                <td class="text-muted small"><?= htmlspecialchars(date('d M H:i', strtotime($c['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/vendor/chart.umd.js"></script>
<script>
new Chart(document.getElementById('weeklyChart'), {
    type: 'bar', data: { labels: <?= json_encode($weekLabels) ?>, datasets: [{ label: 'Calls', data: <?= json_encode(array_map('intval', $weekData)) ?>, backgroundColor: '#0d9488aa', borderColor: '#0d9488', borderWidth: 1 }] },
    options: { responsive: true, scales: { y: { beginAtZero: true, title: { display: true, text: 'Number of Calls' } } }, plugins: { legend: { display: false } } }
});
</script>
