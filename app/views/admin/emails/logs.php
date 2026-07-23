<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-history me-2"></i>Email Logs</h4>
        <a href="<?= BASE_URL ?>/admin/email-logs" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-sync me-1"></i>Refresh
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body aps-cp-card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="sent" <?php echo ($statusFilter ?? '') === 'sent' ? 'selected' : ''; ?>>Sent</option>
                        <option value="failed" <?php echo ($statusFilter ?? '') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="pending" <?php echo ($statusFilter ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo ($statusFilter ?? '') === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="cancelled" <?php echo ($statusFilter ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <span class="text-muted small"><?php echo $total ?? 0; ?> total log entries</span>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card aps-cp-card">
        <div class="card-body p-0">
            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>To</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Sent At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['to_email'] ?? $log['to_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars(mb_substr($log['subject'] ?? '', 0, 60)) . (mb_strlen($log['subject'] ?? '') > 60 ? '...' : ''); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = match ($log['status'] ?? '') {
                                            'sent' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            'pending' => 'bg-warning text-dark',
                                            'processing' => 'bg-info',
                                            'cancelled' => 'bg-secondary',
                                            default => 'bg-secondary'
                                        };
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($log['status'] ?? 'unknown'); ?></span>
                                    </td>
                                    <td><?php echo $log['sent_at'] ? date('d M Y H:i', strtotime($log['sent_at'])) : ($log['created_at'] ? date('d M Y H:i', strtotime($log['created_at'])) : 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewLog(<?php echo htmlspecialchars(json_encode($log)); ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (($totalPages ?? 1) > 1): ?>
                    <div class="p-3">
                        <nav>
                            <ul class="pagination pagination-sm mb-0 justify-content-center">
                                <li class="page-item <?php echo ($page ?? 1) <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?status=<?php echo urlencode($statusFilter ?? ''); ?>&page=<?php echo max(1, ($page ?? 1) - 1); ?>">Previous</a>
                                </li>
                                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                    <li class="page-item <?php echo $p === ($page ?? 1) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?status=<?php echo urlencode($statusFilter ?? ''); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page ?? 1) >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?status=<?php echo urlencode($statusFilter ?? ''); ?>&page=<?php echo min($totalPages, ($page ?? 1) + 1); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted text-center mb-0 py-5">
                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                    No email logs found<?php echo !empty($statusFilter) ? ' with the selected filter' : ''; ?>.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Log Detail Modal -->
<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-envelope me-2"></i>Email Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="logDetailBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewLog(log) {
    const body = document.getElementById('logDetailBody');
    body.innerHTML = `
        <div class="mb-3">
            <strong>To:</strong> ${escapeHtml(log.to_email || 'N/A')} ${log.to_name ? '(' + escapeHtml(log.to_name) + ')' : ''}
        </div>
        <div class="mb-3">
            <strong>From:</strong> ${escapeHtml(log.from_email || 'N/A')} ${log.from_name ? '(' + escapeHtml(log.from_name) + ')' : ''}
        </div>
        <div class="mb-3">
            <strong>Subject:</strong> ${escapeHtml(log.subject || 'N/A')}
        </div>
        <div class="mb-3">
            <strong>Status:</strong> <span class="badge ${log.status === 'sent' ? 'bg-success' : log.status === 'failed' ? 'bg-danger' : log.status === 'pending' ? 'bg-warning' : 'bg-secondary'}">${escapeHtml(log.status || 'unknown')}</span>
        </div>
        <div class="mb-3">
            <strong>Attempts:</strong> ${log.attempts || 0} / ${log.max_attempts || 3}
        </div>
        ${log.error_message ? `<div class="mb-3"><strong>Error:</strong><pre class="text-danger mt-1">${escapeHtml(log.error_message)}</pre></div>` : ''}
        <div class="mb-3">
            <strong>Created:</strong> ${log.created_at || 'N/A'}
        </div>
        <div class="mb-3">
            <strong>Sent At:</strong> ${log.sent_at || 'Not sent yet'}
        </div>
        ${log.body_html ? `<div class="mb-3"><strong>HTML Preview:</strong><div class="border rounded p-3 mt-1" style="max-height:300px;overflow:auto;">${log.body_html}</div></div>` : ''}
    `;
    new bootstrap.Modal(document.getElementById('logModal')).show();
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
</script>