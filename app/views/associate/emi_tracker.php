<?php
/**
 * Associate EMI Tracker Page
 */
$page_title = $page_title ?? 'EMI Tracker';
$current_page = 'emi-tracker';
$emiData = $emiData ?? [];
$stats = $stats ?? ['total_pending' => 0, 'overdue' => 0, 'collected' => 0, 'total_amount' => 0];
?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #6366f1 0%, #14b8a6 100%); color: #fff;">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold"><?= $stats['total_pending'] ?></div>
                <div class="small opacity-75">Pending EMIs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-danger"><?= $stats['overdue'] ?></div>
                <div class="small text-muted">Overdue</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-success">₹<?= number_format($stats['collected']) ?></div>
                <div class="small text-muted">Collected</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 text-center">
                <div class="fs-2 fw-bold text-warning">₹<?= number_format($stats['total_amount']) ?></div>
                <div class="small text-muted">Total Pending</div>
            </div>
        </div>
    </div>
</div>

<!-- EMI List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0"><i class="fas fa-calendar-check me-2 text-primary"></i>EMI Tracker</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($emiData)): ?>
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3 opacity-50"></i>
                <h5 class="text-muted">No pending EMIs!</h5>
                <p class="text-muted">All your customers' EMIs are up to date.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Installment #</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emiData as $emi): ?>
                            <?php
                            $isOverdue = strtotime($emi['due_date'] ?? '') < time() && ($emi['status'] ?? '') !== 'paid';
                            ?>
                            <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                <td>
                                    <strong><?= htmlspecialchars($emi['customer_name'] ?? 'N/A') ?></strong>
                                    <?php if (!empty($emi['customer_phone'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($emi['customer_phone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($emi['property_title'] ?? 'N/A') ?>
                                    <?php if (!empty($emi['city'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($emi['city']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>#<?= $emi['installment_number'] ?? $emi['id'] ?></td>
                                <td><strong>₹<?= number_format($emi['amount'] ?? 0) ?></strong></td>
                                <td>
                                    <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                        <?= date('d M Y', strtotime($emi['due_date'] ?? '')) ?>
                                        <?php if ($isOverdue): ?>
                                            <br><small>(<?= round((time() - strtotime($emi['due_date'])) / 86400) ?> days overdue)</small>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (($emi['status'] ?? '') === 'paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="badge bg-danger">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($emi['customer_phone'])): ?>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $emi['customer_phone']) ?>?text=<?= urlencode('Hi ' . ($emi['customer_name'] ?? '') . ', this is a reminder for your EMI payment of ₹' . number_format($emi['amount'] ?? 0) . ' due on ' . date('d M Y', strtotime($emi['due_date'] ?? '')) . '. Please pay at the earliest.') ?>" 
                                           class="btn btn-outline-success btn-sm" target="_blank" title="Send WhatsApp Reminder">
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
