<?php
/**
 * Admin Plot Transfer & Resale NOC Desk — Detail & Action View
 */
$transfer = $transfer ?? [];
$eligibility = $eligibility ?? [];
$page_title = $page_title ?? 'Review Transfer Application #' . ($transfer['id'] ?? '');
$csrf = $_SESSION['csrf_token'] ?? '';
$isEligible = $eligibility['eligible'] ?? false;
$reasons = $eligibility['reasons'] ?? [];
$st = $transfer['status'] ?? 'requested';
?>

<div class="container-fluid py-3">
    <!-- Breadcrumb & Nav -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="/admin/plot-transfers" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i>Back to Transfers Desk
            </a>
            <h4 class="mb-0 fw-bold mt-1">
                Transfer Application #<?= (int)$transfer['id'] ?>
                <?php if (!empty($transfer['noc_number'])): ?>
                    <span class="badge bg-success ms-2 font-monospace fs-6"><?= htmlspecialchars($transfer['noc_number']) ?></span>
                <?php endif; ?>
            </h4>
        </div>
        <div class="d-flex gap-2">
            <?php if (!empty($transfer['noc_number'])): ?>
                <a href="/admin/plot-transfers/noc/<?= (int)$transfer['id'] ?>" target="_blank" class="btn btn-outline-success">
                    <i class="fas fa-print me-1"></i>Print Official NOC
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pre-Transfer Clearance Alert -->
    <?php if (!$isEligible && $st === 'requested'): ?>
        <div class="alert alert-danger d-flex align-items-start gap-2 shadow-sm mb-3">
            <i class="fas fa-exclamation-triangle fa-lg mt-1"></i>
            <div>
                <strong>Clearance Radar Alert:</strong> This plot has pending financial or legal encumbrances:
                <ul class="mb-0 mt-1 ps-3">
                    <?php foreach ($reasons as $r): ?>
                        <li><?= htmlspecialchars($r) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php elseif ($st === 'requested'): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 shadow-sm mb-3">
            <i class="fas fa-check-circle fa-lg"></i>
            <div><strong>Clearance Radar Passed:</strong> Plot has zero outstanding dues, maintenance clear, and clean legal title.</div>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- Left: Details -->
        <div class="col-lg-7">
            <!-- Plot & Colony Details -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-map-marked-alt text-primary me-2"></i>Plot & Township Information</span>
                    <span class="badge bg-primary">Plot #<?= htmlspecialchars($transfer['plot_number'] ?? '—') ?></span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Colony Name</small>
                            <span class="fw-semibold"><?= htmlspecialchars($transfer['colony_name'] ?? '—') ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Plot Area</small>
                            <span class="fw-semibold"><?= (float)($transfer['area_sqft'] ?? 0) ?> Sq.Ft</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Booking Reference</small>
                            <span class="fw-semibold font-monospace"><?= htmlspecialchars($transfer['booking_number'] ?? 'Direct Plot') ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Original Plot Value</small>
                            <span class="fw-semibold">₹<?= number_format((float)($transfer['total_price'] ?? 0), 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Allottee vs New Transferee -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-users text-primary me-2"></i>Parties to Ownership Transfer
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 border-end">
                            <h6 class="text-muted text-uppercase small fw-bold mb-2">Current Allottee (Transferor)</h6>
                            <div class="fw-bold fs-6"><?= htmlspecialchars($transfer['current_customer_name'] ?? '—') ?></div>
                            <div class="small text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($transfer['current_customer_phone'] ?? '—') ?></div>
                            <div class="small text-muted"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($transfer['current_customer_email'] ?? '—') ?></div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary text-uppercase small fw-bold mb-2">Proposed Transferee (New Buyer)</h6>
                            <div class="fw-bold fs-6 text-primary"><?= htmlspecialchars($transfer['new_buyer_name'] ?? '—') ?></div>
                            <div class="small"><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($transfer['new_buyer_phone'] ?? '—') ?></div>
                            <div class="small"><i class="fas fa-id-card me-1 text-muted"></i>Aadhaar: <?= htmlspecialchars($transfer['new_buyer_aadhaar'] ?? '—') ?></div>
                            <div class="small"><i class="fas fa-file-invoice me-1 text-muted"></i>PAN: <?= htmlspecialchars($transfer['new_buyer_pan'] ?? '—') ?></div>
                            <?php if (!empty($transfer['new_buyer_address'])): ?>
                                <div class="small mt-1"><i class="fas fa-home me-1 text-muted"></i><?= nl2br(htmlspecialchars($transfer['new_buyer_address'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commercial Terms -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-handshake text-primary me-2"></i>Commercial & Transfer Terms
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Transfer Type</small>
                            <span class="badge bg-secondary"><?= strtoupper(str_replace('_', ' ', (string)$transfer['transfer_type'])) ?></span>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Agreed Resale Price</small>
                            <span class="fw-semibold">₹<?= number_format((float)($transfer['transfer_amount'] ?? 0), 2) ?></span>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Transfer Admin Fee</small>
                            <span class="fw-semibold">₹<?= number_format((float)($transfer['transfer_fee'] ?? 0), 2) ?> (<?= htmlspecialchars($transfer['transfer_fee_status'] ?? 'pending') ?>)</span>
                        </div>
                        <div class="col-12 mt-2">
                            <small class="text-muted d-block">Applicant Remarks / Justification</small>
                            <div class="p-2 bg-light rounded small"><?= nl2br(htmlspecialchars($transfer['transfer_reason'] ?? 'None specified')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Action Center -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-tasks text-primary me-2"></i>Action & Decision Center
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small text-muted d-block">Current Lifecycle Stage</label>
                        <span class="badge bg-dark fs-6 px-3 py-2 mt-1">
                            <?= strtoupper(str_replace('_', ' ', (string)$st)) ?>
                        </span>
                    </div>

                    <!-- Step 1: Approve & Issue Transfer NOC -->
                    <?php if ($st === 'requested' || $st === 'under_review'): ?>
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-bold text-success mb-2">
                                <i class="fas fa-certificate me-1"></i>Step 1: Approve & Issue Transfer NOC
                            </h6>
                            <p class="small text-muted mb-2">
                                Approves application, generates official NOC registration code, and allows seller/buyer to execute formal transfer agreement.
                            </p>
                            <form method="POST" action="/admin/plot-transfers/<?= (int)$transfer['id'] ?>/approve">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Transfer Fee (₹)</label>
                                    <input type="number" step="100" name="transfer_fee" class="form-control form-control-sm" value="<?= (float)($transfer['transfer_fee'] ?: 5000) ?>">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Approval Remarks</label>
                                    <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="e.g. Verified clean ledger. NOC granted."></textarea>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                                    <i class="fas fa-check-circle me-1"></i>Approve & Issue Transfer NOC
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Step 2: Final Ownership Handover -->
                    <?php if ($st === 'approved'): ?>
                        <div class="border rounded p-3 mb-3 bg-light">
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="fas fa-key me-1"></i>Step 2: Execute Re-Allotment & Ownership Handover
                            </h6>
                            <p class="small text-muted mb-2">
                                Transfers plot record & booking in the database to <strong><?= htmlspecialchars($transfer['new_buyer_name']) ?></strong>.
                            </p>
                            <form method="POST" action="/admin/plot-transfers/<?= (int)$transfer['id'] ?>/execute" onsubmit="return confirm('Confirm ownership handover to new buyer? This will update the plot and booking allottee in the live database.');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold py-2">
                                    <i class="fas fa-exchange-alt me-1"></i>Execute Ownership Transfer
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <?php if ($st === 'completed'): ?>
                        <div class="alert alert-success text-center py-3 mb-3">
                            <i class="fas fa-check-double fa-2x mb-2 d-block text-success"></i>
                            <strong>Ownership Transferred!</strong>
                            <div class="small mt-1">Plot and bookings have been re-assigned to the new allottee.</div>
                        </div>
                    <?php endif; ?>

                    <!-- Reject Form -->
                    <?php if ($st !== 'completed' && $st !== 'rejected'): ?>
                        <div class="border-top pt-3">
                            <button class="btn btn-outline-danger btn-sm w-100" type="button" data-bs-toggle="collapse" data-bs-target="#rejectCollapse">
                                <i class="fas fa-times-circle me-1"></i>Reject Request
                            </button>
                            <div class="collapse mt-2" id="rejectCollapse">
                                <form method="POST" action="/admin/plot-transfers/<?= (int)$transfer['id'] ?>/reject">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                    <div class="mb-2">
                                        <textarea name="rejection_reason" class="form-control form-control-sm" rows="2" placeholder="Specify reason for rejection..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger btn-sm w-100">Confirm Rejection</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($st === 'rejected'): ?>
                        <div class="alert alert-danger mb-0 small">
                            <strong>Rejected:</strong> <?= nl2br(htmlspecialchars($transfer['rejection_reason'] ?? 'No reason given')) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
