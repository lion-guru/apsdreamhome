<?php
$page_title = $page_title ?? 'Call Sessions History';
$sessions = $sessions ?? [];
$agents = $agents ?? [];
$totalSessions = $totalSessions ?? 0;
$totalCompleted = $totalCompleted ?? 0;
$totalFailed = $totalFailed ?? 0;
$avgDuration = $avgDuration ?? 0;
$filterStatus = $_GET['status'] ?? '';
$filterAgent = $_GET['agent'] ?? '';
$filterFrom = $_GET['from'] ?? '';
$filterTo = $_GET['to'] ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Call Sessions History</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-agents" class="btn btn-outline-primary btn-sm"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Total Sessions</div><div class="aps-cp-stat-value"><?= number_format($totalSessions) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Completed</div><div class="aps-cp-stat-value text-success"><?= $totalCompleted ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Failed</div><div class="aps-cp-stat-value text-danger"><?= $totalFailed ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body text-center"><div class="aps-cp-stat-label">Avg Duration</div><div class="aps-cp-stat-value"><?= round($avgDuration) ?>s</div></div></div></div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header"><i class="fas fa-filter me-2"></i>Filters</div>
        <div class="aps-cp-card-body">
            <form method="GET" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach (['scheduled','in_progress','completed','failed','no_answer'] as $st): ?>
                            <option value="<?= $st ?>" <?= $filterStatus === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$st)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Agent</label>
                    <select name="agent" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach ($agents as $ag): ?>
                            <option value="<?= htmlspecialchars($ag) ?>" <?= $filterAgent === $ag ? 'selected' : '' ?>><?= htmlspecialchars($ag) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">From</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($filterFrom) ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($filterTo) ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>/admin/voice-agents/sessions" class="btn btn-outline-secondary btn-sm w-100"><i class="fas fa-times me-1"></i>Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><i class="fas fa-list me-2"></i>Sessions (<?= count($sessions) ?> results)</div>
        <div class="aps-cp-card-body">
            <?php if (empty($sessions)): ?>
                <div class="text-center text-muted py-4"><i class="fas fa-history fa-2x mb-2"></i><p>No sessions found</p></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>ID</th><th>Lead</th><th>Phone</th><th>Agent</th><th>Duration</th><th>Response</th><th>Sentiment</th><th>Status</th><th>Date</th><th>Details</th></tr></thead>
                        <tbody>
                        <?php foreach ($sessions as $s): ?>
                            <tr>
                                <td>#<?= $s['id'] ?></td>
                                <td><?= htmlspecialchars($s['lead_name'] ?? 'N/A') ?></td>
                                <td><code class="small"><?= htmlspecialchars($s['phone']) ?></code></td>
                                <td><span class="aps-cp-badge badge bg-secondary"><?= htmlspecialchars($s['ai_agent_id'] ?? 'N/A') ?></span></td>
                                <td><?= $s['duration_seconds'] > 0 ? round($s['duration_seconds']/60,1).'m' : '-' ?></td>
                                <td><span class="aps-cp-badge badge bg-<?= $s['customer_response'] === 'interested' ? 'success' : ($s['customer_response'] === 'dnd' ? 'danger' : ($s['customer_response'] === 'callback' ? 'warning' : 'secondary')) ?>"><?= ucfirst(htmlspecialchars($s['customer_response'] ?? '-')) ?></span></td>
                                <td><span class="text-<?= $s['sentiment'] === 'positive' ? 'success' : ($s['sentiment'] === 'negative' ? 'danger' : 'muted') ?>"><?= ucfirst(htmlspecialchars($s['sentiment'] ?? '-')) ?></span></td>
                                <td><span class="aps-cp-badge badge bg-<?= $s['status'] === 'completed' ? 'success' : ($s['status'] === 'failed' ? 'danger' : ($s['status'] === 'in_progress' ? 'info' : 'secondary')) ?>"><?= ucfirst(htmlspecialchars($s['status'])) ?></span></td>
                                <td class="text-muted small"><?= date('d M Y H:i', strtotime($s['created_at'])) ?></td>
                                <td>
                                    <?php if ($s['call_transcript']): ?>
                                        <button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#detailModal<?= $s['id'] ?>"><i class="fas fa-eye"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php foreach ($sessions as $s): ?>
                    <?php if ($s['call_transcript']): ?>
                        <div class="modal fade" id="detailModal<?= $s['id'] ?>" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
                            <div class="modal-header"><h5 class="modal-title">Call #<?= $s['id'] ?> â€” <?= htmlspecialchars($s['lead_name'] ?? '') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <h6>Transcript</h6><pre class="bg-light p-3 rounded small" class="style-82023"><?= htmlspecialchars($s['call_transcript'] ?? '') ?></pre>
                                <?php if ($s['ai_summary']): ?><h6 class="mt-3">AI Summary</h6><p><?= htmlspecialchars($s['ai_summary']) ?></p><?php endif; ?>
                            </div>
                        </div></div></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
