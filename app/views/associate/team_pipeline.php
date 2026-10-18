<?php
$rows = $rows ?? [];
$tot = ['leads' => 0, 'open' => 0, 'won' => 0, 'bookings' => 0, 'revenue' => 0.0];
foreach ($rows as $r) {
    $tot['leads'] += $r['leads'];
    $tot['open'] += $r['open'];
    $tot['won'] += $r['won'];
    $tot['bookings'] += $r['bookings'];
    $tot['revenue'] += $r['revenue'];
}
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <div>
            <h4 class="mb-1"><i class="fas fa-users text-primary me-2"></i><?= __('assoc_tp_title', [], 'Team Pipeline') ?></h4>
            <small class="text-muted"><?= __('assoc_tp_sub', [], 'Direct downline performance — same team the Network Tree shows') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/associate/team" class="btn btn-outline-secondary btn-sm"><i class="fas fa-sitemap me-1"></i><?= __('assoc_tp_tree', [], 'Network Tree') ?></a>
    </div>
    <div class="card border-0 shadow-sm mt-3"><div class="card-body p-0">
        <?php if (empty($rows)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h5><?= __('assoc_tp_empty', [], 'No team members yet') ?></h5>
                <p class="small mb-0"><?= __('assoc_tp_empty_desc', [], 'Share your referral link to grow your team.') ?></p>
            </div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="bg-light"><tr><th><?= __('assoc_tp_th_member', [], 'Member') ?></th><th class="text-end"><?= __('assoc_tp_th_leads', [], 'Leads') ?></th><th class="text-end"><?= __('assoc_tp_th_open', [], 'Open') ?></th><th class="text-end"><?= __('assoc_tp_th_won', [], 'Won') ?></th><th class="text-end"><?= __('assoc_tp_th_bookings', [], 'Bookings') ?></th><th class="text-end"><?= __('assoc_tp_th_revenue', [], 'Revenue') ?></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr><td><strong><?= htmlspecialchars($r['name'] ?? '') ?></strong></td><td class="text-end"><?= number_format($r['leads']) ?></td><td class="text-end"><?= number_format($r['open']) ?></td><td class="text-end text-success fw-bold"><?= number_format($r['won']) ?></td><td class="text-end"><?= number_format($r['bookings']) ?></td><td class="text-end">₹<?= number_format($r['revenue'], 0) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-light fw-bold"><tr><td><?= __('assoc_tp_total', [], 'Total') ?></td><td class="text-end"><?= number_format($tot['leads']) ?></td><td class="text-end"><?= number_format($tot['open']) ?></td><td class="text-end"><?= number_format($tot['won']) ?></td><td class="text-end"><?= number_format($tot['bookings']) ?></td><td class="text-end">₹<?= number_format($tot['revenue'], 0) ?></td></tr></tfoot>
        </table></div>
        <?php endif; ?>
    </div></div>
    <?php $esc = $escalations ?? []; ?>
    <?php if (!empty($esc)): ?>
    <div class="card border-danger shadow-sm mt-3">
        <div class="card-header bg-danger text-white fw-bold"><i class="fas fa-bell me-2"></i><?= __('assoc_tp_esc', [], 'Needs Attention — overdue follow-ups in your team') ?></div>
        <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="bg-light"><tr><th><?= __('assoc_tp_esc_who', [], 'Owner') ?></th><th><?= __('assoc_tp_esc_lead', [], 'Lead') ?></th><th><?= __('assoc_tp_esc_due', [], 'Due') ?></th></tr></thead>
            <tbody>
            <?php foreach ($esc as $e): ?>
                <tr><td><?= htmlspecialchars($e['owner_name'] ?? '') ?> <small class="text-muted">(<?= htmlspecialchars($e['followup_type'] ?? '') ?>)</small></td><td><?= htmlspecialchars($e['lead_name'] ?? ('#' . $e['id'])) ?> <small class="text-muted"><?= htmlspecialchars($e['phone'] ?? '') ?></small></td><td><span class="badge bg-danger"><?= htmlspecialchars($e['followup_date'] ?? '') ?></span></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div>
    </div>
    <?php endif; ?>
</div>
