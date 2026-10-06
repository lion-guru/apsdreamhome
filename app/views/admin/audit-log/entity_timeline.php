<?php $page_title = 'Entity Timeline'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-history me-2"></i><?= htmlspecialchars($entity_type) ?> #<?= (int)$entity_id ?></h2>
        <a href="<?= BASE_URL ?>/admin/audit-log" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h6 class="mb-0">Change history (<?= count($timeline ?? []) ?> events)</h6></div>
        <div class="card-body p-0">
            <?php if (empty($timeline)): ?>
                <p class="text-muted text-center py-4">No recorded events for this entity.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Time</th><th>Action</th><th>User</th><th>Status</th><th>Description</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($timeline as $t): ?>
                            <tr>
                                <td><small><?= htmlspecialchars($t['created_at'] ?? '') ?></small></td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($t['action'] ?? '') ?></span> <small class="text-muted"><?= htmlspecialchars($t['action_type'] ?? '') ?></small></td>
                                <td><small>#<?= (int)($t['user_id'] ?? 0) ?> <?= htmlspecialchars($t['user_role'] ?? '') ?></small></td>
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
