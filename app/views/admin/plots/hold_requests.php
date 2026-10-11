<?php
/** @var array $requests */
/** @var int $pending_count */
$requests = $requests ?? [];
$pending_count = $pending_count ?? 0;
$base = defined('BASE_URL') ? BASE_URL : '';
$colors = ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger', 'expired' => 'secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-inbox me-2"></i>Public Hold Requests</h4>
        <p class="text-muted mb-0">From the shareable live layout — approve to lock the plot for 24h</p>
    </div>
    <a href="<?= $base ?>/admin/plots/map" class="btn btn-outline-secondary btn-sm"><i class="fas fa-map me-1"></i>Master Map</a>
</div>

<div class="aps-cp-card mb-4">
    <div class="aps-cp-card-body py-3">
        <strong class="h4 text-warning"><?= (int)$pending_count ?></strong>
        <span class="text-muted">pending approval</span>
    </div>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="text-center text-muted py-4">No hold requests yet. Share a live layout link with <code>?ref=YOURCODE</code>.</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr>
                <th>When</th><th>Plot</th><th>Customer</th><th>Ref</th><th>Plot Now</th><th>Status</th><th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($requests as $r): ?>
                <tr>
                    <td class="small"><?= date('d M Y H:i', strtotime($r['created_at'])) ?></td>
                    <td><strong><?= htmlspecialchars($r['plot_number'] ?? ('#' . $r['plot_id'])) ?></strong>
                        <small class="text-muted d-block"><?= htmlspecialchars($r['colony_name'] ?: '') ?></small></td>
                    <td><div class="fw-medium"><?= htmlspecialchars($r['name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($r['phone']) ?></small></td>
                    <td><?= $r['ref_code'] ? '<span class="badge bg-info">' . htmlspecialchars($r['ref_code']) . '</span>' : '<span class="text-muted">—</span>' ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($r['plot_status'] ?? '?') ?></span></td>
                    <td><span class="badge bg-<?= $colors[$r['status']] ?? 'secondary' ?>"><?= ucfirst($r['status']) ?></span>
                        <?php if (!empty($r['admin_note'])): ?><small class="d-block text-muted"><?= htmlspecialchars($r['admin_note']) ?></small><?php endif; ?></td>
                    <td>
                        <?php if (($r['status'] ?? '') === 'pending'): ?>
                        <div class="d-flex gap-1">
                            <form method="POST" action="<?= $base ?>/admin/plots/hold-requests/<?= (int)$r['id'] ?>/approve">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button class="btn btn-sm btn-success" title="Approve → 24h hold"><i class="fas fa-check"></i></button>
                            </form>
                            <form method="POST" action="<?= $base ?>/admin/plots/hold-requests/<?= (int)$r['id'] ?>/reject">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button class="btn btn-sm btn-outline-danger" title="Reject"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
