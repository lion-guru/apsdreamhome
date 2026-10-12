<?php
/**
 * Admin Commission Advances & Loans Management Desk
 */
$page_title = $page_title ?? 'Commission Advances & Loans';
$advances = $advances ?? [];
$stats = $stats ?? [];
$current_status = $current_status ?? 'all';
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-hand-holding-usd text-primary me-2"></i>Associate Commission Advances & Loan Desk
            </h4>
            <small class="text-muted">Oversee advance requests, approve disbursement, and monitor automatic commission payout recovery.</small>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/finance/payouts" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-wallet me-1"></i>Associate Payouts
            </a>
            <a href="/admin/finance/cash-drawer" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-cash-register me-1"></i>Cash Drawer
            </a>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-muted small fw-semibold">Total Disbursed</div>
                <div class="fs-4 fw-bold text-primary">₹<?= number_format((float)($stats['total_disbursed'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-muted small fw-semibold">Total Recovered</div>
                <div class="fs-4 fw-bold text-success">₹<?= number_format((float)($stats['total_recovered'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-muted small fw-semibold">Outstanding Recovery</div>
                <div class="fs-4 fw-bold text-danger">₹<?= number_format((float)($stats['outstanding'] ?? 0), 2) ?></div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 bg-white">
                <div class="text-muted small fw-semibold">Pending Review</div>
                <div class="fs-4 fw-bold text-warning"><?= (int)($stats['pending_review'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="d-flex gap-2 mb-3">
        <?php foreach (['all' => 'All Advances', 'pending' => 'Pending Review', 'approved' => 'Approved', 'disbursed' => 'Disbursed (Active)', 'repaid' => 'Fully Repaid', 'rejected' => 'Rejected'] as $stKey => $stLabel): ?>
            <a href="/admin/commission-advances?status=<?= $stKey ?>" class="btn btn-sm <?= $current_status === $stKey ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <?= $stLabel ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Advances Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID / Date</th>
                        <th>Associate</th>
                        <th>Requested</th>
                        <th>Approved</th>
                        <th>Recovery Progress</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($advances)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No commission advances found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($advances as $a): ?>
                            <?php
                            $approved = (float)($a['approved_amount'] ?? 0);
                            $recovered = (float)($a['recovered_amount'] ?? 0);
                            $pct = $approved > 0 ? min(100, round(($recovered * 100) / $approved)) : 0;
                            $st = $a['status'] ?? 'pending';
                            $badge = match ($st) {
                                'pending' => 'warning text-dark',
                                'approved' => 'info text-white',
                                'disbursed' => 'primary',
                                'repaid' => 'success',
                                'rejected' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold">#<?= (int)$a['id'] ?></div>
                                    <small class="text-muted"><?= date('d M Y', strtotime($a['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($a['associate_name'] ?? '—') ?></div>
                                    <small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($a['associate_phone'] ?? '—') ?></small>
                                </td>
                                <td>
                                    <div class="fw-semibold">₹<?= number_format((float)$a['requested_amount'], 2) ?></div>
                                    <small class="text-muted"><?= (int)$a['tenure_months'] ?> Months · <?= htmlspecialchars($a['purpose'] ?? '') ?></small>
                                </td>
                                <td>
                                    <?php if ($approved > 0): ?>
                                        <div class="fw-bold text-dark">₹<?= number_format($approved, 2) ?></div>
                                        <small class="text-muted"><?= (float)$a['deduction_pct'] ?>% per payout</small>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td style="min-width: 150px;">
                                    <?php if ($st === 'disbursed' || $st === 'repaid'): ?>
                                        <div class="d-flex justify-content-between small mb-1">
                                            <span>₹<?= number_format($recovered) ?></span>
                                            <span class="fw-bold"><?= $pct ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-<?= $pct >= 100 ? 'success' : 'primary' ?>" style="width: <?= $pct ?>%"></div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $badge ?> text-uppercase"><?= $st ?></span>
                                </td>
                                <td class="text-end">
                                    <?php if ($st === 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveModal<?= $a['id'] ?>">
                                            Approve
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $a['id'] ?>">
                                            Reject
                                        </button>
                                    <?php elseif ($st === 'approved'): ?>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#disburseModal<?= $a['id'] ?>">
                                            <i class="fas fa-money-check me-1"></i>Disburse UTR
                                        </button>
                                    <?php else: ?>
                                        <small class="text-muted"><?= htmlspecialchars($a['disbursement_ref'] ?? 'Closed') ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal<?= $a['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="/admin/commission-advances/<?= $a['id'] ?>/approve">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold">Approve Advance #<?= $a['id'] ?> — <?= htmlspecialchars($a['associate_name']) ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Approved Amount (₹)</label>
                                                    <input type="number" name="approved_amount" class="form-control" value="<?= (float)$a['requested_amount'] ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Payout Recovery Rate (%)</label>
                                                    <input type="number" name="deduction_pct" class="form-control" value="25" min="5" max="100" required>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small fw-semibold">Admin Remarks</label>
                                                    <textarea name="admin_remarks" class="form-control" rows="2" placeholder="e.g. Approved against upcoming booking pipeline."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success btn-sm px-3">Confirm Approval</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Disburse Modal -->
                            <div class="modal fade" id="disburseModal<?= $a['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="/admin/commission-advances/<?= $a['id'] ?>/disburse">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold">Record Bank Disbursement #<?= $a['id'] ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-2">
                                                    <label class="form-label small fw-semibold">Bank UTR / Transaction Reference</label>
                                                    <input type="text" name="disbursement_ref" class="form-control" placeholder="e.g. UTR123456789 / CHEQUE-981" required>
                                                </div>
                                                <small class="text-muted">This activates auto-recovery from the associate's future commission disbursements.</small>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary btn-sm px-3">Confirm Disbursement</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?= $a['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="/admin/commission-advances/<?= $a['id'] ?>/reject">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold">Reject Advance #<?= $a['id'] ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label class="form-label small fw-semibold">Reason for Rejection</label>
                                                <textarea name="admin_remarks" class="form-control" rows="2" placeholder="e.g. Insufficient active sales pipeline." required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
