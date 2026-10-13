<?php
$page_title = 'Payment History - APS Dream Home';
$current_page = 'farmer-payments';
$extraHead = '<style>.badge-status { font-size:0.8rem; padding:0.35rem 0.75rem; }</style>';
$payments = $payments ?? [];
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-credit-card text-primary me-2"></i>Payment History</h4>
        <a href="<?php echo BASE_URL; ?>/farmer/dashboard" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($payments)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-coins fa-4x mb-3"></i>
                <p>No payment records found.</p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Bank Reference</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                        <?php
                        $tType = $p['transaction_type'] ?? '';
                        $pStatus = $p['status'] ?? 'pending';
                        $typeBadge = $tType === 'credit' ? 'success' : ($tType === 'debit' ? 'danger' : 'info');
                        $statusBadge = $pStatus === 'completed' ? 'success' : ($pStatus === 'pending' ? 'warning' : 'secondary');
                        ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($p['created_at'] ?? 'now')); ?></td>
                            <td><span class="badge bg-<?php echo e($typeBadge); ?>"><?php echo ucfirst($tType ?: 'N/A'); ?></span></td>
                            <td><strong>₹<?php echo number_format($p['amount'] ?? 0); ?></strong></td>
                            <td><?php echo htmlspecialchars($p['payment_method'] ?? 'N/A'); ?></td>
                            <td><small class="text-muted"><?php echo htmlspecialchars($p['bank_reference'] ?? 'N/A'); ?></small></td>
                            <td><span class="badge bg-<?php echo e($statusBadge); ?> badge-status"><?php echo ucfirst($pStatus); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
