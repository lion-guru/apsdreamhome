<?php
$page_title = $page_title ?? 'Voice Call Logs';
$totalCalls = $totalCalls ?? 0;
$completedCalls = $completedCalls ?? 0;
$failedCalls = $failedCalls ?? 0;
$noAnswerCalls = $noAnswerCalls ?? 0;
$avgDuration = $avgDuration ?? 0;
$callsToday = $callsToday ?? 0;
$interestedCount = $interestedCount ?? 0;
$calls = $calls ?? [];
$agents = $agents ?? [];
$pagination = $pagination ?? ['page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 25, 'offset' => 0];
$filters = $filters ?? [];
$base = BASE_URL . '/admin/ai-calling/call-logs';
?>
<style>
.cl-stat-card { border-left: 4px solid var(--bs-primary); border-radius: 10px; transition: transform .15s; }
.cl-stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
.cl-stat-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }
.cl-stat-label { font-size: 0.78rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.cl-stat-meta { font-size: 0.72rem; color: #94a3b8; }
.cl-row { cursor: pointer; transition: background .12s; }
.cl-row:hover { background: #f0fdfa !important; }
#callDetailModal .modal-body { max-height: 70vh; overflow-y: auto; }
.transcript-line { padding: 6px 12px; margin: 4px 0; border-radius: 8px; font-size: 0.85rem; max-width: 85%; }
.transcript-bot { background: #f0f9ff; border: 1px solid #bae6fd; color: #0c4a6e; }
.transcript-user { background: #f0fdf4; border: 1px solid #bbf7d0; color: #14532d; margin-left: auto; }
.transcript-meta { font-size: 0.72rem; color: #94a3b8; margin-top: 2px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-headset me-2 text-primary"></i>Voice Call Logs</h2>
        <div>
            <a href="<?= $base ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sync me-1"></i>Refresh</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card cl-stat-card border-0 shadow-sm"><div class="card-body py-3 px-3"><div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-phone-volume"></i></span></div>
                <div><div class="cl-stat-value"><?= number_format($totalCalls) ?></div><div class="cl-stat-label">Total Calls</div><div class="cl-stat-meta">Today: <?= $callsToday ?></div></div>
            </div></div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card cl-stat-card border-0 shadow-sm" class="style-93507"><div class="card-body py-3 px-3"><div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-check-circle"></i></span></div>
                <div><div class="cl-stat-value text-success"><?= number_format($completedCalls) ?></div><div class="cl-stat-label">Completed</div><div class="cl-stat-meta"><?= $totalCalls > 0 ? round($completedCalls/$totalCalls*100) : 0 ?>% success</div></div>
            </div></div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card cl-stat-card border-0 shadow-sm" class="style-56775"><div class="card-body py-3 px-3"><div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><span class="badge bg-danger rounded-pill p-2"><i class="fas fa-times-circle"></i></span></div>
                <div><div class="cl-stat-value text-danger"><?= number_format($failedCalls + $noAnswerCalls) ?></div><div class="cl-stat-label">Failed / No Answer</div><div class="cl-stat-meta"><?= $failedCalls ?> failed, <?= $noAnswerCalls ?> no answer</div></div>
            </div></div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card cl-stat-card border-0 shadow-sm" class="style-37744"><div class="card-body py-3 px-3"><div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-clock"></i></span></div>
                <div><div class="cl-stat-value"><?= round($avgDuration) ?>s</div><div class="cl-stat-label">Avg Duration</div><div class="cl-stat-meta"><?= $interestedCount ?> interested</div></div>
            </div></div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="get" action="<?= $base ?>">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Search</label>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Phone, name, transcript..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <?php foreach (['completed','failed','in_progress','no_answer','scheduled'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Sentiment</label>
                        <select name="sentiment" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="positive" <?= ($filters['sentiment'] ?? '') === 'positive' ? 'selected' : '' ?>>Positive</option>
                            <option value="neutral" <?= ($filters['sentiment'] ?? '') === 'neutral' ? 'selected' : '' ?>>Neutral</option>
                            <option value="negative" <?= ($filters['sentiment'] ?? '') === 'negative' ? 'selected' : '' ?>>Negative</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Response</label>
                        <select name="response" class="form-select form-select-sm">
                            <option value="">All</option>
                            <?php foreach (['interested','not_interested','callback','dnd','no_answer'] as $r): ?>
                                <option value="<?= $r ?>" <?= ($filters['response'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$r)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Agent</label>
                        <select name="agent" class="form-select form-select-sm">
                            <option value="">All Agents</option>
                            <?php foreach ($agents as $a): ?>
                                <option value="<?= htmlspecialchars($a) ?>" <?= ($filters['agent'] ?? '') === $a ? 'selected' : '' ?>><?= htmlspecialchars($a) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">From</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['from'] ?? '') ?>">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">To</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['to'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search me-1"></i>Apply</button>
                    <a href="<?= $base ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i>Clear</a>
                    <span class="text-muted small align-self-center ms-2"><?= number_format($pagination['total']) ?> results</span>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th class="style-1979">#</th><th>Lead / Phone</th><th>Agent</th><th>Script</th><th>Duration</th><th>Sentiment</th><th>Response</th><th>Interest</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
<?php if (empty($calls)): ?>
                        <tr><td colspan="10" class="text-center text-muted py-5">
                            <i class="fas fa-headset fa-3x d-block mb-3 text-muted opacity-25"></i>
                            <h5 class="text-muted">No call logs found</h5>
                            <p class="text-muted small">No voice calls match your current filters.</p>
                        </td></tr>
<?php else: foreach ($calls as $c): ?>
                        <tr class="cl-row" onclick="showCallDetail(<?= $c['id'] ?>)">
                            <td class="text-muted small">#<?= $c['id'] ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($c['lead_name'] ?? 'Unknown') ?></div>
                                <code class="small"><?= htmlspecialchars($c['phone'] ?? '-') ?></code>
                            </td>
                            <td><span class="badge bg-dark bg-opacity-10 text-dark"><?= htmlspecialchars($c['ai_agent_id'] ?? 'auto') ?></span></td>
                            <td><small class="text-muted"><?= htmlspecialchars(str_replace('_', ' ', $c['script_template'] ?? '-')) ?></small></td>
                            <td>
<?php if ($c['duration_seconds'] > 0): ?>
                                <span class="fw-semibold"><?= floor($c['duration_seconds']/60) ?>m <?= $c['duration_seconds']%60 ?>s</span>
<?php else: ?>
                                <span class="text-muted">-</span>
<?php endif; ?>
                            </td>
                            <td>
<?php if ($c['sentiment']): ?>
                                <span class="text-<?= $c['sentiment'] === 'positive' ? 'success' : ($c['sentiment'] === 'negative' ? 'danger' : 'muted') ?> fw-semibold">
                                    <?= ucfirst(htmlspecialchars($c['sentiment'])) ?>
                                </span>
<?php else: ?>
                                <span class="text-muted">-</span>
<?php endif; ?>
                            </td>
                            <td>
<?php
$rc = ['interested'=>'success','not_interested'=>'danger','callback'=>'warning','dnd'=>'dark','no_answer'=>'secondary'];
$rc = $rc[$c['customer_response'] ?? ''] ?? 'light';
?>
                                <span class="badge bg-<?= $rc ?>"><?= ucfirst(str_replace('_', ' ', $c['customer_response'] ?? '-')) ?></span>
                            </td>
                            <td>
<?php if ($c['interest_level'] && $c['interest_level'] !== 'none'): ?>
                                <span class="badge bg-<?= $c['interest_level'] === 'hot' ? 'danger' : ($c['interest_level'] === 'warm' ? 'warning' : 'info') ?>"><?= ucfirst($c['interest_level']) ?></span>
<?php else: ?>
                                <span class="text-muted">-</span>
<?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $c['status'] === 'completed' ? 'success' : ($c['status'] === 'failed' ? 'danger' : ($c['status'] === 'in_progress' ? 'info' : 'secondary')) ?>"><?= ucfirst(str_replace('_', ' ', $c['status'])) ?></span>
                            </td>
                            <td><small class="text-muted"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></small></td>
                        </tr>
<?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php if ($pagination['total_pages'] > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['page']-1 ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>''])) ?>">Prev</a>
            </li>
<?php for ($p = max(1, $pagination['page']-3); $p <= min($pagination['total_pages'], $pagination['page']+3); $p++): ?>
            <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>''])) ?>"><?= $p ?></a>
            </li>
<?php endfor; ?>
            <li class="page-item <?= $pagination['page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['page']+1 ?>&<?= http_build_query(array_diff_key($_GET, ['page'=>''])) ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>
</div>

<div class="modal fade" id="callDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-phone-alt me-2"></i>Call Detail <span id="cdId"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="cdBody">
                <div class="text-center py-4"><div class="spinner-border text-primary"></div></div>
            </div>
        </div>
    </div>
</div>

<script>
function showCallDetail(id) {
    var modal = new bootstrap.Modal(document.getElementById('callDetailModal'));
    document.getElementById('cdId').textContent = '#' + id;
    document.getElementById('cdBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';
    modal.show();
    fetch('<?= BASE_URL ?>/admin/ai-calling/call-detail?id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success || !data.call) {
                document.getElementById('cdBody').innerHTML = '<div class="alert alert-danger">Call not found.</div>';
                return;
            }
            var c = data.call;
            var html = '<div class="row g-3 mb-4">';
            html += '<div class="col-md-6"><div class="card border-0 bg-light"><div class="card-body p-3">';
            html += '<h6 class="text-primary mb-2"><i class="fas fa-user me-1"></i>Lead Info</h6>';
            html += '<div><strong>' + esc(c.lead_name || 'Unknown') + '</strong></div>';
            html += '<div><code>' + esc(c.phone || '-') + '</code></div>';
            if (c.lead_email) html += '<div class="small text-muted">' + esc(c.lead_email) + '</div>';
            if (c.lead_budget) html += '<div class="small">Budget: ' + esc(c.lead_budget) + '</div>';
            if (c.lead_city) html += '<div class="small">City: ' + esc(c.lead_city) + '</div>';
            html += '</div></div></div>';
            html += '<div class="col-md-6"><div class="card border-0 bg-light"><div class="card-body p-3">';
            html += '<h6 class="text-primary mb-2"><i class="fas fa-chart-bar me-1"></i>Call Stats</h6>';
            html += '<div class="row">';
            html += '<div class="col-6"><small class="text-muted">Duration</small><div class="fw-bold">' + (c.duration_seconds > 0 ? Math.floor(c.duration_seconds/60) + 'm ' + c.duration_seconds%60 + 's' : '-') + '</div></div>';
            html += '<div class="col-6"><small class="text-muted">Status</small><div><span class="badge bg-' + statusColor(c.status) + '">' + esc(c.status) + '</span></div></div>';
            html += '<div class="col-6 mt-2"><small class="text-muted">Sentiment</small><div>' + esc(c.sentiment || '-') + '</div></div>';
            html += '<div class="col-6 mt-2"><small class="text-muted">Response</small><div>' + esc(c.customer_response || '-') + '</div></div>';
            html += '<div class="col-6 mt-2"><small class="text-muted">Interest</small><div>' + esc(c.interest_level || '-') + '</div></div>';
            html += '<div class="col-6 mt-2"><small class="text-muted">Agent</small><div>' + esc(c.ai_agent_id || 'auto') + '</div></div>';
            html += '</div></div></div></div>';
            html += '</div>';
            if (c.ai_summary) {
                html += '<div class="card border-0 bg-light mb-3"><div class="card-body p-3">';
                html += '<h6 class="text-primary mb-2"><i class="fas fa-brain me-1"></i>AI Summary</h6>';
                html += '<p class="mb-0">' + esc(c.ai_summary) + '</p>';
                html += '</div></div>';
            }
            if (c.extracted) {
                html += '<div class="card border-0 bg-light mb-3"><div class="card-body p-3">';
                html += '<h6 class="text-primary mb-2"><i class="fas fa-user-plus me-1"></i>Extracted Lead Data</h6>';
                html += '<div class="row">';
                if (c.extracted.extracted_name) html += '<div class="col-sm-6"><small class="text-muted">Name</small><div>' + esc(c.extracted.extracted_name) + '</div></div>';
                if (c.extracted.extracted_budget) html += '<div class="col-sm-6"><small class="text-muted">Budget</small><div>' + esc(c.extracted.extracted_budget) + '</div></div>';
                if (c.extracted.extracted_location) html += '<div class="col-sm-6"><small class="text-muted">Location</small><div>' + esc(c.extracted.extracted_location) + '</div></div>';
                if (c.extracted.buying_timeline) html += '<div class="col-sm-6"><small class="text-muted">Timeline</small><div>' + esc(c.extracted.buying_timeline) + '</div></div>';
                if (c.extracted.extracted_requirements) html += '<div class="col-12"><small class="text-muted">Requirements</small><div>' + esc(c.extracted.extracted_requirements) + '</div></div>';
                html += '</div></div></div>';
            }
            if (c.call_transcript) {
                html += '<div class="card border-0 bg-light mb-3"><div class="card-body p-3">';
                html += '<h6 class="text-primary mb-2"><i class="fas fa-scroll me-1"></i>Call Transcript</h6>';
                var lines = c.call_transcript.split('\n');
                for (var i = 0; i < lines.length; i++) {
                    var line = lines[i].trim();
                    if (!line) continue;
                    var isBot = /bot|ai|agent|system/i.test(line) || line.indexOf(':') > 0 && /namaste|dhanyavaad|aps/i.test(line);
                    html += '<div class="transcript-line ' + (isBot ? 'transcript-bot' : 'transcript-user') + '">' + esc(line) + '</div>';
                }
                html += '</div></div>';
            }
            if (c.recording_url) {
                html += '<div class="mb-3"><h6 class="text-primary mb-2"><i class="fas fa-play-circle me-1"></i>Recording</h6>';
                html += '<audio controls src="' + esc(c.recording_url) + '" class="w-100"></audio></div>';
            }
            html += '<div class="text-muted small">Created: ' + esc(c.created_at) + '</div>';
            document.getElementById('cdBody').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('cdBody').innerHTML = '<div class="alert alert-danger">Failed to load call details.</div>';
        });
}
function esc(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
function statusColor(s) { return s === 'completed' ? 'success' : s === 'failed' ? 'danger' : s === 'in_progress' ? 'info' : 'secondary'; }
</script>
