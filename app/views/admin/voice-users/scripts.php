<?php $scripts = $scripts ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Call Scripts</h4>
    <a href="<?= BASE_URL ?>admin/ai-calling/training" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Script</a>
</div>
<div class="card aps-cp-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Script Name</th>
                        <th>Language</th>
                        <th>Agent</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($scripts)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No scripts found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($scripts as $s): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['name'] ?? $s['script_name'] ?? 'Unnamed') ?></strong></td>
                                <td><?= htmlspecialchars($s['language'] ?? 'en') ?></td>
                                <td><?= htmlspecialchars($s['agent_name'] ?? $s['agent'] ?? 'General') ?></td>
                                <td><?php $active = $s['is_active'] ?? $s['status'] ?? 0; ?>
                                    <span class="badge bg-<?= $active ? 'success' : 'secondary' ?>">
                                        <?= $active ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>admin/ai-calling/training" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                    <form method="post" action="<?= BASE_URL ?>admin/ai-calling/training/save-script" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="script_id" value="<?= (int)($s['id'] ?? 0) ?>">
                                        <input type="hidden" name="is_active" value="<?= $active ? 0 : 1 ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?= $active ? 'warning' : 'success' ?>" title="<?= $active ? 'Deactivate' : 'Activate' ?>">
                                            <i class="fas fa-<?= $active ? 'pause' : 'play' ?>"></i>
                                        </button>
                                    </form>
                                    <a href="<?= BASE_URL ?>admin/ai-calling/training" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
