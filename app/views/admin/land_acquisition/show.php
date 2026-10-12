<?php
/**
 * Farmer Land Acquisition Deal Detail & Compensation Milestones View
 */
$acquisition = $acquisition ?? [];
$milestones = $milestones ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb & Nav -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="/admin/farmer-acquisitions" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i>Back to Land Acquisition Desk
            </a>
            <h4 class="mb-0 fw-bold mt-1">
                Gata #<?= htmlspecialchars($acquisition['gata_number'] ?? 'N/A') ?> — <?= htmlspecialchars($acquisition['farmer_name'] ?? 'N/A') ?>
                <span class="badge bg-primary ms-2 font-monospace fs-6"><?= htmlspecialchars($acquisition['deal_token'] ?? '') ?></span>
            </h4>
        </div>
        <div>
            <!-- Status Update Form -->
            <form method="POST" action="/admin/farmer-acquisitions/<?= (int)$acquisition['id'] ?>/status" class="d-inline-flex gap-2 align-items-center">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <label for="deal_status_select" class="visually-hidden">Lifecycle Stage</label>
                <select name="status" id="deal_status_select" class="form-select form-select-sm">
                    <option value="bayana_agreement" <?= ($acquisition['status'] === 'bayana_agreement') ? 'selected' : '' ?>>Bayana Agreement</option>
                    <option value="mutation_in_progress" <?= ($acquisition['status'] === 'mutation_in_progress') ? 'selected' : '' ?>>Sec 143 Mutation</option>
                    <option value="possession_taken" <?= ($acquisition['status'] === 'possession_taken') ? 'selected' : '' ?>>Possession Taken</option>
                    <option value="registry_completed" <?= ($acquisition['status'] === 'registry_completed') ? 'selected' : '' ?>>Registry Completed</option>
                    <option value="disputed" <?= ($acquisition['status'] === 'disputed') ? 'selected' : '' ?>>Disputed</option>
                </select>
                <button type="submit" class="btn btn-outline-primary btn-sm">Update Stage</button>
            </form>
        </div>
    </div>

    <!-- Deal Specs Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Revenue Parcel &amp; Tehsil</div>
                    <div class="fw-bold fs-6">Mauza: <?= htmlspecialchars($acquisition['mauza_village'] ?? 'N/A') ?></div>
                    <div class="small text-muted">Tehsil: <?= htmlspecialchars($acquisition['tehsil'] ?? 'Sadar') ?>, <?= htmlspecialchars($acquisition['district'] ?? 'Gorakhpur') ?></div>
                    <div class="small text-muted"><?= htmlspecialchars($acquisition['fasli_year'] ?? '') ?></div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Acquired Land Area</div>
                    <div class="fw-bold fs-5 text-primary"><?= (float)($acquisition['area_bigha'] ?? 0) ?> Bigha</div>
                    <div class="small text-muted"><?= number_format((float)($acquisition['area_sqft'] ?? 0), 0) ?> sq.ft (Pucca Bigha)</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Total Deal Consideration</div>
                    <div class="fw-bold fs-5 text-dark">₹<?= number_format((float)($acquisition['total_deal_amount'] ?? 0), 2) ?></div>
                    <div class="small text-muted">Rate: ₹<?= number_format((float)($acquisition['rate_per_bigha'] ?? 0), 2) ?>/Bigha</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Disbursed vs Balance</div>
                    <div class="fw-bold fs-5 text-success">₹<?= number_format((float)($acquisition['paid_amount'] ?? 0), 2) ?></div>
                    <div class="small text-danger fw-semibold">Balance: ₹<?= number_format((float)(($acquisition['total_deal_amount'] ?? 0) - ($acquisition['paid_amount'] ?? 0)), 2) ?></div>
                </div>
            </div>

            <hr class="text-muted my-3">

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Landholder Contact &amp; KYC</div>
                    <div class="fw-semibold"><?= htmlspecialchars($acquisition['farmer_phone'] ?? '') ?></div>
                    <div class="small text-muted">Aadhaar: <?= htmlspecialchars($acquisition['farmer_aadhaar'] ?? 'Not provided') ?> &bull; PAN: <?= htmlspecialchars($acquisition['farmer_pan'] ?? 'N/A') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Farmer Bank Account (RTGS Disbursals)</div>
                    <div class="fw-semibold"><?= htmlspecialchars($acquisition['bank_name'] ?? 'N/A') ?> &bull; <?= htmlspecialchars($acquisition['bank_account_no'] ?? 'N/A') ?></div>
                    <div class="small text-muted font-monospace">IFSC: <?= htmlspecialchars($acquisition['bank_ifsc'] ?? 'N/A') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Key Dates</div>
                    <div class="small">Agreement: <strong><?= date('d M Y', strtotime($acquisition['agreement_date'] ?? 'now')) ?></strong></div>
                    <div class="small text-muted">Target Bainama: <strong><?= date('d M Y', strtotime($acquisition['target_registry_date'] ?? 'now')) ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compensation Milestones Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold mb-0"><i class="fas fa-layer-group text-primary me-2"></i>Staged Milestone Compensation Schedule</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3">Milestone Stage</th>
                            <th>Share %</th>
                            <th>Scheduled Amount</th>
                            <th>Status</th>
                            <th>Disbursement Date</th>
                            <th>Payment Ref / UTR</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($milestones as $m): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($m['milestone_name']) ?></div>
                                    <?php if (!empty($m['voucher_token'])): ?>
                                        <div class="small text-muted font-monospace"><?= htmlspecialchars($m['voucher_token']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-bold"><?= (float)$m['percentage'] ?>%</span>
                                </td>
                                <td class="fw-bold fs-6 text-dark">
                                    ₹<?= number_format((float)$m['amount'], 2) ?>
                                </td>
                                <td>
                                    <?php if ($m['status'] === 'disbursed'): ?>
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Disbursed</span>
                                    <?php elseif ($m['status'] === 'held'): ?>
                                        <span class="badge bg-danger"><i class="fas fa-hand-paper me-1"></i>Held</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($m['disbursed_at']) ? date('d M Y', strtotime($m['disbursed_at'])) : '<span class="text-muted small">—</span>' ?>
                                </td>
                                <td>
                                    <?php if (!empty($m['payment_ref'])): ?>
                                        <div class="fw-medium text-dark font-monospace small"><?= htmlspecialchars($m['payment_ref']) ?></div>
                                        <div class="small text-muted text-uppercase"><?= htmlspecialchars($m['payment_mode'] ?? 'RTGS') ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($m['status'] !== 'disbursed'): ?>
                                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#payoutModal<?= $m['id'] ?>">
                                                <i class="fas fa-paper-plane me-1"></i>Disburse Payout
                                            </button>
                                        <?php else: ?>
                                            <a href="/admin/farmer-acquisitions/milestones/<?= $m['id'] ?>/voucher" target="_blank" class="btn btn-outline-primary">
                                                <i class="fas fa-print me-1"></i>Print Voucher
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Disburse Payout Modal -->
                                    <div class="modal fade" id="payoutModal<?= $m['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <form method="POST" action="/admin/farmer-acquisitions/milestones/<?= $m['id'] ?>/disburse">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                                <input type="hidden" name="acquisition_id" value="<?= (int)$acquisition['id'] ?>">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold">Disburse <?= htmlspecialchars($m['milestone_name']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <div class="text-muted small">Payable Consideration Amount</div>
                                                            <div class="fs-4 fw-bold text-success">₹<?= number_format((float)$m['amount'], 2) ?></div>
                                                            <div class="small text-muted">Direct RTGS to <?= htmlspecialchars($acquisition['farmer_name']) ?> (A/C: <?= htmlspecialchars($acquisition['bank_account_no'] ?? 'N/A') ?>)</div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="pay_mode_<?= $m['id'] ?>" class="form-label">Payout Mode</label>
                                                            <select name="payment_mode" id="pay_mode_<?= $m['id'] ?>" class="form-select">
                                                                <option value="rtgs">RTGS (Real-Time Gross Settlement)</option>
                                                                <option value="neft">NEFT Electronic Transfer</option>
                                                                <option value="cheque">Bank Demand Draft / Cheque</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="pay_ref_<?= $m['id'] ?>" class="form-label">Bank UTR Number / Cheque Ref <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="pay_ref_<?= $m['id'] ?>" name="payment_ref" placeholder="e.g. UTR-PUNB20261012-765432" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="tds_<?= $m['id'] ?>" class="form-label">TDS Deducted (u/s 194LA) (₹)</label>
                                                            <input type="number" step="0.01" class="form-control" id="tds_<?= $m['id'] ?>" name="tds_deducted" value="0.00">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="date_<?= $m['id'] ?>" class="form-label">Disbursement Date</label>
                                                            <input type="date" class="form-control" id="date_<?= $m['id'] ?>" name="disbursed_at" value="<?= date('Y-m-d') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Confirm RTGS Disbursal</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
