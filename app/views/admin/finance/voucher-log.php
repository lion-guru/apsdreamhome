<?php $page_title = $page_title ?? 'Voucher Log'; $page_heading = $page_heading ?? 'Payment Voucher Audit Log'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Voucher Audit Log</h2>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Voucher #</th><th>Type</th><th>Date</th><th>Amount</th><th>Party</th><th>Mode</th><th>Created</th></tr></thead>
                <tbody>
                <?php if (empty($vouchers)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No voucher log entries</td></tr>
                <?php else: foreach ($vouchers as $v): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($v['voucher_number'] ?? '-') ?></code></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($v['voucher_type'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($v['transaction_date'] ?? '-') ?></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($v['amount'] ?? 0), 2) ?></td>
                        <td><?= htmlspecialchars($v['party_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($v['payment_mode'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($v['created_at'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
