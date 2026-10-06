<?php $page_title = 'Audit Event Detail'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-search me-2"></i>Event #<?= (int)($log['id'] ?? 0) ?></h2>
        <a href="<?= BASE_URL ?>/admin/audit-log" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Event</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:35%">Action</th><td><span class="badge bg-primary"><?= htmlspecialchars($log['action'] ?? '') ?></span> <small class="text-muted"><?= htmlspecialchars($log['action_type'] ?? '') ?></small></td></tr>
                        <tr><th>Status</th><td><?= htmlspecialchars($log['status'] ?? '') ?><?= !empty($log['error_message']) ? ' — <small class="text-danger">' . htmlspecialchars($log['error_message']) . '</small>' : '' ?></td></tr>
                        <tr><th>Description</th><td><?= nl2br(htmlspecialchars($log['description'] ?? '')) ?></td></tr>
                        <tr><th>Entity</th><td><?= htmlspecialchars(trim(($log['entity_type'] ?? '') . ' #' . ($log['entity_id'] ?? ''), ' #')) ?: '—' ?>
                            <?php if (!empty($log['entity_type']) && !empty($log['entity_id'])): ?>
                                <a href="<?= BASE_URL ?>/admin/audit-log/entity?entity_type=<?= urlencode($log['entity_type']) ?>&entity_id=<?= (int)$log['entity_id'] ?>" class="ms-2 small">full timeline</a>
                            <?php endif; ?>
                        </td></tr>
                        <tr><th>Time</th><td><?= htmlspecialchars($log['created_at'] ?? '') ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Actor & Request</h6></div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><th style="width:35%">User</th><td>#<?= (int)($log['user_id'] ?? 0) ?> (<?= htmlspecialchars($log['user_role'] ?? '') ?>)
                            <?php if (!empty($log['user_id'])): ?>
                                <a href="<?= BASE_URL ?>/admin/audit-log/user/<?= (int)$log['user_id'] ?>" class="ms-2 small">user timeline</a>
                            <?php endif; ?>
                        </td></tr>
                        <tr><th>IP</th><td><code class="small"><?= htmlspecialchars($log['ip_address'] ?? '') ?></code></td></tr>
                        <tr><th>Method / URL</th><td><small><code><?= htmlspecialchars(($log['request_method'] ?? '') . ' ' . ($log['request_url'] ?? '')) ?></code></small></td></tr>
                        <tr><th>User Agent</th><td><small class="text-muted"><?= htmlspecialchars(substr($log['user_agent'] ?? '', 0, 120)) ?></small></td></tr>
                        <tr><th>Session</th><td><small><code><?= htmlspecialchars(substr($log['session_id'] ?? '', 0, 24)) ?></code></small></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <?php if (!empty($log['old_values']) || !empty($log['new_values'])): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Value Changes</h6></div>
                <div class="card-body row">
                    <div class="col-md-6"><h6 class="small text-muted">Before</h6><pre class="bg-light p-2 rounded small"><?= htmlspecialchars($log['old_values'] ?? '') ?></pre></div>
                    <div class="col-md-6"><h6 class="small text-muted">After</h6><pre class="bg-light p-2 rounded small"><?= htmlspecialchars($log['new_values'] ?? '') ?></pre></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($related)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Same-entity history (<?= count($related) ?>)</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead><tr><th>Time</th><th>Action</th><th>User</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($related as $r): ?>
                            <tr>
                                <td><small><?= htmlspecialchars($r['created_at'] ?? '') ?></small></td>
                                <td><small><?= htmlspecialchars($r['action'] ?? '') ?></small></td>
                                <td><small>#<?= (int)($r['user_id'] ?? 0) ?></small></td>
                                <td><small><?= htmlspecialchars($r['status'] ?? '') ?></small></td>
                                <td><a href="<?= BASE_URL ?>/admin/audit-log/<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
