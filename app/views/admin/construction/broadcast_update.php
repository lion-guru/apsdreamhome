<?php
/** Milestone broadcast to booked buyers (WhatsApp, best-effort). */
/** @var array $colonies */
/** @var array $history */
$colonies = $colonies ?? [];
$history = $history ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<div class="mb-4">
    <h4 class="mb-1"><i class="fas fa-bullhorn me-2"></i>Milestone Broadcast</h4>
    <p class="text-muted mb-0">1-click WhatsApp update to all booked buyers of a colony (with site photo)</p>
</div>
<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <form method="POST" action="<?= $base ?>/admin/construction/broadcast-update/send" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label small">Colony *</label>
                <select name="colony_id" class="form-select" required>
                    <?php foreach ($colonies as $c): ?><option value="<?= (int)$c['id'] ?>"><?= $e($c['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label small">Milestone *</label><input type="text" name="milestone_title" class="form-control" required maxlength="150" placeholder="e.g. Damar road done"></div>
            <div class="col-md-4"><label class="form-label small">Message (blank = auto)</label><input type="text" name="message" class="form-control" maxlength="2000"></div>
            <div class="col-md-2"><label class="form-label small">Site photo (≤5MB)</label><input type="file" name="site_photo" class="form-control" accept=".jpg,.jpeg,.png,.webp"></div>
            <div class="col-md-2 pt-4"><button type="submit" class="btn btn-success w-100"><i class="fas fa-paper-plane me-1"></i>Broadcast</button></div>
        </div>
    </form>
</div></div>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>When</th><th>Colony</th><th>Milestone</th><th class="text-end">Buyers</th><th class="text-end">Sent</th><th class="text-end">Failed</th><th>Photo</th></tr></thead>
        <tbody>
        <?php if (empty($history)): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No broadcasts yet.</td></tr>
        <?php else: foreach ($history as $h): ?>
            <tr>
                <td class="small"><?= $e($h['created_at'] ?? '') ?></td>
                <td><?= $e($h['colony_name'] ?? '') ?></td>
                <td class="fw-medium"><?= $e($h['milestone_title']) ?></td>
                <td class="text-end"><?= (int)($h['recipients_total'] ?? 0) ?></td>
                <td class="text-end text-success"><?= (int)($h['sent_count'] ?? 0) ?></td>
                <td class="text-end text-muted"><?= (int)($h['failed_count'] ?? 0) ?></td>
                <td><?= !empty($h['photo_path']) ? '<a target="_blank" href="' . $base . '/' . $e($h['photo_path']) . '">view</a>' : '<span class="text-muted">—</span>' ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
</div></div>
