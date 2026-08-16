<?php
$page_title = $page_title ?? __('assoc_emi_title', [], 'EMI Tracker');
$current_page = 'emi-tracker';
$emiData = $emiData ?? [];
$stats = $stats ?? ['total_pending' => 0, 'overdue' => 0, 'collected' => 0, 'total_amount' => 0];
?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm" class="style-19672">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold"><?= $stats['total_pending'] ?></div>
                <div class="small opacity-75"><?= __('assoc_emi_pending', [], 'Pending EMIs') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-danger"><?= $stats['overdue'] ?></div>
                <div class="small text-muted"><?= __('assoc_emi_overdue', [], 'Overdue') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-success">₹<?= number_format($stats['collected']) ?></div>
                <div class="small text-muted"><?= __('assoc_emi_collected', [], 'Collected') ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-warning">₹<?= number_format($stats['total_amount']) ?></div>
                <div class="small text-muted"><?= __('assoc_emi_total_pending', [], 'Total Pending') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i><?= __('assoc_emi_title', [], 'EMI Tracker') ?></h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($emiData)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-50"></i>
                <h5 class="text-muted"><?= __('assoc_emi_empty', [], 'No pending EMIs!') ?></h5>
                <p class="text-muted"><?= __('assoc_emi_empty_desc', [], 'All your customers\' EMIs are up to date.') ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('assoc_emi_th_customer', [], 'Customer') ?></th>
                            <th><?= __('assoc_emi_th_property', [], 'Property') ?></th>
                            <th><?= __('assoc_emi_th_installment', [], 'Installment #') ?></th>
                            <th><?= __('assoc_emi_th_amount', [], 'Amount') ?></th>
                            <th><?= __('assoc_emi_th_due', [], 'Due Date') ?></th>
                            <th><?= __('assoc_emi_th_status', [], 'Status') ?></th>
                            <th><?= __('assoc_emi_th_action', [], 'Action') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emiData as $emi): ?>
                            <?php
                            $isOverdue = strtotime($emi['due_date'] ?? '') < time() && ($emi['status'] ?? '') !== 'paid';
                            ?>
                            <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($emi['customer_name'] ?? __('assoc_emi_na', [], 'N/A')) ?></strong>
                                    <?php if (!empty($emi['customer_phone'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($emi['customer_phone'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($emi['property_title'] ?? __('assoc_emi_na', [], 'N/A')) ?>
                                    <?php if (!empty($emi['city'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($emi['city'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>#<?= $emi['installment_number'] ?? $emi['id'] ?></td>
                                <td><strong>₹<?= number_format($emi['amount'] ?? 0) ?></strong></td>
                                <td>
                                    <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                        <?= date('d M Y', strtotime($emi['due_date'] ?? '')) ?>
                                        <?php if ($isOverdue): ?>
                                            <br><small>(<?= round((time() - strtotime($emi['due_date'])) / 86400) ?> <?= __('assoc_emi_days_overdue', [], 'days overdue') ?>)</small>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($emi['status'] ?? '') === 'paid'): ?>
                                        <span class="badge bg-success"><?= __('assoc_emi_paid', [], 'Paid') ?></span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="badge bg-danger"><?= __('assoc_emi_overdue', [], 'Overdue') ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><?= __('assoc_emi_pending_status', [], 'Pending') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($emi['customer_phone'])): ?>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $emi['customer_phone']) ?>?text=<?= urlencode(__('assoc_emi_reminder_text', ['name' => ($emi['customer_name'] ?? ''), 'amount' => number_format($emi['amount'] ?? 0), 'date' => date('d M Y', strtotime($emi['due_date'] ?? ''))], 'Hi %name%, this is a reminder for your EMI payment of ₹%amount% due on %date%. Please pay at the earliest.')) ?>" 
                                           class="btn btn-outline-success btn-sm" target="_blank" title="<?= __('assoc_emi_whatsapp_reminder', [], 'Send WhatsApp Reminder') ?>">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
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
