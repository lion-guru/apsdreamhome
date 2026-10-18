<?php
/**
 * Advance / Loan Details
 */

$page_title = $page_title ?? 'Advance Details';
$advance = $advance ?? [];
$repayments = $repayments ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-hand-holding-usd me-2 text-primary"></i>Advance Details</h4>
                    <p class="text-muted mb-0 small">Reference: <strong><?= htmlspecialchars($advance['reference_number'] ?? 'N/A') ?></strong></p>
                </div>
                <a href="<?= BASE_URL ?>/employee/self-service/advances" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Advances
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Advance Summary Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Advance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <div class="fw-bold fs-4 text-primary">₹<?= number_format($advance['amount'] ?? 0, 2) ?></div>
                                <div class="small text-muted">Principal Amount</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <div class="fw-bold fs-4 text-success">₹<?= number_format($advance['remaining_balance'] ?? 0, 2) ?></div>
                                <div class="small text-muted">Outstanding Balance</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <div class="fw-bold fs-4 text-warning">₹<?= number_format($advance['total_interest'] ?? 0, 2) ?></div>
                                <div class="small text-muted">Total Interest</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <div class="fw-bold fs-4 <?= ($advance['status'] ?? '') === 'repaid' ? 'text-success' : 'text-info' ?>">
                                    <?= ucfirst($advance['status'] ?? 'Unknown') ?>
                                </div>
                                <div class="small text-muted">Status</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Type:</strong> <?= ucfirst($advance['type'] ?? 'advance') ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Installments:</strong> <?= $advance['installments'] ?? 0 ?> months
                        </div>
                        <div class="col-md-4">
                            <strong>Applied On:</strong> <?= date('d M Y', strtotime($advance['created_at'] ?? 'now')) ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-4">
                            <strong>Approved On:</strong> <?= $advance['approved_at'] ? date('d M Y', strtotime($advance['approved_at'])) : 'Pending' ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Disbursed On:</strong> <?= $advance['disbursed_at'] ? date('d M Y', strtotime($advance['disbursed_at'])) : 'Not Disbursed' ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Interest Rate:</strong> <?= ($advance['interest_rate'] ?? 0) > 0 ? $advance['interest_rate'] . '% p.a.' : 'Interest-free' ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong>Reason:</strong> <?= htmlspecialchars($advance['reason'] ?? 'Not specified') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Repayment Schedule -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Repayment Schedule</h5>
                    <span class="badge bg-primary"><?= count($repayments) ?> Installments</span>
                </div>
                <div class="card-body">
                    <?php if (empty($repayments)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                            <p>No repayment schedule generated yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Due Date</th>
                                        <th>Principal</th>
                                        <th>Interest</th>
                                        <th>Total Due</th>
                                        <th>Paid</th>
                                        <th>Paid Amount</th>
                                        <th>Paid Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($repayments as $index => $r): ?>
                                    <tr class="<?= $r['status'] === 'overdue' ? 'table-danger' : ($r['status'] === 'paid' ? 'table-success' : '') ?>">
                                        <td><?= $index + 1 ?></td>
                                        <td><?= date('d M Y', strtotime($r['due_date'])) ?></td>
                                        <td>₹<?= number_format($r['principal_amount'] ?? 0, 2) ?></td>
                                        <td>₹<?= number_format($r['interest_amount'] ?? 0, 2) ?></td>
                                        <td class="fw-bold">₹<?= number_format(($r['principal_amount'] ?? 0) + ($r['interest_amount'] ?? 0), 2) ?></td>
                                        <td>
                                            <span class="badge <?= $r['status'] === 'paid' ? 'bg-success' : ($r['status'] === 'overdue' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                <?= ucfirst($r['status'] ?? 'pending') ?>
                                            </span>
                                        </td>
                                        <td>₹<?= number_format($r['paid_amount'] ?? 0, 2) ?></td>
                                        <td><?= $r['paid_date'] ? date('d M Y', strtotime($r['paid_date'])) : '-' ?></td>
                                        <td>
                                            <?php if ($r['status'] === 'overdue'): ?>
                                                <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i> Overdue</span>
                                            <?php elseif ($r['status'] === 'paid'): ?>
                                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
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

            <!-- Repayment History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Repayment History</h5>
                </div>
                <div class="card-body">
                    <?php 
                    $paidRepayments = array_filter($repayments, fn($r) => ($r['paid_amount'] ?? 0) > 0);
                    if (empty($paidRepayments)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-receipt fa-2x mb-2"></i>
                            <p>No repayments made yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Installment</th>
                                        <th>Paid Date</th>
                                        <th>Paid Amount</th>
                                        <th>Payment Mode</th>
                                        <th>Transaction Ref</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paidRepayments as $r): ?>
                                    <tr>
                                        <td>#<?= $r['installment_number'] ?? 'N/A' ?></td>
                                        <td><?= $r['paid_date'] ? date('d M Y H:i', strtotime($r['paid_date'])) : 'N/A' ?></td>
                                        <td class="fw-bold text-success">₹<?= number_format($r['paid_amount'] ?? 0, 2) ?></td>
                                        <td><?= ucfirst($r['payment_mode'] ?? 'salary') ?></td>
                                        <td><?= htmlspecialchars($r['transaction_ref'] ?? 'Auto-deducted') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>