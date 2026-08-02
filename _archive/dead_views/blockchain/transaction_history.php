<?php $pageTitle = $page_title ?? 'Blockchain Transaction History'; ?>
<div class="container-fluid py-4">
    <h4 class="mb-4"><i class="fas fa-history me-2"></i><?= htmlspecialchars($pageTitle) ?></h4>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-building me-2"></i>Property</h5></div>
        <div class="card-body aps-cp-card-body">
            <p class="mb-1"><strong><?= htmlspecialchars($property['title'] ?? '-') ?></strong></p>
            <p class="mb-0 text-muted"><?= htmlspecialchars($property['city'] ?? '') ?></p>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Transactions</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light"><tr><th>ID</th><th>Type</th><th>Hash</th><th>Block</th><th>Date</th></tr></thead>
                    <tbody>
                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td><?= $t['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($t['type'] ?? '-') ?></td>
                                    <td><code title="<?= htmlspecialchars($t['hash'] ?? '') ?>"><?= htmlspecialchars(substr($t['hash'] ?? '', 0, 16)) ?>...</code></td>
                                    <td><?= $t['block_number'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No transactions found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
