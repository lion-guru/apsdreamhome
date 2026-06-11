<?php
$pageTitle = $pageTitle ?? 'Batch Payout';
$base = $base ?? (defined('BASE_URL') ? BASE_URL : '/apsdreamhome');
$pending_count = $pending_count ?? 0;
$total_amount = $total_amount ?? 0;
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-money-bill-wave me-2 text-success"></i>Batch Payout</h1>
        <a href="<?= $base ?>/admin/commission" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-primary text-uppercase mb-1">Pending Commissions</div><div class="h5 mb-0 fw-bold"><?= number_format($pending_count) ?></div></div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body aps-cp-card-body">
                    <div class="row align-items-center">
                        <div class="col"><div class="text-xs fw-bold text-success text-uppercase mb-1">Total Payout Amount</div><div class="h5 mb-0 fw-bold">₹<?= number_format($total_amount, 2) ?></div></div>
                        <div class="col-auto"><i class="fas fa-rupee-sign fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Process Payout</h6></div>
        <div class="card-body aps-cp-card-body">
            <?php if ($pending_count < 1): ?>
                <p class="text-muted text-center py-4"><i class="fas fa-check-circle fa-2x d-block mb-2 text-success"></i>No pending payouts to process.</p>
            <?php else: ?>
                <form method="POST" action="<?= $base ?>/admin/commission/payout">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label class="form-label">Select users for Payout</label>
                        <select name="agent_ids[]" class="form-select" multiple size="5">
                            <option value="all" selected>All Pending users</option>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple. Choose "All Pending users" to process all.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Reference</label>
                        <input type="text" name="payment_ref" class="form-control" placeholder="e.g., Bank Transfer #12345">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Process payout for selected users?')"><i class="fas fa-paper-plane me-1"></i>Process Payout</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
