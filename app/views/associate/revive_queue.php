<?php
$rows = $rows ?? [];
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h4 class="mb-1"><i class="fas fa-fire text-warning me-2"></i><?= __('assoc_rev_title', [], 'Revival Queue') ?></h4>
            <small class="text-muted"><?= __('assoc_rev_sub', [], 'Open leads untouched 30+ days — free pipeline, second touch') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/associate/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i><?= __('assoc_rev_back', [], 'Back to Leads') ?></a>
    </div>
    <?php if (empty($rows)): ?>
        <div class="card border-0 shadow-sm mt-3"><div class="card-body text-center py-5 text-muted">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h5><?= __('assoc_rev_clean', [], 'Nothing cold!') ?></h5>
            <p class="mb-0"><?= __('assoc_rev_clean_desc', [], 'Every open lead was touched in the last 30 days.') ?></p>
        </div></div>
    <?php else: ?>
        <form method="POST" action="<?= BASE_URL ?>/associate/leads/bulk-whatsapp" class="card border-0 shadow-sm mt-3">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-bold"><?= count($rows) ?> <?= __('assoc_rev_cold', [], 'cold leads') ?></span>
                <button type="submit" class="btn btn-success btn-sm"><i class="fab fa-whatsapp me-1"></i><?= __('assoc_rev_broadcast', [], 'WhatsApp Selected') ?></button>
            </div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="bg-light"><tr><th></th><th><?= __('assoc_rev_th_lead', [], 'Lead') ?></th><th><?= __('assoc_rev_th_status', [], 'Status') ?></th><th><?= __('assoc_rev_th_idle', [], 'Idle') ?></th><th></th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input" name="lead_ids[]" value="<?= (int)$r['id'] ?>" checked></td>
                        <td><strong><?= htmlspecialchars($r['name'] ?? '') ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['phone'] ?? '') ?></small></td>
                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($r['status'] ?? '') ?></span></td>
                        <td><span class="badge bg-warning text-dark"><?= (int)$r['idle_days'] ?>d</span></td>
                        <td><a href="<?= BASE_URL ?>/associate/leads/<?= (int)$r['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div></div>
        </form>
    <?php endif; ?>
</div>
