<?php
/**
 * Associate Payment History Page
 */
$page_title = $page_title ?? 'Payment History';
$current_page = 'payment-history';
$receipts = $receipts ?? [];
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i>Payment History</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($receipts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">No payments recorded</h5>
                <p class="text-muted">Payment receipts will appear here once transactions are made.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Receipt #</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Mode</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipts as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['receipt_number'] ?? 'N/A') ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($r['customer_name'] ?? 'N/A') ?>
                                    <?php if (!empty($r['customer_phone'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($r['customer_phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['property_title'] ?? 'N/A') ?>
                                    <?php if (!empty($r['city'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($r['city']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><strong class="text-success">₹<?= number_format($r['amount'] ?? 0) ?></strong></td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= ucfirst($r['payment_mode'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td><?= date('d M Y', strtotime($r['receipt_date'] ?? $r['created_at'] ?? '')) ?></td>
                                <td>
                                    <?php
                                    $statusClass = match(strtolower($r['status'] ?? '')) {
                                        'completed', 'verified' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($r['status'] ?? 'N/A') ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
