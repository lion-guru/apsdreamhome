<?php
$page_title = $page_title ?? 'Payment History';
$current_page = 'payment-history';
$payments = $payments ?? [];
$stats = $stats ?? ['total_paid'=>0,'total_count'=>0,'this_month'=>0,'last_payment'=>null];
?>

<div class="aps-cp-hero" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h2><i class="fas fa-receipt me-2"></i>Payment History</h2>
            <p>View all your payment records and receipts.</p>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat aps-cp-stat--green">
            <div class="aps-cp-stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format($stats['total_paid']) ?></div>
                <div class="aps-cp-stat-label">Total Paid</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat aps-cp-stat--blue">
            <div class="aps-cp-stat-icon"><i class="fas fa-list"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value"><?= $stats['total_count'] ?></div>
                <div class="aps-cp-stat-label">Total Transactions</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat aps-cp-stat--orange">
            <div class="aps-cp-stat-icon"><i class="fas fa-calendar"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">₹<?= number_format($stats['this_month']) ?></div>
                <div class="aps-cp-stat-label">This Month</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="aps-cp-stat aps-cp-stat--purple">
            <div class="aps-cp-stat-icon"><i class="fas fa-clock"></i></div>
            <div class="aps-cp-stat-body">
                <div class="aps-cp-stat-value">
                    <?php if ($stats['last_payment']): ?>
                        <?= date('d M', strtotime($stats['last_payment']['payment_date'] ?? $stats['last_payment']['created_at'] ?? 'now')) ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </div>
                <div class="aps-cp-stat-label">Last Payment</div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Table -->
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5><i class="fas fa-list me-2 text-success"></i>All Payments</h5>
    </div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($payments)): ?>
            <div class="aps-cp-empty">
                <div class="aps-cp-empty-icon"><i class="fas fa-receipt"></i></div>
                <h5>No payments yet</h5>
                <p>Your payment history will appear here once you make a payment.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="aps-cp-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Booking</th>
                            <th>Plot / Colony</th>
                            <th>Installment</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p):
                            $payDate = $p['payment_date'] ?? $p['paid_date'] ?? $p['created_at'] ?? '';
                            $amount = (float)($p['amount'] ?? $p['paid_amount'] ?? 0);
                        ?>
                        <tr>
                            <td>
                                <strong><?= $payDate ? date('d M Y', strtotime($payDate)) : '—' ?></strong>
                                <?php if ($payDate): ?>
                                    <br><small class="text-muted"><?= date('h:i A', strtotime($payDate)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted"><?= htmlspecialchars($p['booking_number'] ?? '') ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['plot_number'] ?? '—') ?>
                                <br><small class="text-muted"><?= htmlspecialchars($p['colony_name'] ?? '') ?></small>
                            </td>
                            <td>
                                <?php if (!empty($p['installment_number'])): ?>
                                    #<?= htmlspecialchars($p['installment_number']) ?>
                                <?php elseif (!empty($p['due_date'])): ?>
                                    <small class="text-muted">Due: <?= date('d M', strtotime($p['due_date'])) ?></small>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-success">₹<?= number_format($amount) ?></td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    <?= ucfirst(htmlspecialchars($p['payment_method'] ?? $p['mode'] ?? '—')) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($p['transaction_id'])): ?>
                                    <small class="text-muted" style="font-family:monospace;"><?= htmlspecialchars($p['transaction_id']) ?></small>
                                <?php elseif (!empty($p['reference_number'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($p['reference_number']) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
