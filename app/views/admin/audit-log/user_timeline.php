<?php $page_title = 'User Timeline'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-user-clock me-2"></i><?= htmlspecialchars($user['name'] ?? ('User #' . (int)($user['id'] ?? 0))) ?></h2>
        <a href="<?= BASE_URL ?>/admin/audit-log" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <p class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?> · <?= htmlspecialchars($user['role'] ?? '') ?></p>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Activity (<?= count($timeline ?? []) ?> events)</h6></div>
        <div class="card-body p-0">
            <?php if (empty($timeline)): ?>
                <p class="text-muted text-center py-4">No recorded activity for this user.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Time</th><th>Action</th><th>Entity</th><th>Status</th><th>Description</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($timeline as $t): ?>
                            <tr>
                                <td><small><?= htmlspecialchars($t['created_at'] ?? '') ?></small></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($t['action'] ?? '') ?></span></td>
                                <td><small><?= htmlspecialchars(trim(($t['entity_type'] ?? '') . ' #' . ($t['entity_id'] ?? ''), ' #')) ?: '—' ?></small></td>
                                <td><small><?= htmlspecialchars($t['status'] ?? '') ?></small></td>
                                <td><small class="text-muted"><?= htmlspecialchars(substr($t['description'] ?? '', 0, 80)) ?></small></td>
                                <td><a href="<?= BASE_URL ?>/admin/audit-log/<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
