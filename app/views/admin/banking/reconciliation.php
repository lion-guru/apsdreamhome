<?php
$transactions = $transactions ?? [];
$banks = $banks ?? [];
$page_title = 'Bank Reconciliation';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-balance-scale me-2"></i>Bank Reconciliation</h1>
            <p class="text-muted mb-0">Review and reconcile pending bank transactions</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/admin/banking" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Banking
        </a>
    </div>

    <?php require __DIR__ . '/../partials/search_bar.php'; ?>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?php echo BASE_URL; ?>/admin/banking/reconciliation" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control" name="date_from" value="<?php echo htmlspecialchars($_GET['date_from'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control" name="date_to" value="<?php echo htmlspecialchars($_GET['date_to'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bank</label>
                    <select class="form-select" name="bank_name">
                        <option value="">All Banks</option>
                        <?php foreach ($banks as $b): ?>
                            <option value="<?php echo htmlspecialchars($b['bank_name'] ?? $b); ?>" <?php echo (isset($_GET['bank_name']) && $_GET['bank_name'] === ($b['bank_name'] ?? $b)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($b['bank_name'] ?? $b); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <option value="credit" <?php echo (isset($_GET['type']) && $_GET['type'] === 'credit') ? 'selected' : ''; ?>>Credit</option>
                        <option value="debit" <?php echo (isset($_GET['type']) && $_GET['type'] === 'debit') ? 'selected' : ''; ?>>Debit</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?php echo BASE_URL; ?>/admin/banking/reconciliation" class="btn btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Unreconciled Transactions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-hourglass-half me-2"></i>Pending Reconciliation</h5>
            <button class="btn btn-success" id="bulkReconcileBtn" onclick="bulkReconcile()" disabled>
                <i class="fas fa-check-double me-1"></i>Bulk Reconcile Selected
            </button>
        </div>
        <div class="card-body p-0">
            <form id="bulkReconcileForm" method="POST" action="<?php echo BASE_URL; ?>/admin/banking/bulk-reconcile">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Bank Name</th>
                                <th>Account</th>
                                <th>Cheque No</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                        <h5>All Caught Up!</h5>
                                        <p class="mb-0">No pending transactions to reconcile.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><input type="checkbox" class="recon-checkbox" name="transaction_ids[]" value="<?php echo $t['id']; ?>" onchange="updateBulkBtn()"></td>
                                        <td><?php echo isset($t['date']) ? date('d M Y', strtotime($t['date'])) : '-'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo ($t['type'] ?? '') === 'credit' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($t['type'] ?? '-'); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold">₹<?php echo number_format($t['amount'] ?? 0, 2); ?></td>
                                        <td><?php echo htmlspecialchars($t['bank_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars(substr($t['account_number'] ?? '', -4) ? '****' . substr($t['account_number'] ?? '', -4) : '-'); ?></td>
                                        <td><?php echo htmlspecialchars($t['cheque_number'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars(mb_substr($t['description'] ?? '', 0, 40)); ?></td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>/admin/banking/show/<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/admin/banking/reconcile/<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-success" title="Reconcile">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleSelectAll() {
    const checked = document.getElementById('selectAll').checked;
    document.querySelectorAll('.recon-checkbox').forEach(cb => cb.checked = checked);
    updateBulkBtn();
}

function updateBulkBtn() {
    const count = document.querySelectorAll('.recon-checkbox:checked').length;
    document.getElementById('bulkReconcileBtn').disabled = count === 0;
    document.getElementById('bulkReconcileBtn').innerHTML = '<i class="fas fa-check-double me-1"></i>Bulk Reconcile (' + count + ')';
}

function bulkReconcile() {
    const count = document.querySelectorAll('.recon-checkbox:checked').length;
    if (count === 0) return;
    apsConfirm('Mark ' + count + ' selected transaction(s) as reconciled?').then(function(ok) {
        if (!ok) return;
        document.getElementById('bulkReconcileForm').submit();
    });
</script>
