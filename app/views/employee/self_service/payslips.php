<?php
/** Employee Self-Service - Payslips */

$page_title = $page_title ?? 'My Payslips';
$payslips = $payslips ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.payslip-row { transition: background 0.15s; }
.payslip-row:hover { background: #fefcf6; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>My Payslips</h4>
            <p class="text-muted mb-0 small">View and download your salary payslips</p>
        </div>
    </div>

    <?php if (empty($payslips)): ?>
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Payslips Yet</h5>
            <p class="text-muted small mb-3">Your payslips will appear here once payroll is processed</p>
        </div>
    </div>
    <?php else: ?>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px">Run #</th>
                            <th>Period</th>
                            <th style="width: 120px">Pay Date</th>
                            <th style="width: 150px">Gross / Net</th>
                            <th style="width: 150px">Deductions</th>
                            <th style="width: 150px">Status</th>
                            <th style="width: 120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payslips as $payslip): 
                            $statusClass = [
                                'draft' => 'secondary',
                                'calculated' => 'info',
                                'review' => 'warning',
                                'approved' => 'primary',
                                'processing' => 'info',
                                'completed' => 'success',
                                'paid' => 'success',
                            ][$payslip['status']] ?? 'secondary';
                        ?>
                        <tr class="payslip-row">
                            <td><code class="small"><?= htmlspecialchars($payslip['run_number']) ?></code></td>
                            <td>
                                <div><?= date('d M Y', strtotime($payslip['period_start'])) ?> - <?= date('d M Y', strtotime($payslip['period_end'])) ?></div>
                            </td>
                            <td><?= date('d M Y', strtotime($payslip['pay_date'])) ?></td>
                            <td>
                                <div class="small">Gross: <span class="fw-semibold">₹<?= number_format($payslip['gross_earnings'] ?? 0, 2) ?></span></div>
                                <div class="small text-muted">Net: <span class="fw-semibold text-success">₹<?= number_format($payslip['net_pay'] ?? 0, 2) ?></span></div>
                            </td>
                            <td>
                                <div class="small">Total: <span class="fw-semibold">₹<?= number_format($payslip['total_deductions'] ?? 0, 2) ?></span></div>
                                <div class="small text-muted">
                                    PF: ₹<?= number_format($payslip['pf_employee'] ?? 0, 2) ?> | 
                                    TDS: ₹<?= number_format($payslip['tds'] ?? 0, 2) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst($payslip['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($payslip['status'] === 'paid' || $payslip['status'] === 'completed'): ?>
                                    <a href="<?= BASE_URL ?>/employee/self-service/payslip/download/<?= $payslip['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download me-1"></i> Download PDF
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">Not available</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>