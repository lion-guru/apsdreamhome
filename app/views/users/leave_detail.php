<?php
$leave = $leave ?? [];
function ldStatusBadge($status) {
    $map = ['approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', 'cancelled' => 'secondary'];
    $cls = $map[strtolower($status)] ?? 'secondary';
    return '<span class="badge bg-' . $cls . ' fs-6">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
function ldDate($d) { return $d ? date('d M Y', strtotime($d)) : 'â€”'; }
function ldDateTime($d) { return $d ? date('d M Y, h:i A', strtotime($d)) : 'â€”'; }
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.ld-card { border: none; border-radius: 12px; }
.ld-header { border-radius: 12px 12px 0 0; }
.ld-info-row { padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
.ld-info-row:last-child { border-bottom: none; }
.ld-label { color: #64748b; font-size: 0.85rem; font-weight: 500; }
.ld-value { color: #1e293b; font-weight: 600; }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="/employee/leaves" class="text-decoration-none">Leave Management</a></li>
                    <li class="breadcrumb-item active">Leave #<?= (int)($leave['id'] ?? 0) ?></li>
                </ol>
            </nav>
            <h4 class="mb-0 fw-bold">Leave Application Details</h4>
        </div>
        <div>
            <?php if (strtolower($leave['status'] ?? '') === 'pending'): ?>
                <form method="POST" action="/employee/leaves/<?= (int)$leave['id'] ?>/cancel" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this leave application?')">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-times me-1"></i>Cancel Leave</button>
                </form>
            <?php endif; ?>
            <a href="/employee/leaves" class="btn btn-outline-secondary ms-2"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card ld-card shadow-sm">
                <div class="card-body ld-header p-4" class="style-54835">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="mb-1 fw-bold">
                                <span class="lv-badge" class="style-75733"></span>
                                <?= htmlspecialchars($leave['type_name'] ?? ucfirst($leave['leave_type'] ?? 'Leave')) ?>
                            </h5>
                            <p class="mb-0 opacity-75">Application #<?= (int)($leave['id'] ?? 0) ?></p>
                        </div>
                        <?= ldStatusBadge($leave['status'] ?? 'pending') ?>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ld-info-row">
                                <div class="ld-label">Start Date</div>
                                <div class="ld-value"><?= ldDate($leave['start_date'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ld-info-row">
                                <div class="ld-label">End Date</div>
                                <div class="ld-value"><?= ldDate($leave['end_date'] ?? '') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ld-info-row">
                                <div class="ld-label">Total Days</div>
                                <div class="ld-value"><span class="badge bg-primary fs-6"><?= (int)($leave['total_days'] ?? 0) ?> day<?= (int)($leave['total_days'] ?? 0) !== 1 ? 's' : '' ?></span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ld-info-row">
                                <div class="ld-label">Applied On</div>
                                <div class="ld-value"><?= ldDateTime($leave['created_at'] ?? '') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="ld-info-row">
                        <div class="ld-label">Reason</div>
                        <div class="ld-value" class="style-19219"><?= htmlspecialchars($leave['reason'] ?? 'No reason provided') ?></div>
                    </div>
                    <?php if (!empty($leave['emergency_contact'])): ?>
                    <div class="ld-info-row">
                        <div class="ld-label">Emergency Contact</div>
                        <div class="ld-value"><?= htmlspecialchars($leave['emergency_contact']) ?></div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($leave['work_coverage'])): ?>
                    <div class="ld-info-row">
                        <div class="ld-label">Work Coverage Plan</div>
                        <div class="ld-value"><?= htmlspecialchars($leave['work_coverage']) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Approval Status -->
            <div class="card ld-card shadow-sm mb-3">
                <div class="card-header bg-white border-bottom fw-semibold"><i class="fas fa-gavel me-1"></i>Approval Status</div>
                <div class="card-body">
                    <?php if (strtolower($leave['status'] ?? '') === 'approved'): ?>
                        <div class="text-center text-success mb-2">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                        <div class="text-center fw-semibold mb-1">Approved</div>
                        <?php if (!empty($leave['approved_by_name'])): ?>
                            <div class="text-center text-muted small">By: <?= htmlspecialchars($leave['approved_by_name']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($leave['approved_at'])): ?>
                            <div class="text-center text-muted small">On: <?= ldDateTime($leave['approved_at']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($leave['approved_notes'])): ?>
                            <div class="mt-2 p-2 bg-success bg-opacity-10 rounded small"><?= htmlspecialchars($leave['approved_notes']) ?></div>
                        <?php endif; ?>
                    <?php elseif (strtolower($leave['status'] ?? '') === 'rejected'): ?>
                        <div class="text-center text-danger mb-2">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                        <div class="text-center fw-semibold mb-1">Rejected</div>
                        <?php if (!empty($leave['rejection_reason'])): ?>
                            <div class="mt-2 p-2 bg-danger bg-opacity-10 rounded small"><?= htmlspecialchars($leave['rejection_reason']) ?></div>
                        <?php endif; ?>
                    <?php elseif (strtolower($leave['status'] ?? '') === 'cancelled'): ?>
                        <div class="text-center text-secondary mb-2">
                            <i class="fas fa-ban fa-2x"></i>
                        </div>
                        <div class="text-center fw-semibold">Cancelled</div>
                        <div class="text-center text-muted small">You cancelled this application</div>
                    <?php else: ?>
                        <div class="text-center text-warning mb-2">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                        <div class="text-center fw-semibold">Pending Approval</div>
                        <div class="text-center text-muted small">Waiting for manager to review</div>
                        <div class="mt-3 text-center">
                            <form method="POST" action="/employee/leaves/<?= (int)$leave['id'] ?>/cancel" onsubmit="return confirm('Cancel this leave application?')">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>Cancel Application</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Info -->
            <div class="card ld-card shadow-sm">
                <div class="card-header bg-white border-bottom fw-semibold"><i class="fas fa-info-circle me-1"></i>Quick Info</div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Application ID</span><span class="fw-semibold">#<?= (int)($leave['id'] ?? 0) ?></span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Duration</span><span class="fw-semibold"><?= (int)($leave['total_days'] ?? 0) ?> day<?= (int)($leave['total_days'] ?? 0) !== 1 ? 's' : '' ?></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Last Updated</span><span class="fw-semibold"><?= ldDateTime($leave['updated_at'] ?? $leave['created_at'] ?? '') ?></span></div>
                </div>
            </div>
        </div>
    </div>
</div>
