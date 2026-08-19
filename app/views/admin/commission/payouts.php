<?php
$page_title = $page_title ?? 'Commission Payouts';
$approved_commissions = $approved_commissions ?? [];
$payout_history = $payout_history ?? [];
$stats = $stats ?? ['approved_total' => 0, 'approved_count' => 0, 'paid_total' => 0, 'paid_count' => 0];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><i class="fas fa-money-bill-wave me-2 text-success"></i>Commission Payouts</h1>
        <a href="<?php echo $base; ?>/admin/commission" class="btn btn-secondary">Back</a>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-warning">₹<?php echo number_format($stats['approved_total']); ?></h4>
                    <p class="text-muted mb-0">Ready for Payout (<?php echo $stats['approved_count']; ?>)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-success">₹<?php echo number_format($stats['paid_total']); ?></h4>
                    <p class="text-muted mb-0">Already Paid (<?php echo $stats['paid_count']; ?>)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-info"><?php echo $stats['approved_count'] + $stats['paid_count']; ?></h4>
                    <p class="text-muted mb-0">Total Transactions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="text-primary">₹<?php echo number_format($stats['approved_total'] + $stats['paid_total']); ?></h4>
                    <p class="text-muted mb-0">Total Commission Value</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved Commissions Ready for Payout -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-check-circle text-warning me-2"></i>Approved — Ready for Payout</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($approved_commissions)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                <th>Associate</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Bank</th>
                                <th>IFSC</th>
                                <th>Approved</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approved_commissions as $c): ?>
                                <tr>
                                    <td><input type="checkbox" class="form-check-input payout-check" value="<?php echo $c['id']; ?>" data-amount="<?php echo $c['amount']; ?>"></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($c['associate_name'] ?? 'Unknown'); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($c['associate_email'] ?? ''); ?></small>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($c['commission_type'] ?? 'commission'); ?></span></td>
                                    <td><strong>₹<?php echo number_format(floatval($c['amount'])); ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['bank_account'] ?? 'Not set'); ?></td>
                                    <td><?php echo htmlspecialchars($c['ifsc_code'] ?? '-'); ?></td>
                                    <td><?php echo $c['approved_at'] ? date('M d, Y', strtotime($c['approved_at'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button class="btn btn-success" id="btnBulkPay" disabled onclick="bulkPay()">
                        <i class="fas fa-money-bill-wave me-2"></i>Process Selected Payouts
                    </button>
                    <span id="selectedTotal" class="ms-3 text-muted"></span>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No commissions pending payout. All approved commissions have been processed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payout History -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-history text-success me-2"></i>Payout History</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($payout_history)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Associate</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Paid On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payout_history as $h): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($h['associate_name'] ?? 'Unknown'); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($h['associate_email'] ?? ''); ?></small>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($h['commission_type'] ?? 'commission'); ?></span></td>
                                    <td>₹<?php echo number_format(floatval($h['amount'])); ?></td>
                                    <td><span class="badge bg-success">Paid</span></td>
                                    <td><?php echo $h['payout_date'] ? date('M d, Y', strtotime($h['payout_date'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No payout history yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.payout-check').forEach(cb => cb.checked = this.checked);
    updateTotal();
});
document.querySelectorAll('.payout-check').forEach(cb => cb.addEventListener('change', updateTotal));

function updateTotal() {
    let total = 0;
    let count = 0;
    document.querySelectorAll('.payout-check:checked').forEach(cb => {
        total += parseFloat(cb.dataset.amount || 0);
        count++;
    });
    document.getElementById('btnBulkPay').disabled = count === 0;
    document.getElementById('selectedTotal').textContent = count ? `${count} selected — ₹${total.toLocaleString('en-IN')}` : '';
}

function bulkPay() {
    const ids = [];
    document.querySelectorAll('.payout-check:checked').forEach(cb => ids.push(cb.value));
    if (!ids.length) return;
    apsConfirm(`Process ${ids.length} commission payouts?`).then(function(ok) {
        if (!ok) return;

        fetch('<?php echo $base; ?>/admin/commission/payout', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: 'commission_ids[]=' + ids.join('&commission_ids[]=') + '&payout_method=bank_transfer&csrf_token=<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Payouts processed successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to process payouts', 'error');
            }
        })
        .catch(() => showToast('Network error', 'error'));
    });
}
</script>
