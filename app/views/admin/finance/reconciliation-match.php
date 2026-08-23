<?php $page_title = $page_title ?? 'Match Items'; $page_heading = $page_heading ?? 'Match Statement Items'; $recon = $recon ?? null; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Match Statement Items</h2>
        <div>
            <?php if ($recon && ($recon['status'] ?? '') !== 'completed'): ?>
            <form method="post" action="<?= BASE_URL ?>/admin/finance/reconciliation-complete" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="id" value="<?= (int)($recon['id'] ?? 0) ?>">
                <button class="btn btn-success"><i class="fas fa-check-double me-1"></i>Complete Reconciliation</button>
            </form>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/finance/reconciliation" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <?php if ($recon): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Bank</div><div class="aps-cp-stat-meta"><?= htmlspecialchars($recon['account_name'] ?? '-') ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Statement Balance</div><div class="aps-cp-stat-value">₹<?= number_format((float)($recon['statement_balance'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Book Balance</div><div class="aps-cp-stat-value">₹<?= number_format((float)($recon['book_balance'] ?? 0), 2) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Difference</div><div class="aps-cp-stat-value text-warning">₹<?= number_format((float)($recon['difference'] ?? 0), 2) ?></div></div></div></div>
    </div>
    <?php endif; ?>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Reconciliation Items</h5></div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Date</th><th>Description</th><th class="text-end">Amount</th><th>Type</th><th>Status</th><th>Matched Cashbook</th><th></th></tr></thead>
                <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No items — add some from the bank statement</td></tr>
                <?php else: foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['transaction_date'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($it['description'] ?? '-') ?></td>
                        <td class="text-end">₹<?= number_format((float)($it['amount'] ?? 0), 2) ?></td>
                        <td><span class="badge bg-<?= ($it['type'] ?? '') === 'credit' ? 'success' : 'danger' ?>"><?= htmlspecialchars($it['type'] ?? '-') ?></span></td>
                        <td><span class="badge bg-<?= ($it['status'] ?? '') === 'matched' ? 'success' : 'warning' ?>"><?= htmlspecialchars($it['status'] ?? 'pending') ?></span></td>
                        <td><?= !empty($it['matched_cashbook_id']) ? '#' . (int)$it['matched_cashbook_id'] : '-' ?></td>
                        <td>
                            <?php if (($it['status'] ?? '') !== 'matched'): ?>
                            <form method="post" action="<?= BASE_URL ?>/admin/finance/reconciliation-item-match" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="item_id" value="<?= (int)$it['id'] ?>">
                                <input type="hidden" name="recon_id" value="<?= (int)($recon['id'] ?? 0) ?>">
                                <input type="hidden" name="status" value="matched">
                                <input type="number" name="cashbook_id" placeholder="CB #" class="form-control form-control-sm d-inline-block style-31652">
                                <button class="btn btn-sm btn-outline-success" aria-label="Link"><i class="fas fa-link"></i></button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
