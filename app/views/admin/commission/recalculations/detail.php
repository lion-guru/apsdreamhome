<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="/admin/commission/recalculations" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
            <h2 style="color:#e0e0e0;"><i class="fas fa-calculator me-2" style="color:#ffc107;"></i> Recalculation #<?= $item['id'] ?></h2>
        </div>
    </div>

    <!-- Status Banner -->
    <?php
    $statusBanner = match($item['status'] ?? 'pending') {
        'pending' => ['color' => '#ffc107', 'bg' => 'rgba(255,193,7,0.1)', 'icon' => 'fa-clock', 'label' => 'Pending Approval'],
        'applied' => ['color' => '#28a745', 'bg' => 'rgba(40,167,69,0.1)', 'icon' => 'fa-check-circle', 'label' => 'Applied'],
        'rejected' => ['color' => '#dc3545', 'bg' => 'rgba(220,53,69,0.1)', 'icon' => 'fa-times-circle', 'label' => 'Rejected'],
        default => ['color' => '#6c757d', 'bg' => 'rgba(108,117,125,0.1)', 'icon' => 'fa-info-circle', 'label' => ucfirst($item['status'] ?? 'Unknown')],
    };
    ?>
    <div class="alert mb-4" style="background:<?= $statusBanner['bg'] ?>;border:1px solid <?= $statusBanner['color'] ?>;color:<?= $statusBanner['color'] ?>;">
        <i class="fas <?= $statusBanner['icon'] ?> me-2"></i>
        <strong>Status: <?= $statusBanner['label'] ?></strong>
        — <?= date('d M Y H:i', strtotime($item['created_at'])) ?>
    </div>

    <div class="row">
        <!-- Left: Amount Comparison -->
        <div class="col-md-6">
            <div class="card mb-4" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                <div class="card-header" style="background:rgba(255,255,255,0.05);border-bottom:1px solid #444;">
                    <h5 style="color:#ffc107;margin:0;"><i class="fas fa-balance-scale me-2"></i> Amount Comparison</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-5">
                            <h6 style="color:#888;">Original Amount</h6>
                            <h3 style="color:#dc3545;">₹<?= number_format((float)$item['original_amount']) ?></h3>
                        </div>
                        <div class="col-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-arrow-right" style="color:#ffc107;font-size:1.5rem;"></i>
                        </div>
                        <div class="col-5">
                            <h6 style="color:#888;">New Amount</h6>
                            <h3 style="color:#28a745;">₹<?= number_format((float)$item['new_amount']) ?></h3>
                        </div>
                    </div>
                    <div class="text-center">
                        <?php $diff = (float)$item['amount_diff']; ?>
                        <span class="badge" style="background:<?= $diff >= 0 ? 'rgba(40,167,69,0.2)' : 'rgba(220,53,69,0.2)' ?>;color:<?= $diff >= 0 ? '#28a745' : '#dc3545' ?>;font-size:1rem;padding:8px 16px;">
                            <?= $diff >= 0 ? '+' : '' ?>₹<?= number_format($diff) ?> (<?= $diff >= 0 ? 'Increase' : 'Decrease' ?>)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Original Entry Details -->
            <div class="card mb-4" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                <div class="card-header" style="background:rgba(255,255,255,0.05);border-bottom:1px solid #444;">
                    <h5 style="color:#ffc107;margin:0;"><i class="fas fa-file-invoice me-2"></i> Original Ledger Entry #<?= $item['original_ledger_id'] ?></h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-dark mb-0">
                        <tr><td style="color:#888;width:40%;">Commission Type</td><td><span class="badge bg-info"><?= htmlspecialchars($item['orig_type'] ?? 'N/A') ?></span></td></tr>
                        <tr><td style="color:#888;">Beneficiary</td><td><?= htmlspecialchars($item['beneficiary_name'] ?? 'User #' . $item['beneficiary_user_id']) ?></td></tr>
                        <tr><td style="color:#888;">Source</td><td><?= htmlspecialchars($item['source_name'] ?? 'User #' . $item['source_user_id']) ?></td></tr>
                        <tr><td style="color:#888;">Sale Amount</td><td>₹<?= number_format((float)($item['sale_amount'] ?? 0)) ?></td></tr>
                        <tr><td style="color:#888;">Original Rate</td><td><?= number_format((float)($item['orig_rate'] ?? 0), 1) ?>%</td></tr>
                        <tr><td style="color:#888;">Booking ID</td><td><?= $item['booking_id'] ?? 'N/A' ?></td></tr>
                        <tr><td style="color:#888;">Plan Version</td><td>v<?= $item['orig_plan_version'] ?? 'N/A' ?></td></tr>
                        <tr><td style="color:#888;">Engine</td><td><?= $item['calculation_engine'] ?? 'hybrid' ?></td></tr>
                        <tr><td style="color:#888;">Created At</td><td><?= $item['created_at'] ?? 'N/A' ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Reason + Actions -->
        <div class="col-md-6">
            <!-- Reason -->
            <div class="card mb-4" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                <div class="card-header" style="background:rgba(255,255,255,0.05);border-bottom:1px solid #444;">
                    <h5 style="color:#ffc107;margin:0;"><i class="fas fa-comment me-2"></i> Reason</h5>
                </div>
                <div class="card-body">
                    <p style="color:#ccc;"><?= nl2br(htmlspecialchars($item['reason'] ?? '')) ?></p>
                    <small style="color:#888;">Requested by: <?= htmlspecialchars($item['requested_by_name'] ?? 'Admin #' . $item['requested_by']) ?></small>
                </div>
            </div>

            <!-- Plan Snapshot (if available) -->
            <?php if (!empty($item['plan_snapshot'])): ?>
                <div class="card mb-4" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                    <div class="card-header" style="background:rgba(255,255,255,0.05);border-bottom:1px solid #444;">
                        <h5 style="color:#ffc107;margin:0;"><i class="fas fa-camera me-2"></i> Original Plan Snapshot</h5>
                    </div>
                    <div class="card-body">
                        <small style="color:#888;">This entry was calculated under plan version v<?= $item['orig_plan_version'] ?? 'N/A' ?></small>
                        <pre style="background:#1a1a1a;padding:10px;border-radius:5px;color:#aaa;font-size:0.75rem;max-height:200px;overflow:auto;"><?= htmlspecialchars(json_encode(json_decode($item['plan_snapshot'], true) ?? [], JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Admin Notes -->
            <?php if (!empty($item['admin_notes'])): ?>
                <div class="card mb-4" style="background:rgba(30,30,30,0.9);border:1px solid #444;">
                    <div class="card-header" style="background:rgba(255,255,255,0.05);border-bottom:1px solid #444;">
                        <h5 style="color:#ffc107;margin:0;"><i class="fas fa-sticky-note me-2"></i> Admin Notes</h5>
                    </div>
                    <div class="card-body">
                        <p style="color:#ccc;"><?= nl2br(htmlspecialchars($item['admin_notes'] ?? '')) ?></p>
                        <small style="color:#888;">
                            By: <?= htmlspecialchars($item['approved_by_name'] ?? 'N/A') ?>
                            <?php if (!empty($item['updated_at'])): ?>
                                on <?= date('d M Y H:i', strtotime($item['updated_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons (only for pending) -->
            <?php if (($item['status'] ?? '') === 'pending'): ?>
                <div class="card mb-4" style="background:rgba(255,193,7,0.05);border:1px solid rgba(255,193,7,0.3);">
                    <div class="card-header" style="background:rgba(255,193,7,0.1);border-bottom:1px solid rgba(255,193,7,0.3);">
                        <h5 style="color:#ffc107;margin:0;"><i class="fas fa-gavel me-2"></i> Admin Action</h5>
                    </div>
                    <div class="card-body">
                        <p style="color:#888;font-size:0.85rem;">
                            <i class="fas fa-exclamation-triangle me-1" style="color:#ffc107;"></i>
                            Approving will create a NEW ledger entry and mark the original as superseded.
                            Past entries are NEVER modified.
                        </p>

                        <!-- Approve -->
                        <form method="POST" action="/admin/commission/recalculations/approve" class="mb-3">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="recalc_id" value="<?= $item['id'] ?>">
                            <div class="mb-2">
                                <label style="color:#ccc;font-size:0.85rem;">Admin Notes (optional)</label>
                                <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Reason for approval..." style="background:#1a1a1a;border:#444;color:#ccc;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success" onclick="return confirm('Approve this recalculation? A new ledger entry will be created.')">
                                <i class="fas fa-check me-1"></i> Approve & Apply
                            </button>
                        </form>

                        <!-- Reject -->
                        <form method="POST" action="/admin/commission/recalculations/reject">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="recalc_id" value="<?= $item['id'] ?>">
                            <div class="mb-2">
                                <label style="color:#ccc;font-size:0.85rem;">Rejection Reason</label>
                                <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Why rejecting?" style="background:#1a1a1a;border:#444;color:#ccc;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this recalculation request?')">
                                <i class="fas fa-times me-1"></i> Reject
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- New Ledger Entry (if applied) -->
            <?php if (($item['status'] ?? '') === 'applied' && !empty($item['new_ledger_id'])): ?>
                <div class="card mb-4" style="background:rgba(40,167,69,0.05);border:1px solid rgba(40,167,69,0.3);">
                    <div class="card-header" style="background:rgba(40,167,69,0.1);border-bottom:1px solid rgba(40,167,69,0.3);">
                        <h5 style="color:#28a745;margin:0;"><i class="fas fa-check-circle me-2"></i> Applied</h5>
                    </div>
                    <div class="card-body">
                        <p style="color:#ccc;">New ledger entry created: <strong>#<?= $item['new_ledger_id'] ?></strong></p>
                        <p style="color:#888;font-size:0.85rem;">Original entry #<?= $item['original_ledger_id'] ?> has been marked as superseded.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
