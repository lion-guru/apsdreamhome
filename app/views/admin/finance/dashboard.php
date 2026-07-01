<?php $page_title = $page_title ?? __('finance_money_workflow_dashboard'); $page_heading = $page_heading ?? __('finance_finance_section'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-wallet me-2 text-primary"></i><?php echo __('finance_money_workflow_dashboard'); ?></h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/bank-accounts" class="btn btn-outline-primary"><i class="fas fa-university me-1"></i><?php echo __('finance_bank_accounts'); ?></a>
            <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-outline-primary"><i class="fas fa-book me-1"></i><?php echo __('finance_cash_book'); ?></a>
            <a href="<?= BASE_URL ?>/admin/finance/expense-form" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?php echo __('finance_new_expense'); ?></a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?php echo __('finance_total_bank_balance'); ?></div>
                    <div class="aps-cp-stat-value text-success">₹<?= number_format((float)($stats['total_bank_balance'] ?? 0), 0) ?></div>
                    <div class="aps-cp-stat-meta"><?php echo __('finance_escrow'); ?>: ₹<?= number_format((float)($stats['escrow_balance'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?php echo __('finance_petty_cash'); ?></div>
                    <div class="aps-cp-stat-value text-info">₹<?= number_format((float)($stats['petty_cash'] ?? 0), 0) ?></div>
                    <div class="aps-cp-stat-meta"><?php echo __('finance_cash_on_hand'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?php echo __('finance_mtd_net_cash'); ?></div>
                    <?php $cn = (float)($stats['cash_net_mtd'] ?? 0); ?>
                    <div class="aps-cp-stat-value <?= $cn >= 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format($cn, 0) ?></div>
                    <div class="aps-cp-stat-meta">R: ₹<?= number_format((float)($stats['cash_receipts_mtd'] ?? 0), 0) ?> | P: ₹<?= number_format((float)($stats['cash_payments_mtd'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?php echo __('finance_pending_expenses'); ?></div>
                    <div class="aps-cp-stat-value text-warning"><?= (int)($stats['pending_expenses'] ?? 0) ?></div>
                    <div class="aps-cp-stat-meta"><?php echo __('finance_awaiting_approval'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?php echo __('finance_cheques_issued'); ?></div>
                    <div class="aps-cp-stat-value"><?= (int)($stats['cheques_issued'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?php echo __('finance_cheques_pending'); ?></div>
                    <div class="aps-cp-stat-value text-warning"><?= (int)($stats['cheques_pending'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?php echo __('finance_cheques_bounced'); ?></div>
                    <div class="aps-cp-stat-value text-danger"><?= (int)($stats['cheques_bounced'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?php echo __('finance_gst_net_payable'); ?></div>
                    <div class="aps-cp-stat-value text-primary">₹<?= number_format((float)($stats['gst_net_payable'] ?? 0), 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i><?= __('dash_cf_title') ?></h5>
                    <a href="<?= BASE_URL ?>/admin/finance/cash-flow?days=30" class="btn btn-sm btn-outline-primary"><?= __('dash_cf_view_full') ?></a>
                </div>
                <div class="aps-cp-card-body">
                    <?php $s = $forecast['summary'] ?? []; ?>
                    <div class="row text-center mb-3">
                        <div class="col">
                            <div class="fw-bold text-success fs-5">₹<?= number_format((float)($s['total_inflow'] ?? 0), 0) ?></div>
                            <small class="text-muted"><?= __('dash_cf_expected_inflow') ?></small>
                        </div>
                        <div class="col">
                            <div class="fw-bold text-danger fs-5">₹<?= number_format((float)($s['total_outflow'] ?? 0), 0) ?></div>
                            <small class="text-muted"><?= __('dash_cf_expected_outflow') ?></small>
                        </div>
                        <div class="col">
                            <?php $net = (float)($s['net_position'] ?? 0); ?>
                            <div class="fw-bold fs-5 <?= $net >= 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format($net, 0) ?></div>
                            <small class="text-muted"><?= __('dash_cf_net_position') ?></small>
                        </div>
                        <div class="col">
                            <div class="fw-bold text-primary fs-5"><?= (int)($s['entry_count'] ?? count($forecast['rows'] ?? [])) ?></div>
                            <small class="text-muted"><?= __('dash_cf_entries') ?></small>
                        </div>
                    </div>
                    <?php
                    $rows = $forecast['rows'] ?? [];
                    $cats = [];
                    foreach ($rows as $r) {
                        $cat = $r['category'] ?? 'other';
                        if (!isset($cats[$cat])) $cats[$cat] = ['inflow' => 0, 'outflow' => 0];
                        if (($r['direction'] ?? '') === 'inflow') $cats[$cat]['inflow'] += (float)($r['amount'] ?? 0);
                        else $cats[$cat]['outflow'] += (float)($r['amount'] ?? 0);
                    }
                    $labels = ['customer_payment'=>__('dash_cf_customer_payments'),'salary'=>__('dash_cf_salaries'),'vendor'=>__('dash_cf_vendors'),'commission'=>__('dash_cf_commissions'),'tax'=>__('dash_cf_taxes'),'development'=>__('dash_cf_development'),'land_acquisition'=>__('dash_cf_land'),'loan'=>__('dash_cf_loan'),'other'=>__('dash_cf_other'),'emi_collection'=>__('dash_cf_emi_collection')];
                    if (!empty($cats)):
                    ?>
                    <div class="table-responsive"><table class="table table-sm table-borderless mb-0">
                        <thead><tr><th><?= __('dash_cf_category') ?></th><th class="text-end text-success"><?= __('dash_cf_inflow') ?></th><th class="text-end text-danger"><?= __('dash_cf_outflow') ?></th></tr></thead>
                        <tbody>
                        <?php foreach ($cats as $cat => $vals): ?>
                            <tr>
                                <td><?= htmlspecialchars($labels[$cat] ?? ucfirst($cat)) ?></td>
                                <td class="text-end"><?= $vals['inflow'] > 0 ? '₹'.number_format($vals['inflow'], 0) : '—' ?></td>
                                <td class="text-end"><?= $vals['outflow'] > 0 ? '₹'.number_format($vals['outflow'], 0) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <?php else: ?>
                    <p class="text-center text-muted mb-0"><?= __('dash_cf_no_data') ?> <a href="<?= BASE_URL ?>/admin/finance/cash-flow?days=30"><?= __('dash_cf_generate') ?></a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="aps-cp-card h-100">
                <div class="aps-cp-card-header">
                    <h5 class="mb-0"><i class="fas fa-university me-2 text-info"></i><?= __('dash_cf_bank_balances') ?></h5>
                    <a href="<?= BASE_URL ?>/admin/finance/bank-accounts" class="btn btn-sm btn-outline-primary"><?= __('dash_cf_all') ?></a>
                </div>
                <div class="aps-cp-card-body">
                    <?php if (!empty($banks)): foreach (array_slice($banks, 0, 5) as $b): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($b['bank_name'] ?? '') ?></div>
                            <small class="text-muted"><?= htmlspecialchars($b['account_number'] ?? '') ?></small>
                        </div>
                        <div class="fw-bold text-success">₹<?= number_format((float)($b['current_balance'] ?? 0), 0) ?></div>
                    </div>
                    <hr class="my-1">
                    <?php endforeach; else: ?>
                    <p class="text-muted text-center"><?= __('dash_cf_no_accounts') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?php echo __('finance_recent_cash_transactions'); ?></h5>
            <a href="<?= BASE_URL ?>/admin/finance/cash-book" class="btn btn-sm btn-outline-primary"><?php echo __('finance_view_all'); ?></a>
        </div>
        <div class="aps-cp-card-body">
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th><?php echo __('finance_date'); ?></th><th><?php echo __('finance_type'); ?></th><th><?php echo __('finance_party'); ?></th><th><?php echo __('finance_mode'); ?></th><th class="text-end"><?php echo __('finance_amount'); ?></th><th><?php echo __('finance_voucher'); ?></th></tr>
                </thead>
                <tbody>
                <?php if (empty($recent_txns)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><?php echo __('finance_no_transactions_yet'); ?></td></tr>
                <?php else: foreach ($recent_txns as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['transaction_date'] ?? '') ?></td>
                        <td><span class="badge bg-<?= ($t['transaction_type'] ?? '') === 'receipt' ? 'success' : 'danger' ?>"><?= htmlspecialchars($t['transaction_type'] ?? '') ?></span></td>
                        <td><?= htmlspecialchars($t['party_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($t['payment_mode'] ?? '-') ?></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($t['amount'] ?? 0), 2) ?></td>
                        <td><code><?= htmlspecialchars($t['voucher_number'] ?? '-') ?></code></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
