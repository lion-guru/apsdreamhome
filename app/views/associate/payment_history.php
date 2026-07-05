<?php
$page_title = $page_title ?? __('assoc_ph_title', [], 'Payment History');
$current_page = 'payment-history';
$receipts = $receipts ?? [];
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-receipt me-2 text-primary"></i><?= __('assoc_ph_title', [], 'Payment History') ?></h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($receipts)): ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted"><?= __('assoc_ph_empty', [], 'No payments recorded') ?></h5>
                <p class="text-muted"><?= __('assoc_ph_empty_desc', [], 'Payment receipts will appear here once transactions are made.') ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('assoc_ph_th_receipt', [], 'Receipt #') ?></th>
                            <th><?= __('assoc_ph_th_customer', [], 'Customer') ?></th>
                            <th><?= __('assoc_ph_th_property', [], 'Property') ?></th>
                            <th><?= __('assoc_ph_th_amount', [], 'Amount') ?></th>
                            <th><?= __('assoc_ph_th_mode', [], 'Mode') ?></th>
                            <th><?= __('assoc_ph_th_date', [], 'Date') ?></th>
                            <th><?= __('assoc_ph_th_status', [], 'Status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipts as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['receipt_number'] ?? __('assoc_ph_na', [], 'N/A')) ?></strong></td>
                                <td>
                                    <?= htmlspecialchars($r['customer_name'] ?? __('assoc_ph_na', [], 'N/A')) ?>
                                    <?php if (!empty($r['customer_phone'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($r['customer_phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($r['property_title'] ?? __('assoc_ph_na', [], 'N/A')) ?>
                                    <?php if (!empty($r['city'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($r['city']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><strong class="text-success">₹<?= number_format($r['amount'] ?? 0) ?></strong></td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= ucfirst($r['payment_mode'] ?? __('assoc_ph_na', [], 'N/A')) ?>
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
                                    <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($r['status'] ?? __('assoc_ph_na', [], 'N/A')) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
