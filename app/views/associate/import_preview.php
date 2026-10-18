<?php
$rows = $rows ?? [];
$skipped = $skipped ?? [];
$validCount = (int)($valid_count ?? count($rows));
$skippedCount = (int)($skipped_count ?? count($skipped));
$token = $token ?? '';
?>
<div class="container-fluid px-4 py-3">
    <div class="mb-1">
        <h4 class="mb-1"><i class="fas fa-eye text-primary me-2"></i><?= __('assoc_ip_title', [], 'Preview Import') ?></h4>
        <small class="text-muted"><?= sprintf(__('assoc_ip_sub', [], '%d valid rows, %d will be skipped. Nothing is saved yet.'), $validCount, $skippedCount) ?></small>
    </div>
    <div class="card border-0 shadow-sm mt-3"><div class="card-header bg-white fw-bold"><?= __('assoc_ip_valid', [], 'First valid rows') ?></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0">
            <thead class="bg-light"><tr><th><?= __('assoc_ip_th_name', [], 'Name') ?></th><th><?= __('assoc_ip_th_phone', [], 'Phone') ?></th><th><?= __('assoc_ip_th_email', [], 'Email') ?></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr><td><?= htmlspecialchars($r['name'] ?? '') ?></td><td><?= htmlspecialchars($r['phone'] ?? '') ?></td><td><?= htmlspecialchars($r['email'] ?? '') ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div>
    </div>
    <?php if (!empty($skipped)): ?>
    <div class="card border-0 shadow-sm mt-3"><div class="card-header bg-white fw-bold text-warning"><i class="fas fa-exclamation-triangle me-2"></i><?= __('assoc_ip_skipped', [], 'Skipped rows') ?></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-sm mb-0">
            <thead class="bg-light"><tr><th><?= __('assoc_ip_th_row', [], 'Row') ?></th><th><?= __('assoc_ip_th_why', [], 'Why') ?></th></tr></thead>
            <tbody>
            <?php foreach ($skipped as $s): ?>
                <tr><td><?= (int)($s['row'] ?? 0) ?></td><td><?= htmlspecialchars($s['reason'] ?? '') ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div>
    </div>
    <?php endif; ?>
    <div class="d-flex gap-2 mt-3">
        <form method="POST" action="<?= BASE_URL ?>/associate/leads/import/confirm">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <button type="submit" class="btn btn-success" <?= $validCount > 0 ? '' : 'disabled' ?>><i class="fas fa-check me-1"></i><?= sprintf(__('assoc_ip_confirm', [], 'Import %d leads'), $validCount) ?></button>
        </form>
        <a href="<?= BASE_URL ?>/associate/leads/import" class="btn btn-outline-secondary"><?= __('assoc_ip_cancel', [], 'Cancel') ?></a>
    </div>
</div>
