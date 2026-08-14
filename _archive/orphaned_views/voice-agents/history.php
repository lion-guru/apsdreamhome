<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-history me-2"></i> Call History</h3>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-users/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/voice-users/history">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Agent</label>
                        <select name="agent" class="form-select">
                            <option value="">All users</option>
                            <?php foreach ($agents_list as $a): ?>
                            <option value="<?= htmlspecialchars($a['agent_id']) ?>" <?= ($filters['agent'] ?? '') === $a['agent_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['agent_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="scheduled" <?= ($filters['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="no_answer" <?= ($filters['status'] ?? '') === 'no_answer' ? 'selected' : '' ?>>No Answer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name or Phone" value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-2">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i> Filter</button>
                    <a href="<?= BASE_URL ?>/admin/voice-users/history" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Call Records</h6>
            <span class="badge bg-secondary"><?= $total ?? 0 ?> total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small">Date</th>
                            <th class="small">Lead Name</th>
                            <th class="small">Phone</th>
                            <th class="small">Agent</th>
                            <th class="small text-center">Duration</th>
                            <th class="small text-center">Status</th>
                            <th class="small">Interest</th>
                            <th class="small">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($calls)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No call records found</td></tr>
                        <?php else: ?>
                        <?php foreach ($calls as $call): ?>
                        <tr>
                            <td class="small"><?= date('d M Y', strtotime($call['created_at'] ?? 'now')) ?><br><span class="text-muted"><?= date('h:i A', strtotime($call['created_at'] ?? 'now')) ?></span></td>
                            <td class="fw-medium"><?= htmlspecialchars($call['lead_name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($call['phone'] ?? $call['lead_phone'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($call['agent_display_name'] ?? $call['ai_agent_id'] ?? 'Auto') ?></td>
                            <td class="text-center"><?= $call['duration_seconds'] ? gmdate('i:s', $call['duration_seconds']) : '-' ?></td>
                            <td class="text-center">
                                <span class="badge bg-<?= $call['status'] === 'completed' ? 'success' : ($call['status'] === 'in_progress' ? 'info' : ($call['status'] === 'failed' ? 'danger' : ($call['status'] === 'no_answer' ? 'secondary' : 'warning'))) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $call['status'] ?? 'unknown')) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($call['interest_level']) && $call['interest_level'] !== 'none'): ?>
                                <span class="badge bg-<?= $call['interest_level'] === 'hot' ? 'danger' : ($call['interest_level'] === 'warm' ? 'warning' : 'info') ?>">
                                    <?= ucfirst($call['interest_level']) ?>
                                </span>
                                <?php else: ?>
                                <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#callDetailModal"
                                    data-id="<?= $call['id'] ?>"
                                    data-lead="<?= htmlspecialchars($call['lead_name'] ?? 'Unknown') ?>"
                                    data-summary="<?= htmlspecialchars($call['ai_summary'] ?? '') ?>"
                                    data-transcript="<?= htmlspecialchars($call['call_transcript'] ?? '') ?>"
                                    data-sentiment="<?= $call['sentiment_score'] ?? '' ?>"
                                    data-status="<?= $call['customer_response'] ?? '' ?>"
                                    onclick="showCallDetail(this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (($total_pages ?? 1) > 1): ?>
        <div class="card-footer bg-white">
            <nav>
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL ?>/admin/voice-users/history?page=<?= $i ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?><?= !empty($_GET['agent']) ? '&agent=' . urlencode($_GET['agent']) : '' ?><?= !empty($_GET['date_from']) ? '&date_from=' . urlencode($_GET['date_from']) : '' ?><?= !empty($_GET['date_to']) ? '&date_to=' . urlencode($_GET['date_to']) : '' ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Call Detail Modal -->
<div class="modal fade" id="callDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-phone-alt me-2"></i> Call Detail - <span id="modalLeadName">-</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="small text-muted">Status</label>
                        <div><span id="modalStatus" class="badge bg-success">-</span></div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Sentiment Score</label>
                        <div><strong id="modalSentiment">-</strong></div>
                    </div>
                    <div class="col-md-4">
                        <label class="small text-muted">Customer Response</label>
                        <div><strong id="modalResponse">-</strong></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted fw-bold">AI Summary</label>
                    <p id="modalSummary" class="bg-light p-3 rounded mb-0" class="style-58999">-</p>
                </div>
                <div>
                    <label class="small text-muted fw-bold">Transcript</label>
                    <div id="modalTranscript" class="bg-dark text-light p-3 rounded small" class="style-54046">-</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showCallDetail(btn) {
    document.getElementById('modalLeadName').textContent = btn.dataset.lead || '-';
    document.getElementById('modalSummary').textContent = btn.dataset.summary || 'No summary available';
    document.getElementById('modalTranscript').textContent = btn.dataset.transcript || 'No transcript available';
    var sentiment = btn.dataset.sentiment || '';
    if (sentiment) {
        var sentVal = parseFloat(sentiment);
        document.getElementById('modalSentiment').textContent = sentVal.toFixed(2);
    } else {
        document.getElementById('modalSentiment').textContent = '-';
    }
    var response = btn.dataset.status || '';
    document.getElementById('modalResponse').textContent = response ? response.replace(/_/g, ' ') : '-';
    var badge = document.getElementById('modalStatus');
    if (response === 'interested') {
        badge.className = 'badge bg-success';
        badge.textContent = 'Interested';
    } else if (response === 'not_interested') {
        badge.className = 'badge bg-danger';
        badge.textContent = 'Not Interested';
    } else if (response === 'callback') {
        badge.className = 'badge bg-warning text-dark';
        badge.textContent = 'Callback';
    } else {
        badge.className = 'badge bg-secondary';
        badge.textContent = response || '-';
    }
}
</script>
