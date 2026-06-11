<?php
$page_title = $page_title ?? 'Chatbot Logs';
$logs = $logs ?? [];
$stats = $stats ?? ['total' => 0, 'avg_satisfaction' => 0, 'avg_response_time' => 0];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Chatbot Logs</h1>
        <p class="text-muted mb-0">Monitor AI chatbot interactions and performance</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-4 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                            <i class="fas fa-comments fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Interactions</h6>
                        <h3 class="mb-0"><?= number_format($stats['total']) ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-success bg-opacity-10 text-success rounded p-3">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Avg Satisfaction</h6>
                        <h3 class="mb-0"><?= number_format($stats['avg_satisfaction'], 1) ?>/5</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-info bg-opacity-10 text-info rounded p-3">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Avg Response Time</h6>
                        <h3 class="mb-0"><?= number_format($stats['avg_response_time'], 0) ?>ms</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0">Interaction Logs</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Query</th>
                        <th>Score</th>
                        <th>Response Time</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No interactions recorded</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($row['user_name'] ?? 'Guest') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($row['user_email'] ?? '') ?></small>
                                </td>
                                <td><small><?= htmlspecialchars(mb_substr($row['query'] ?? '', 0, 60)) ?><?= mb_strlen($row['query'] ?? '') > 60 ? '...' : '' ?></small></td>
                                <td>
                                    <?php if (($row['satisfaction_score'] ?? null) !== null): ?>
                                        <span class="badge bg-<?= ($row['satisfaction_score'] ?? 0) >= 4 ? 'success' : (($row['satisfaction_score'] ?? 0) >= 2 ? 'warning' : 'danger') ?>">
                                            <?= (int)$row['satisfaction_score'] ?>/5
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= number_format($row['response_time'] ?? 0) ?>ms</small></td>
                                <td><small><?= date('M j, Y H:i', strtotime($row['created_at'] ?? 'now')) ?></small></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-info" onclick="viewChat(<?= $row['id'] ?>)">
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
</div>

<div class="modal fade" id="chatModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chat Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="chatModalBody">
                <div class="text-center py-4 text-muted">Loading...</div>
            </div>
        </div>
    </div>
</div>

<script>
function viewChat(id) {
    const el = document.getElementById('chatModalBody');
    el.innerHTML = '<div class="text-center py-4 text-muted">Loading...</div>';
    const modal = new bootstrap.Modal(document.getElementById('chatModal'));
    modal.show();
    <?php foreach ($logs as $row): ?>
    if (id === <?= $row['id'] ?>) {
        el.innerHTML = `
            <div class="mb-3">
                <label class="fw-bold text-muted small">Query</label>
                <div class="p-3 bg-light rounded mt-1">${escapeHtml(<?= json_encode($row['query'] ?? '') ?>)}</div>
            </div>
            <div class="mb-3">
                <label class="fw-bold text-muted small">Response</label>
                <div class="p-3 bg-light rounded mt-1">${escapeHtml(<?= json_encode($row['response'] ?? '') ?>)}</div>
            </div>
            <div class="row text-muted small">
                <div class="col-md-4">Satisfaction: ${<?= json_encode($row['satisfaction_score']) ?> ?? 'N/A'}/5</div>
                <div class="col-md-4">Response Time: ${<?= json_encode(number_format($row['response_time'] ?? 0)) ?>}ms</div>
                <div class="col-md-4">Date: ${<?= json_encode(date('M j, Y H:i', strtotime($row['created_at'] ?? 'now'))) ?>}</div>
            </div>
        `;
    }
    <?php endforeach; ?>
}
function escapeHtml(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
</script>
