<?php
$rows = $rows ?? [];
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h4 class="mb-1"><i class="fas fa-trash-alt text-secondary me-2"></i><?= __('assoc_trash_title', [], 'Trash') ?></h4>
            <small class="text-muted"><?= __('assoc_trash_sub', [], 'Deleted leads stay here with everything intact — restore anytime') ?></small>
        </div>
        <div class="d-flex gap-2">
            <form method="POST" action="<?= BASE_URL ?>/associate/leads/trash/empty" onsubmit="return confirm('<?= __('assoc_trash_empty_confirm', [], 'Permanently delete trashed leads older than 30 days?') ?>')">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i><?= __('assoc_trash_empty', [], 'Empty 30d+ Trash') ?></button>
            </form>
            <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('assoc_trash_back', [], 'Back to Leads') ?></a>
        </div>
    </div>
    <div class="card border-0 shadow-sm mt-3"><div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-trash-alt fa-3x mb-3"></i>
                <h5><?= __('assoc_trash_empty_state', [], 'Trash is empty') ?></h5>
                <p class="small mb-0"><?= __('assoc_trash_empty_desc', [], 'Deleted leads land here and can be restored with notes intact.') ?></p>
            </div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="bg-light"><tr><th><?= __('assoc_trash_th_who', [], 'Who') ?></th><th><?= __('assoc_trash_th_status', [], 'Status') ?></th><th><?= __('assoc_trash_th_deleted', [], 'Deleted') ?></th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['name'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['phone'] ?? '') ?></small></td>
                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
                    <td><small class="text-muted"><?= htmlspecialchars($r['deleted_at'] ?? '') ?></small></td>
                    <td class="text-end text-nowrap">
                        <form method="POST" action="<?= BASE_URL ?>/associate/leads/<?= (int)$r['id'] ?>/restore" class="d-inline">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-undo me-1"></i><?= __('assoc_trash_restore', [], 'Restore') ?></button>
                        </form>
                        <form method="POST" action="<?= BASE_URL ?>/associate/leads/<?= (int)$r['id'] ?>/destroy" class="d-inline" onsubmit="return confirm('<?= __('assoc_trash_destroy_confirm', [], 'Permanently delete? Notes and history go too.') ?>')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div></div>
</div>
