<?php
$page_title = $page_title ?? 'Auto Dialer';
$total_scheduled = $total_scheduled ?? 0;
$pending_total = $pending_total ?? 0;
$completed_total = $completed_total ?? 0;
$pending_today = $pending_today ?? 0;
$completed_today = $completed_today ?? 0;
$total_logs = $total_logs ?? 0;
$today_queue = $today_queue ?? [];
$upcoming = $upcoming ?? [];
$recent_logs = $recent_logs ?? [];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-phone-volume me-2 text-primary"></i>Auto Dialer</h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/ai-calling/call-analytics" class="btn btn-outline-info btn-sm"><i class="fas fa-chart-pie me-1"></i>Call Analytics</a>
            <a href="<?= BASE_URL ?>/admin/ai-calling/schedule" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar me-1"></i>Schedule</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-phone"></i></span></div>
                    <div><div class="aps-cp-stat-label">Total Scheduled</div><div class="aps-cp-stat-value"><?= number_format($total_scheduled) ?></div><div class="aps-cp-stat-meta">In queue</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-hourglass-half"></i></span></div>
                    <div><div class="aps-cp-stat-label">Pending Today</div><div class="aps-cp-stat-value text-warning"><?= number_format($pending_today) ?></div><div class="aps-cp-stat-meta"><?= $pending_total ?> total pending</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                    <div><div class="aps-cp-stat-label">Completed Today</div><div class="aps-cp-stat-value text-success"><?= number_format($completed_today) ?></div><div class="aps-cp-stat-meta"><?= $completed_total ?> total done</div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-clipboard-list"></i></span></div>
                    <div><div class="aps-cp-stat-label">App Call Logs</div><div class="aps-cp-stat-value"><?= number_format($total_logs) ?></div><div class="aps-cp-stat-meta">Logged from app</div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-ol me-2 text-primary"></i>Today's Call Queue</h5>
                    <div>
                        <button class="btn btn-success btn-sm me-2" id="btn-process"><i class="fas fa-play me-1"></i>Process Queue</button>
                        <button class="btn btn-primary btn-sm" id="btn-ai-schedule" data-bs-toggle="collapse" data-bs-target="#aiForm"><i class="fas fa-robot me-1"></i>AI Auto-Schedule</button>
                    </div>
                </div>
                <div class="collapse" id="aiForm">
                    <div class="card-body border-bottom bg-light">
                        <form id="aiScheduleForm" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                            <div class="col-auto">
                                <label class="form-label mb-0 small">Min Score</label>
                                <input type="number" name="min_score" class="form-control form-control-sm" value="70" min="0" max="100" style="width:90px">
                            </div>
                            <div class="col-auto">
                                <label class="form-label mb-0 small">Date</label>
                                <input type="date" name="scheduled_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" style="width:150px">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-magic me-1"></i>Run AI</button>
                            </div>
                            <div class="col-12"><small class="text-muted">AI scores all leads and schedules hot leads (≥ min score) for calling.</small></div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Time</th><th>Lead</th><th>Phone</th><th>Priority</th><th>Script</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($today_queue)): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">No calls scheduled for today.</td></tr>
                                <?php else: foreach ($today_queue as $q): ?>
                                    <tr>
                                        <td><?= substr($q['scheduled_time'] ?? '', 0, 5) ?></td>
                                        <td><?= htmlspecialchars($q['lead_name'] ?? ('Lead #' . ($q['lead_id'] ?? '?'))) ?></td>
                                        <td><?= htmlspecialchars($q['lead_phone'] ?? $q['phone'] ?? '-') ?></td>
                                        <td><span class="badge bg-<?= ($q['priority'] ?? 'medium') === 'high' ? 'danger' : 'secondary' ?>"><?= ucfirst($q['priority'] ?? 'medium') ?></span></td>
                                        <td><?= htmlspecialchars(str_replace('_', ' ', $q['script_template'] ?? '-')) ?></td>
                                        <td><span class="badge bg-<?= ($q['status'] ?? '') === 'completed' ? 'success' : (($q['status'] ?? '') === 'failed' ? 'danger' : 'warning') ?>"><?= ucfirst($q['status'] ?? 'pending') ?></span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-rocket me-2 text-info"></i>Upcoming Calls</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light"><tr><th>Date</th><th>Lead</th><th>Phone</th><th>Time</th><th>Priority</th></tr></thead>
                            <tbody>
                                <?php if (empty($upcoming)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No upcoming calls.</td></tr>
                                <?php else: foreach ($upcoming as $u): ?>
                                    <tr>
                                        <td><?= date('d M', strtotime($u['scheduled_date'])) ?></td>
                                        <td><?= htmlspecialchars($u['lead_name'] ?? ('Lead #' . ($u['lead_id'] ?? '?'))) ?></td>
                                        <td><?= htmlspecialchars($u['lead_phone'] ?? $u['phone'] ?? '-') ?></td>
                                        <td><?= substr($u['scheduled_time'] ?? '', 0, 5) ?></td>
                                        <td><span class="badge bg-<?= ($u['priority'] ?? 'medium') === 'high' ? 'danger' : 'secondary' ?>"><?= ucfirst($u['priority'] ?? 'medium') ?></span></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-history me-2 text-secondary"></i>Recent App Call Logs</h5></div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (empty($recent_logs)): ?>
                            <div class="list-group-item text-muted text-center py-4">No call logs yet.</div>
                        <?php else: foreach ($recent_logs as $log): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($log['name'] ?? 'Unknown') ?></strong>
                                    <small class="text-muted"><?= date('d M H:i', strtotime($log['created_at'])) ?></small>
                                </div>
                                <div>
                                    <span class="badge bg-<?= ($log['outcome'] ?? '') === 'connected' ? 'success' : (($log['outcome'] ?? '') === 'not_answered' ? 'secondary' : 'light') ?>"><?= ucfirst(str_replace('_', ' ', $log['outcome'] ?? '—')) ?></span>
                                    <span class="badge bg-dark"><?= ucfirst($log['method'] ?? 'app') ?></span>
                                    <?php if (!empty($log['duration'])): ?><span class="badge bg-info"><?= $log['duration'] ?>s</span><?php endif; ?>
                                </div>
                                <?php if (!empty($log['notes'])): ?><small class="text-muted d-block mt-1"><?= htmlspecialchars(substr($log['notes'], 0, 80)) ?></small><?php endif; ?>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="<?= BASE_URL ?>/admin/ai-calling/call-analytics" class="btn btn-sm btn-outline-primary">View Full Analytics</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('btn-process')?.addEventListener('click', function() {
    const btn = this; btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing…';
    fetch('<?= BASE_URL ?>/admin/ai-calling/auto-dialer/process', {method: 'POST', headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r => r.json()).then(d => {
            alert((d.message || 'Done') + (d.processed !== undefined ? ' (Processed: ' + d.processed + ', Failed: ' + d.failed + ')' : ''));
            location.reload();
        }).catch(e => { alert('Error: ' + e); btn.disabled = false; btn.innerHTML = '<i class="fas fa-play me-1"></i>Process Queue'; });
});
document.getElementById('aiScheduleForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type=submit]'); btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Running…';
    const fd = new FormData(this);
    const params = new URLSearchParams();
    fd.forEach((v,k) => params.append(k, v));
    fetch('<?= BASE_URL ?>/admin/ai-calling/auto-dialer/ai-schedule', {method: 'POST', headers:{'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'}, body: params.toString()})
        .then(r => r.json()).then(d => {
            alert((d.message || 'Done'));
            location.reload();
        }).catch(err => { alert('Error: ' + err); })
        .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-magic me-1"></i>Run AI'; });
});
</script>
