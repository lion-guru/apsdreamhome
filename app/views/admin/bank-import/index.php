<?php $page_title = $page_title ?? 'Bank Statement Import'; $page_heading = $page_heading ?? 'Bank Statement Import'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-csv me-2 text-primary"></i>Bank Statement Import</h2>
        <a href="<?= BASE_URL ?>/admin/bank-import/upload" class="btn btn-primary"><i class="fas fa-upload me-1"></i>Upload New CSV</a>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3"><i class="fas fa-file-import text-primary fs-5"></i></div>
                        </div>
                        <div>
                            <div class="text-muted small">Total Imports</div>
                            <div class="fs-4 fw-bold"><?= (int)($total_imports ?? 0) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-info bg-opacity-10 p-3"><i class="fas fa-list text-info fs-5"></i></div>
                        </div>
                        <div>
                            <div class="text-muted small">Total Transactions</div>
                            <div class="fs-4 fw-bold"><?= number_format((int)($total_transactions ?? 0)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3"><i class="fas fa-check-circle text-success fs-5"></i></div>
                        </div>
                        <div>
                            <div class="text-muted small">Match Rate</div>
                            <div class="fs-4 fw-bold"><?= (float)($match_rate ?? 0) ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3"><i class="fas fa-exclamation-triangle text-warning fs-5"></i></div>
                        </div>
                        <div>
                            <div class="text-muted small">Unmatched</div>
                            <div class="fs-4 fw-bold"><?= number_format((int)($total_unmatched ?? 0)) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Imports Table -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-history me-1"></i> Import History</span>
        </div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Filename</th>
                        <th>Bank Account</th>
                        <th>Import Date</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Matched</th>
                        <th class="text-center">Unmatched</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($imports)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">No imports yet. <a href="<?= BASE_URL ?>/admin/bank-import/upload">Upload your first CSV</a></td></tr>
                <?php else: foreach ($imports as $imp): ?>
                    <tr>
                        <td><?= (int)$imp['id'] ?></td>
                        <td><i class="fas fa-file-csv text-success me-1"></i><?= htmlspecialchars($imp['original_filename'] ?? $imp['filename']) ?></td>
                        <td><?= htmlspecialchars(($imp['account_name'] ?? '—') . ' (' . ($imp['bank_name'] ?? '') . ')') ?></td>
                        <td><?= htmlspecialchars($imp['import_date'] ?? $imp['created_at']) ?></td>
                        <td class="text-center"><span class="badge bg-secondary"><?= (int)($imp['total_rows'] ?? 0) ?></span></td>
                        <td class="text-center"><span class="badge bg-success"><?= (int)($imp['matched_rows'] ?? 0) ?></span></td>
                        <td class="text-center"><span class="badge bg-warning text-dark"><?= (int)($imp['unmatched_rows'] ?? 0) ?></span></td>
                        <td>
                            <?php if (($imp['status'] ?? '') === 'completed'): ?>
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Completed</span>
                            <?php elseif (($imp['status'] ?? '') === 'processing'): ?>
                                <span class="badge bg-info"><i class="fas fa-spinner fa-spin me-1"></i>Processing</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Failed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/admin/bank-import/<?= (int)$imp['id'] ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                <?php if (($imp['status'] ?? '') === 'completed' && (int)($imp['unmatched_rows'] ?? 0) > 0): ?>
                                    <form method="post" action="<?= BASE_URL ?>/admin/bank-import/<?= (int)$imp['id'] ?>/match" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <button type="submit" class="btn btn-outline-success" title="Auto-Match" data-aps-confirm="Auto-match transactions?" aria-label="Auto"><i class="fas fa-magic"></i></button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="<?= BASE_URL ?>/admin/bank-import/<?= (int)$imp['id'] ?>/delete" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <button type="submit" class="btn btn-outline-danger" title="Delete" data-aps-confirm="Delete this import and all its transactions?" aria-label="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
