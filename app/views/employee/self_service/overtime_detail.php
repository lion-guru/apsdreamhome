<?php
/**
 * Overtime Request Details
 */

$page_title = $page_title ?? 'Overtime Request Details';
$request = $request ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-clock me-2 text-info"></i>Overtime Request Details</h4>
                    <p class="text-muted mb-0 small">Reference: <strong><?= htmlspecialchars($request['reference_number'] ?? 'N/A') ?></strong></p>
                </div>
                <a href="<?= BASE_URL ?>/employee/self-service/overtime" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Overtime
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Request Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Request Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="fw-bold fs-3 text-info"><?= $request['hours'] ?? 0 ?></div>
                                <div class="small text-muted">Hours</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="fw-bold fs-3 text-success">₹<?= number_format($request['amount'] ?? 0, 2) ?></div>
                                <div class="small text-muted">Amount</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3">
                                <div class="fw-bold fs-3 text-warning"><?= $request['rate_multiplier'] ?? '1.5x' ?></div>
                                <div class="small text-muted">Rate</div>
                            </div>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Date:</strong> <?= date('d M Y', strtotime($request['overtime_date'] ?? $request['date'] ?? 'now')) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Time:</strong> <?= date('H:i', strtotime($request['start_time'] ?? '00:00')) ?> - <?= date('H:i', strtotime($request['end_time'] ?? '00:00')) ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <strong>Status:</strong> 
                            <span class="badge <?= $request['status'] === 'approved' ? 'bg-success' : ($request['status'] === 'rejected' ? 'bg-danger' : ($request['status'] === 'paid' ? 'bg-info' : 'bg-warning text-dark')) ?> ms-2">
                                <?= ucfirst($request['status'] ?? 'pending') ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Submitted:</strong> <?= date('d M Y H:i', strtotime($request['created_at'] ?? 'now')) ?>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong>Reason:</strong>
                            <p class="mt-1"><?= htmlspecialchars($request['reason'] ?? 'Not specified') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Details -->
            <?php if (($request['status'] ?? '') !== 'pending'): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-check me-2"></i>Approval Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Approved By:</strong> <?= htmlspecialchars($request['approved_by_name'] ?? 'System') ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Approved On:</strong> <?= $request['approved_at'] ? date('d M Y H:i', strtotime($request['approved_at'])) : '-' ?>
                        </div>
                    </div>
                    <?php if (($request['status'] ?? '') === 'rejected'): ?>
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong class="text-danger">Rejection Reason:</strong>
                            <p class="mt-1 text-danger"><?= htmlspecialchars($request['rejection_reason'] ?? 'Not specified') ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Payment Details -->
            <?php if (($request['status'] ?? '') === 'paid' || ($request['status'] ?? '') === 'approved'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Paid Amount:</strong> ₹<?= number_format($request['paid_amount'] ?? $request['amount'] ?? 0, 2) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Payment Date:</strong> <?= $request['paid_at'] ? date('d M Y', strtotime($request['paid_at'])) : 'Pending' ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Payment Mode:</strong> <?= ucfirst($request['payment_mode'] ?? 'salary') ?>
                        </div>
                    </div>
                    <?php if (!empty($request['payslip_reference'])): ?>
                    <div class="row mt-2">
                        <div class="col-12">
                            <strong>Payslip Ref:</strong> <?= htmlspecialchars($request['payslip_reference']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>