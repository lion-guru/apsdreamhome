<?php
$transactions = $transactions ?? [];
$stats = $stats ?? ['total'=>0,'active'=>0,'inactive'=>0,'due'=>0,'total_amount'=>0];
$filters = $filters ?? ['status'=>''];
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Recurring Journal Entries</h1>
        <div>
            <form method="POST" action="<?= BASE_URL ?>/admin/recurring/process-due" class="d-inline" onsubmit="return confirm('Process all due recurring transactions?');">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <button type="submit" class="btn btn-info me-2"><i class="fas fa-play me-2"></i>Process Due (<?= $stats['due'] ?>)</button>
            </form>
            <a href="<?= BASE_URL ?>/admin/recurring/create" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Recurring</a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body"><h3><?= $stats['total'] ?></h3><small>Total</small></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body"><h3><?= $stats['active'] ?></h3><small>Active</small></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body"><h3><?= $stats['due'] ?></h3><small>Due Now</small></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body"><h3>₹<?= number_format($stats['total_amount'] ?? 0) ?></h3><small>Monthly Value</small></div></div></div>
    </div>

    <div class="card mb-4"><div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3"><label>Status</label><select name="status" class="form-select"><option value="">All</option><option value="active" <?= $filters['status']==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $filters['status']==='inactive'?'selected':'' ?>>Inactive</option><option value="due" <?= $filters['status']==='due'?'selected':'' ?>>Due Now</option></select></div>
            <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Filter</button></div>
            <div class="col-md-2 d-flex align-items-end"><a href="<?= BASE_URL ?>/admin/recurring" class="btn btn-outline-secondary w-100">Clear</a></div>
        </form>
    </div></div>

    <div class="card"><div class="card-body p-0"><table class="table mb-0">
        <thead><tr><th>Type</th><th>Description</th><th>Amount</th><th>Frequency</th><th>Next Due</th><th>Last Run</th><th>Count</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (empty($transactions)): ?>
            <tr><td colspan="9" class="text-center text-muted py-4">No recurring transactions found</td></tr>
        <?php else: ?>
            <?php foreach ($transactions as $t): ?>
                <tr>
                    <td><?= ucfirst($t['transaction_type']) ?></td>
                    <td><?= htmlspecialchars($t['description'] ?? '—') ?></td>
                    <td>₹<?= number_format($t['amount'], 2) ?></td>
                    <td><?= ucfirst($t['frequency']) ?></td>
                    <td><?= date('M d, Y', strtotime($t['next_execution_date'])) ?></td>
                    <td><?= $t['last_execution_date'] ? date('M d, Y', strtotime($t['last_execution_date'])) : '—' ?></td>
                    <td><?= $t['execution_count'] ?><?= $t['max_executions'] ? '/' . $t['max_executions'] : '' ?></td>
                    <td>
                        <?php if ($t['is_active'] && $t['next_execution_date'] <= date('Y-m-d')): ?>
                            <span class="badge bg-warning">Due</span>
                        <?php elseif ($t['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="<?= BASE_URL ?>/admin/recurring/edit/<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                        <?php if ($t['is_active'] && $t['next_execution_date'] <= date('Y-m-d')): ?>
                        <form method="POST" action="<?= BASE_URL ?>//admin/recurring/execute/<?= $t['id'] ?>" class="d-inline" onsubmit="return confirm('Execute now?');">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button class="btn btn-sm btn-outline-success"><i class="fas fa-play"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table></div></div>
</div>