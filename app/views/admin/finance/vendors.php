<?php $page_title = $page_title ?? __('finance_vendor_payments'); $page_heading = $page_heading ?? __('finance_vendor_payments_outstanding'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-truck me-2 text-primary"></i><?php echo __('finance_vendor_payments'); ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/vendor-payment" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?php echo __('finance_new_payment'); ?></a>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-exclamation-circle me-2 text-warning"></i><?php echo __('finance_vendor_outstanding'); ?></h5></div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th><?php echo __('finance_vendor'); ?></th><th><?php echo __('finance_type'); ?></th><th class="text-end"><?php echo __('finance_payable'); ?></th><th class="text-end"><?php echo __('finance_tds'); ?></th><th class="text-end"><?php echo __('finance_gst'); ?></th><th><?php echo __('finance_bills'); ?></th></tr></thead>
                <tbody>
                <?php if (empty($outstanding)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><?php echo __('finance_no_outstanding_vendor_dues'); ?></td></tr>
                <?php else: foreach ($outstanding as $o): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($o['vendor_name'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($o['vendor_type'] ?? '-') ?></span></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($o['total_payable'] ?? 0), 2) ?></td>
                        <td class="text-end">₹<?= number_format((float)($o['total_tds'] ?? 0), 2) ?></td>
                        <td class="text-end">₹<?= number_format((float)($o['total_gst'] ?? 0), 2) ?></td>
                        <td><?= (int)($o['bills'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-list me-2"></i><?php echo __('finance_recent_payments'); ?></h5></div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?php echo __('finance_date'); ?></th>
                        <th><?php echo __('finance_vendor'); ?></th>
                        <th><?php echo __('finance_type'); ?></th>
                        <th><?php echo __('finance_bill_hash'); ?></th>
                        <th><?= __('vendors_currency_abbr') ?></th>
                        <th class="text-end"><?php echo __('finance_amount'); ?></th>
                        <th class="text-end"><?php echo __('finance_tds'); ?></th>
                        <th class="text-end"><?php echo __('finance_gst'); ?></th>
                        <th class="text-end"><?= __('vendors_amount_inr') ?></th>
                        <th><?php echo __('finance_status'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4"><?php echo __('finance_no_vendor_payments_recorded'); ?></td></tr>
                <?php else: foreach ($payments as $p):
                    $cur = strtoupper($p['currency'] ?? 'INR');
                    $fx  = (float)($p['exchange_rate'] ?? 1.0);
                    $sym = ['INR'=>'₹','USD'=>'$','EUR'=>'€','GBP'=>'£','AED'=>'د.إ'][$cur] ?? '₹';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($p['payment_date'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['vendor_name'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($p['vendor_type'] ?? '-') ?></span></td>
                        <td><code><?= htmlspecialchars($p['bill_number'] ?? '-') ?></code></td>
                        <td><span class="badge bg-info"><?= $cur ?></span></td>
                        <td class="text-end fw-bold"><?= $sym ?><?= number_format((float)($p['gross_amount'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= $sym ?><?= number_format((float)($p['tds_amount'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= $sym ?><?= number_format((float)($p['gst_amount'] ?? 0), 2) ?></td>
                        <td class="text-end">
                            <?php if (!empty($p['amount_inr'])): ?>
                                ₹<?= number_format((float)$p['amount_inr'], 2) ?>
                            <?php elseif ($cur !== 'INR'): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                ₹<?= number_format((float)($p['gross_amount'] ?? 0), 2) ?>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-<?= ($p['status'] ?? '') === 'paid' ? 'success' : 'warning' ?>"><?= htmlspecialchars($p['status'] ?? 'pending') ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
