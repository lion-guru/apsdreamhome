<?php
/**
 * Respond to Shift Swap Request
 */

$page_title = $page_title ?? 'Respond to Shift Swap';
$swap = $swap ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-<?= $swap['type'] === 'received' ? 'warning text-dark' : 'info text-white' ?>">
                    <h4 class="mb-0"><i class="fas fa-exchange-alt me-2"></i><?= htmlspecialchars($page_title) ?></h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <!-- Swap Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Swap Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Date:</strong> <?= date('d M Y', strtotime($swap['swap_date'] ?? 'now')) ?>
                                </div>
                                <div class="col-md-6">
                                    <strong>Type:</strong> 
                                    <span class="badge bg-<?= ($swap['type'] ?? '') === 'requested' ? 'info' : 'warning text-dark' ?>">
                                        <?= ucfirst($swap['type'] ?? 'swap') ?>
                                    </span>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">My Shift</h6>
                                    <p class="mb-1">
                                        <span class="badge bg-<?= ($swap['my_shift_type'] ?? '') === 'night' ? 'dark' : 'primary' ?>">
                                            <?= ucfirst($swap['my_shift_type'] ?? 'day') ?>
                                        </span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Time:</strong> <?= date('H:i', strtotime($swap['my_start_time'] ?? '09:00')) ?> - 
                                        <?= date('H:i', strtotime($swap['my_end_time'] ?? '18:00')) ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Colleague:</strong> <?= htmlspecialchars($swap['colleague_name'] ?? 'N/A') ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-2">Colleague's Shift</h6>
                                    <p class="mb-1">
                                        <span class="badge bg-<?= ($swap['colleague_shift_type'] ?? '') === 'night' ? 'dark' : 'primary' ?>">
                                            <?= ucfirst($swap['colleague_shift_type'] ?? 'day') ?>
                                        </span>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Time:</strong> <?= date('H:i', strtotime($swap['colleague_start_time'] ?? '09:00')) ?> - 
                                        <?= date('H:i', strtotime($swap['colleague_end_time'] ?? '18:00')) ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Requested by:</strong> 
                                        <?php if (($swap['type'] ?? '') === 'requested'): ?>
                                            You
                                        <?php else: ?>
                                            <?= htmlspecialchars($swap['requester_name'] ?? 'Colleague') ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-12">
                                    <strong>Reason:</strong>
                                    <p class="mt-1"><?= htmlspecialchars($swap['reason'] ?? 'Not specified') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (($swap['type'] ?? '') === 'received' && ($swap['status'] ?? '') === 'pending'): ?>
                    <!-- Response Form -->
                    <div class="card mb-4">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-reply me-2"></i>Your Response</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="<?= BASE_URL ?>/employee/self-service/shift-swaps/<?= $swap['id'] ?>/respond" id="respondSwapForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="action" id="actionInput">

                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-success flex-fill" onclick="setAction('accept')">
                                                <i class="fas fa-check me-1"></i> Accept Swap
                                            </button>
                                            <button type="button" class="btn btn-danger flex-fill" onclick="setAction('reject')">
                                                <i class="fas fa-times me-1"></i> Reject Swap
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3" id="rejectionReasonRow" style="display: none;">
                                    <label for="rejection_reason" class="col-sm-3 col-form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                                    <div class="col-sm-9">
                                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Explain why you cannot accept this swap..."></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-9 offset-sm-3">
                                        <button type="submit" class="btn btn-primary btn-lg me-2" id="submitBtn" disabled>
                                            <i class="fas fa-paper-plane me-1"></i> <span id="submitText">Submit Response</span>
                                        </button>
                                        <a href="<?= BASE_URL ?>/employee/self-service/shift-swaps" class="btn btn-outline-secondary btn-lg">
                                            <i class="fas fa-arrow-left me-1"></i> Back
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="text-muted">
                                <?php if (($swap['status'] ?? '') === 'completed'): ?>
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h5>Swap Completed</h5>
                                <p>This shift swap has been completed and the schedule has been updated.</p>
                                <?php elseif (($swap['status'] ?? '') === 'rejected'): ?>
                                <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                                <h5>Swap Rejected</h5>
                                <p>This shift swap was rejected.</p>
                                <?php if (!empty($swap['rejection_reason'])): ?>
                                <p class="text-muted"><strong>Reason:</strong> <?= htmlspecialchars($swap['rejection_reason']) ?></p>
                                <?php endif; ?>
                                <?php else: ?>
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h5>Pending Response</h5>
                                <p>Waiting for the other party to respond.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const actionInput = document.getElementById('actionInput');
    const rejectionReasonRow = document.getElementById('rejectionReasonRow');
    const rejectionReason = document.getElementById('rejection_reason');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');

    function setAction(action) {
        actionInput.value = action;
        if (action === 'reject') {
            rejectionReasonRow.style.display = 'block';
            rejectionReason.required = true;
            submitBtn.textContent = ' Reject Swap';
            submitBtn.className = 'btn btn-danger btn-lg';
        } else {
            rejectionReasonRow.style.display = 'none';
            rejectionReason.required = false;
            submitBtn.textContent = ' Accept Swap';
            submitBtn.className = 'btn btn-success btn-lg';
        }
        submitBtn.disabled = false;
    }

    window.setAction = setAction;

    // Form validation
    document.getElementById('respondSwapForm').addEventListener('submit', function(e) {
        if (actionInput.value === 'reject' && !rejectionReason.value.trim()) {
            e.preventDefault();
            rejectionReason.focus();
            alert('Please provide a rejection reason.');
        }
    });
});
</script>