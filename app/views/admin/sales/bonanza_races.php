<?php
/** Bonanza race admin (list + launch). */
/** @var array $rows */
$rows = $rows ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<div class="mb-4">
    <h4 class="mb-1"><i class="fas fa-trophy me-2"></i>Bonanza Races</h4>
    <p class="text-muted mb-0">Festive booking contests — progress counts live bookings per associate</p>
</div>
<div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
    <h6 class="fw-bold mb-3">Launch Race</h6>
    <form method="POST" action="<?= $base ?>/admin/sales/bonanza-races/store">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label small">Race name *</label><input type="text" name="race_name" class="form-control" required maxlength="150" placeholder="e.g. Diwali Dhamaka"></div>
            <div class="col-md-2"><label class="form-label small">Start *</label><input type="date" name="start_date" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label small">End *</label><input type="date" name="end_date" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label small">Target bookings *</label><input type="number" min="1" name="target_bookings" class="form-control" value="5" required></div>
            <div class="col-md-2"><label class="form-label small">Prize</label><input type="text" name="prize_text" class="form-control" maxlength="255" placeholder="e.g. Royal Enfield"></div>
            <div class="col-md-1 pt-4"><button type="submit" class="btn btn-primary w-100">Go</button></div>
        </div>
    </form>
</div></div>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Race</th><th>Window</th><th class="text-end">Target</th><th>Prize</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No races yet — launch the first above.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td class="fw-medium"><?= $e($r['race_name']) ?></td>
                <td class="small"><?= $e($r['start_date']) ?> → <?= $e($r['end_date']) ?></td>
                <td class="text-end"><?= (int)($r['target_bookings'] ?? 0) ?> bookings</td>
                <td><?= $e($r['prize_text'] ?? '—') ?></td>
                <td><span class="badge bg-<?= !empty($r['is_active']) ? 'success' : 'secondary' ?>"><?= !empty($r['is_active']) ? 'Active' : 'Off' ?></span></td>
                <td><a class="btn btn-sm btn-outline-primary" href="<?= $base ?>/admin/sales/bonanza-races/<?= (int)$r['id'] ?>/leaderboard">Leaderboard</a></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table></div>
</div></div>
