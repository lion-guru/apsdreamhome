<?php
$page_title = $page_title ?? 'Cash Collections';
$page_heading = $page_heading ?? 'On-Field Cash Collection & Reconciliation';
$stats = $stats ?? [];
$collections = $collections ?? [];
$collectors = $collectors ?? [];
$filters = $filters ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-money-bill-wave me-2"></i>Cash Collections</h2>
            <p class="text-muted mb-0">Field agent collection receipts & reconciliation</p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/cash-collections/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Submit Receipt</a>
            <a href="<?= BASE_URL ?>/admin/cash-collections/reconciliations" class="btn btn-outline-secondary"><i class="fas fa-balance-scale me-2"></i>Reconciliation</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Total Collected</p>
                    <h3 class="text-success">₹<?= number_format($stats['total_amount'] ?? 0) ?></h3>
                    <small class="text-muted"><?= number_format($stats['total_submitted'] ?? 0) ?> receipts</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Today's Collection</p>
                    <h3 class="text-info">₹<?= number_format($stats['today_amount'] ?? 0) ?></h3>
                    <small class="text-muted"><?= number_format($stats['today_count'] ?? 0) ?> receipts today</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Pending Verification</p>
                    <h3 class="text-warning"><?= number_format($stats['pending_count'] ?? 0) ?></h3>
                    <small class="text-muted">₹<?= number_format($stats['pending_amount'] ?? 0) ?> awaiting</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Active Collectors</p>
                    <h3 class="text-primary"><?= number_format($stats['active_collectors'] ?? 0) ?></h3>
                    <small class="text-muted">Last 7 days</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/cash-collections" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <?php foreach (['submitted' => 'Submitted', 'verified' => 'Verified', 'rejected' => 'Rejected', 'reconciled' => 'Reconciled'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= ($filters['status'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Collector</label>
                    <select name="collector_id" class="form-select">
                        <option value="">All Collectors</option>
                        <?php foreach ($collectors as $c): ?>
                            <option value="<?= $c['collector_id'] ?>" <?= ($filters['collector_id'] ?? '') == $c['collector_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?> (<?= number_format($c['collection_count'] ?? 0) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From</label>
                    <input type="date" name="from" class="form-control" value="<?= $filters['from'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To</label>
                    <input type="date" name="to" class="form-control" value="<?= $filters['to'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Collection Receipts (<?= count($collections) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
                            <th>ID</th>
                            <th>Collector</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($collections)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2"></i>No collections found</td></tr>
                        <?php else: foreach ($collections as $c): ?>
                            <tr>
                                <?php if (($c['status'] ?? '') === 'submitted'): ?>
                                    <td><input type="checkbox" name="bulk_ids[]" value="<?= $c['id'] ?>" class="form-check-input bulk-check"></td>
                                <?php else: ?>
                                    <td></td>
                                <?php endif; ?>
                                <td>#<?= $c['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($c['collector_name'] ?? 'N/A') ?></strong>
                                    <?php if (!empty($c['booking_number'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($c['booking_number']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($c['customer_name'] ?? '') ?></td>
                                <td><strong class="text-success">₹<?= number_format($c['amount'] ?? 0) ?></strong></td>
                                <td><?= date('d M Y', strtotime($c['collection_date'])) ?></td>
                                <td><span class="badge bg-light text-dark"><?= ucfirst($c['payment_method'] ?? 'cash') ?></span></td>
                                <td>
                                    <?php
                                    $colors = ['submitted' => 'warning', 'verified' => 'success', 'rejected' => 'danger', 'reconciled' => 'info'];
                                    ?>
                                    <span class="badge bg-<?= $colors[$c['status']] ?? 'secondary' ?>"><?= ucfirst($c['status'] ?? '') ?></span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/admin/cash-collections/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                    <?php if (($c['status'] ?? '') === 'submitted'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/cash-collections/verify" style="display:inline">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success" title="Verify"><i class="fas fa-check"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                    <?php if (!empty($collections) && array_filter($collections, fn($c) => ($c['status'] ?? '') === 'submitted')): ?>
                    <tfoot>
                        <tr>
                            <td colspan="2">
                                <button type="button" class="btn btn-sm btn-success" id="bulkVerifyBtn" disabled>
                                    <i class="fas fa-check-double me-1"></i>Bulk Verify Selected
                                </button>
                            </td>
                            <td colspan="8"></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<form id="bulkVerifyForm" method="POST" action="<?= BASE_URL ?>/admin/cash-collections/bulk-verify" style="display:none">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
    <input type="hidden" name="ids[]" id="bulkIdsInput" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('selectAll');
    var bulkBtn = document.getElementById('bulkVerifyBtn');
    var bulkForm = document.getElementById('bulkVerifyForm');
    var bulkIdsInput = document.getElementById('bulkIdsInput');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.bulk-check').forEach(function(cb) { cb.checked = selectAll.checked; });
            updateBulkBtn();
        });
    }
    document.querySelectorAll('.bulk-check').forEach(function(cb) {
        cb.addEventListener('change', updateBulkBtn);
    });

    function updateBulkBtn() {
        var checked = document.querySelectorAll('.bulk-check:checked');
        if (bulkBtn) bulkBtn.disabled = checked.length === 0;
    }

    if (bulkBtn) {
        bulkBtn.addEventListener('click', function() {
            var ids = [];
            document.querySelectorAll('.bulk-check:checked').forEach(function(cb) { ids.push(cb.value); });
            bulkIdsInput.value = ids.join(',');
            bulkForm.submit();
        });
    }
});
</script>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';
