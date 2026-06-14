<?php
$page_title = $page_title ?? 'Reconciliation Sessions';
$page_heading = $page_heading ?? 'Collection Reconciliation';
$reconciliations = $reconciliations ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-balance-scale me-2"></i>Reconciliation Sessions</h2>
            <p class="text-muted mb-0">Daily collection reconciliation per field agent</p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/cash-collections/reconciliations/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Start Session</a>
            <a href="<?= BASE_URL ?>/admin/cash-collections" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Collections</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-success">₹<?= number_format(array_sum(array_column($reconciliations, 'total_submitted'))) ?></h3>
                    <p class="text-muted mb-0">Total Submitted</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-primary">₹<?= number_format(array_sum(array_column($reconciliations, 'total_verified'))) ?></h3>
                    <p class="text-muted mb-0">Total Verified</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?= count(array_filter($reconciliations, fn($r) => ($r['status'] ?? '') === 'open')) ?></h3>
                    <p class="text-muted mb-0">Open Sessions</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>All Sessions (<?= count($reconciliations) ?>)</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Collector</th>
                            <th>Date</th>
                            <th>Submitted</th>
                            <th>Verified</th>
                            <th>Rejected</th>
                            <th>Discrepancy</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reconciliations)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No reconciliation sessions yet</td></tr>
                        <?php else: foreach ($reconciliations as $r): ?>
                            <tr>
                                <td>#<?= $r['id'] ?></td>
                                <td><strong><?= htmlspecialchars($r['collector_name'] ?? 'N/A') ?></strong></td>
                                <td><?= date('d M Y', strtotime($r['session_date'])) ?></td>
                                <td>
                                    <strong>₹<?= number_format($r['total_submitted'] ?? 0) ?></strong>
                                </td>
                                <td><span class="text-success">₹<?= number_format($r['total_verified'] ?? 0) ?></span></td>
                                <td><span class="text-danger">₹<?= number_format($r['total_rejected'] ?? 0) ?></span></td>
                                <td>
                                    <?php if (($r['discrepancy_amount'] ?? 0) != 0): ?>
                                        <span class="badge bg-warning text-dark">₹<?= number_format($r['discrepancy_amount']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">₹0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $colors = ['open' => 'success', 'closed' => 'secondary', 'discrepancy' => 'warning'];
                                    ?>
                                    <span class="badge bg-<?= $colors[$r['status']] ?? 'secondary' ?> px-3 py-2"><?= ucfirst($r['status'] ?? '') ?></span>
                                </td>
                                <td>
                                    <?php if (($r['status'] ?? '') === 'open'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/cash-collections/reconciliations/close" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Close Session" onclick="return confirm('Close this reconciliation session?')"><i class="fas fa-lock"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Closed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';
