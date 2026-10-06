<?php
$page_title = $page_title ?? 'Withdrawal Request Details';
$base = defined('BASE_URL') ? BASE_URL : '';
$request = $request ?? [];
$wallet = $wallet ?? null;
$transactions = $transactions ?? [];
$history = $history ?? [];
$base = BASE_URL;
$csrf_token = $_SESSION['csrf_token'] ?? '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="m-0"><i class="fas fa-money-bill-wave me-2 text-info"></i>Withdrawal Request #<?= $request['id'] ?></h4>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/admin/withdrawals/export" class="btn btn-outline-secondary btn-sm"><i class="fas fa-download me-1"></i>Export CSV</a>
            <a href="<?= $base ?>/admin/withdrawals" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <?php 
            $statusClass = match($request['status']) {
                'pending' => 'warning',
                'approved' => 'info',
                'rejected' => 'danger',
                'completed' => 'success',
                'processing' => 'primary',
                default => 'secondary'
            };
            $statusIcon = match($request['status']) {
                'pending' => 'clock',
                'approved' => 'check-circle',
                'rejected' => 'times-circle',
                'completed' => 'check-double',
                'processing' => 'spinner',
                default => 'question'
            };
            ?>
            <span class="badge bg-<?= $statusClass ?> fs-6 px-3 py-2">
                <i class="fas fa-<?= $statusIcon ?> me-1"></i>
                <?= ucfirst($request['status']) ?>
            </span>
        </div>
        <div class="d-flex gap-2">
            <?php if ($request['status'] === 'pending'): ?>
                <button type="button" class="btn btn-success" onclick="openApproveModal(<?= $request['id'] ?>)">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
                <button type="button" class="btn btn-danger" onclick="openRejectModal(<?= $request['id'] ?>)">
                    <i class="fas fa-times me-1"></i>Reject
                </button>
            <?php elseif ($request['status'] === 'approved'): ?>
                <button type="button" class="btn btn-primary" onclick="openCompleteModal(<?= $request['id'] ?>)">
                    <i class="fas fa-check-double me-1"></i>Mark Completed
                </button>
            <?php endif; ?>
            <a href="<?= $base ?>/admin/withdrawals/export" class="btn btn-outline-success"><i class="fas fa-download me-1"></i>Export CSV</a>
            <a href="<?= $base ?>/admin/withdrawals" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Request Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-info-circle me-2"></i>Request Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Request ID</label>
                            <div class="fw-bold">#<?= $request['id'] ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Created</label>
                            <div class="fw-medium"><?= date('d M Y H:i', strtotime($request['created_at'])) ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Amount</label>
                            <div class="fw-bold fs-5 text-primary">₹<?= number_format((float)($request['amount'] ?? 0), 2) ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <?php 
                            $statusClass = match($request['status']) {
                                'pending' => 'warning',
                                'approved' => 'info',
                                'rejected' => 'danger',
                                'completed' => 'success',
                                'processing' => 'primary',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?= $statusClass ?> fs-6 px-3 py-2">
                                <i class="fas fa-<?= match($request['status']) {
                                    'pending' => 'clock',
                                    'approved' => 'check-circle',
                                    'rejected' => 'times-circle',
                                    'completed' => 'check-double',
                                    'processing' => 'spinner',
                                    default => 'question'
                                } ?> me-1"></i>
                                <?= ucfirst($request['status']) ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Processed At</label>
                            <div><?= $request['processed_at'] ? date('d M Y H:i', strtotime($request['processed_at'])) : '<span class="text-muted">Not processed</span>' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Approved At</label>
                            <div><?= $request['approved_at'] ? date('d M Y H:i', strtotime($request['approved_at'])) : '<span class="text-muted">Not approved</span>' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Approved By</label>
                            <div><?= $request['approved_by'] ? $request['approved_by'] : '<span class="text-muted">N/A</span>' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Wallet System</label>
                            <div class="fw-medium"><?= ucfirst(str_replace('_', ' ', $request['wallet_system'] ?? 'unknown')) ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Bank Account</label>
                            <div class="fw-medium"><?= htmlspecialchars($request['account_holder_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Bank</label>
                            <div><?= htmlspecialchars($request['bank_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Account</label>
                            <div>****<?= $request['account_number'] ? substr($request['account_number'], -4) : '----' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">IFSC</label>
                            <div><?= htmlspecialchars($request['ifsc_code'] ?? 'N/A') ?></div>
                        </div>
                        <?php if ($request['rejection_reason']): ?>
                        <div class="col-12">
                            <label class="form-label text-muted small">Rejection Reason</label>
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?= htmlspecialchars($request['rejection_reason']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($request['remarks']): ?>
                        <div class="col-12">
                            <label class="form-label text-muted small">Admin Remarks</label>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-sticky-note me-2"></i>
                                <?= htmlspecialchars($request['remarks']) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- User Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-user me-2"></i>User Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-muted small">User ID</label>
                            <div class="fw-medium">#<?= $request['user_id'] ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Name</label>
                            <div class="fw-medium"><?= htmlspecialchars($request['name'] ?? 'Unknown') ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Role</label>
                            <div>
                                <span class="badge bg-<?= 
                                    $request['role'] === 'customer' ? 'primary' : 
                                    ($request['role'] === 'associate' ? 'success' : 
                                    ($request['role'] === 'agent' ? 'warning' : 'info')) ?>">
                                    <?= ucfirst($request['role'] ?? 'unknown') ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Email</label>
                            <div><?= htmlspecialchars($request['email'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Phone</label>
                            <div><?= htmlspecialchars($request['phone'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Referral Code</label>
                            <div><?= htmlspecialchars($request['referral_code'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-university me-2"></i>Bank Account Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Account Holder</label>
                            <div class="fw-medium"><?= htmlspecialchars($request['account_holder_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Bank Name</label>
                            <div><?= htmlspecialchars($request['bank_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Branch</label>
                            <div><?= htmlspecialchars($request['branch_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Account Number</label>
                            <div class="fw-medium">****<?= $request['account_number'] ? substr($request['account_number'], -4) : '----' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">IFSC Code</label>
                            <div><?= htmlspecialchars($request['ifsc_code'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">MICR Code</label>
                            <div><?= htmlspecialchars($request['micr_code'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Primary Account</label>
                            <div>
                                <span class="badge bg-<?= ($request['is_primary'] ?? 0) ? 'success' : 'secondary' ?>">
                                    <?= ($request['is_primary'] ?? 0) ? 'Yes' : 'No' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Wallet Balance -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-wallet me-2 text-success"></i>Wallet Balance</h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($wallet): ?>
                        <div class="fs-3 fw-bold text-success mb-1">₹<?= number_format((float)($wallet['balance'] ?? $wallet['points_balance'] ?? 0), 2) ?></div>
                        <small class="text-muted">Available Balance</small>
                        <hr>
                        <div class="row text-center g-2">
                            <div class="col-6">
                                <div class="fw-bold text-primary"><?= number_format((float)($wallet['total_credited'] ?? 0), 2) ?></div>
                                <small class="text-muted">Total Credited</small>
                            </div>
                            <div class="col-6">
                                <div class="fw-bold text-danger"><?= number_format((float)($wallet['total_debited'] ?? $wallet['total_used'] ?? 0), 2) ?></div>
                                <small class="text-muted">Total Debited</small>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">Wallet not found</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Withdrawal Transactions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-history me-2"></i>Recent Withdrawal Transactions</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($transactions)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($transactions, 0, 10) as $tx): ?>
                                        <?php 
                                        $isCredit = in_array($tx['transaction_type'] ?? '', ['credit', 'commission', 'bonus', 'referral', 'refund']);
                                        $typeClass = $isCredit ? 'text-success' : 'text-danger';
                                        ?>
                                        <tr>
                                            <td class="small text-muted"><?= date('d M H:i', strtotime($tx['created_at'] ?? 'now')) ?></td>
                                            <td class="<?= $typeClass ?> fw-medium"><?= $isCredit ? '+' : '-' ?>₹<?= number_format((float)($tx['amount'] ?? 0), 2) ?></td>
                                            <td class="fw-bold">₹<?= number_format((float)($tx['balance_after'] ?? 0), 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No withdrawal transactions</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- User's Withdrawal History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="m-0"><i class="fas fa-list me-2"></i>User's Withdrawal History</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($history)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $h): ?>
                                        <?php 
                                        $hStatusClass = match($h['status']) {
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'rejected' => 'danger',
                                            'completed' => 'success',
                                            'processing' => 'primary',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <tr>
                                            <td><strong>#<?= $h['id'] ?></strong></td>
                                            <td class="text-end">₹<?= number_format((float)($h['amount'] ?? 0), 2) ?></td>
                                            <td><span class="badge bg-<?= $hStatusClass ?>"><?= ucfirst($h['status']) ?></span></td>
                                            <td class="text-muted small"><?= date('d M Y', strtotime($h['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No previous withdrawal requests</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>/admin/withdrawals/update/<?= $request['id'] ?>" id="approveForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="status" value="approved">
                <input type="hidden" name="id" value="<?= $request['id'] ?>">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-check-circle me-2"></i>Approve Withdrawal Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Request #<?= $request['id'] ?></strong> for <strong>₹<?= number_format((float)($request['amount'] ?? 0), 2) ?></strong>
                        <br><small>User: <?= htmlspecialchars($request['name'] ?? 'Unknown') ?> (<?= htmlspecialchars($request['email'] ?? '') ?>)</small>
                    </div>
                    <p>Are you sure you want to <strong>approve</strong> this withdrawal request?</p>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Add notes for the user..."></textarea>
                        <div class="form-text">Notes will be visible to the user in their withdrawal history.</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sendNotificationApprove" name="send_notification" checked>
                        <label class="form-check-label" for="sendNotificationApprove">
                            <i class="fas fa-bell me-1"></i>Send notification to user
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="approveSubmitBtn" disabled>
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="approveSpinner"></i>
                        <i class="fas fa-check me-1"></i><span id="approveBtnText">Approve</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>/admin/withdrawals/update/<?= $request['id'] ?>" id="rejectForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="status" value="rejected">
                <input type="hidden" name="id" value="<?= $request['id'] ?>">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Withdrawal Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will reject the withdrawal of <strong>₹<?= number_format((float)($request['amount'] ?? 0), 2) ?></strong> and refund the amount to the user's wallet.
                        <br><small>User: <?= htmlspecialchars($request['name'] ?? 'Unknown') ?> (<?= htmlspecialchars($request['email'] ?? '') ?>)</small>
                    </div>
                    <p class="text-danger"><strong>Warning:</strong> This action will refund the amount to the user's wallet. The funds will be returned to their original wallet.</p>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="admin_notes" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                        <div class="form-text">This reason will be visible to the user and recorded in the audit trail.</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sendNotificationReject" name="send_notification" checked>
                        <label class="form-check-label" for="sendNotificationReject">
                            <i class="fas fa-bell me-1"></i>Send notification to user
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="rejectSubmitBtn" disabled>
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="rejectSpinner"></i>
                        <i class="fas fa-times me-1"></i><span id="rejectBtnText">Reject</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1" aria-hidden="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>/admin/withdrawals/update/<?= $request['id'] ?>" id="completeForm">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="status" value="completed">
                <input type="hidden" name="id" value="<?= $request['id'] ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-check-double me-2"></i>Mark Withdrawal as Completed</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Request #<?= $request['id'] ?></strong> for <strong>₹<?= number_format((float)($request['amount'] ?? 0), 2) ?></strong>
                        <br><small>User: <?= htmlspecialchars($request['name'] ?? 'Unknown') ?> (<?= htmlspecialchars($request['email'] ?? '') ?>)</small>
                    </div>
                    <p>Mark this approved withdrawal as <strong>completed</strong>? This indicates the funds have been successfully transferred to the user's bank account.</p>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Add completion notes (e.g., UTR number, transfer reference)..."></textarea>
                        <div class="form-text">Notes will be recorded in the audit trail and visible to the user.</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="sendNotificationComplete" name="send_notification" checked>
                        <label class="form-check-label" for="sendNotificationComplete">
                            <i class="fas fa-bell me-1"></i>Send completion notification to user
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="completeSubmitBtn" disabled>
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="completeSpinner"></i>
                        <i class="fas fa-check-double me-1"></i><span id="completeBtnText">Mark Completed</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Global state for loading states
let currentLoadingBtn = null;

function showLoading(btnId, spinnerId, textId, loadingText) {
    const btn = document.getElementById(btnId);
    const spinner = document.getElementById(spinnerId);
    const text = document.getElementById(textId);
    if (btn && spinner && text) {
        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = loadingText;
        currentLoadingBtn = { btnId, spinnerId, textId };
    }
}

function hideLoading(btnId, spinnerId, textId, originalText) {
    const btn = document.getElementById(btnId);
    const spinner = document.getElementById(spinnerId);
    const text = document.getElementById(textId);
    if (btn && spinner && text) {
        btn.disabled = false;
        spinner.classList.add('d-none');
        text.textContent = originalText;
        currentLoadingBtn = null;
    }
}

function openApproveModal() {
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
    // Focus the notes textarea after modal is shown
    setTimeout(() => {
        const textarea = document.querySelector('#approveForm textarea[name="admin_notes"]');
        if (textarea) textarea.focus();
    }, 150);
}

function openRejectModal() {
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
    setTimeout(() => {
        const textarea = document.querySelector('#rejectForm textarea[name="admin_notes"]');
        if (textarea) textarea.focus();
    }, 150);
}

function openCompleteModal() {
    const modal = new bootstrap.Modal(document.getElementById('completeModal'));
    modal.show();
    setTimeout(() => {
        const textarea = document.querySelector('#completeForm textarea[name="admin_notes"]');
        if (textarea) textarea.focus();
    }, 150);
}

// Form submission handlers with loading states
document.addEventListener('DOMContentLoaded', function() {
    // Approve form
    const approveForm = document.getElementById('approveForm');
    if (approveForm) {
        approveForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('approveSubmitBtn');
            const textarea = this.querySelector('textarea[name="admin_notes"]');
            if (submitBtn) {
                showLoading('approveSubmitBtn', 'approveSpinner', 'approveBtnText', 'Approving...');
            }
        });
    });

    // Reject form
    const rejectForm = document.getElementById('rejectForm');
    if (rejectForm) {
        rejectForm.addEventListener('submit', function(e) {
            const textarea = this.querySelector('textarea[name="admin_notes"]');
            if (!textarea.value.trim()) {
                e.preventDefault();
                alert('Rejection reason is required');
                return false;
            }
            const submitBtn = document.getElementById('rejectSubmitBtn');
            if (submitBtn) {
                showLoading('rejectSubmitBtn', 'rejectSpinner', 'rejectBtnText', 'Rejecting...');
            }
        });
    });

    // Complete form
    const completeForm = document.getElementById('completeForm');
    if (completeForm) {
        completeForm.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('completeSubmitBtn');
            if (submitBtn) {
                showLoading('completeSubmitBtn', 'completeSpinner', 'completeBtnText', 'Completing...');
            }
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Escape to close modals
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(modal => {
                bootstrap.Modal.getInstance(modal)?.hide();
            });
        }
        // Enter to submit focused form (when modal is open)
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
            const activeModal = document.querySelector('.modal.show');
            if (activeModal) {
                const form = activeModal.querySelector('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.click();
                    }
                }
            }
        }
    });

    // Auto-hide loading states after 30 seconds (fallback)
    setInterval(function() {
        if (currentLoadingBtn) {
            const btn = document.getElementById(currentLoadingBtn.btnId);
            if (btn && btn.disabled) {
                // Check if form was submitted (page will redirect)
                // If still on same page after 30s, something went wrong
                console.warn('Loading state timeout for:', currentLoadingBtn.btnId);
            }
        }
    }, 30000);

    // Add enter key support for textareas (Ctrl+Enter to submit)
    document.querySelectorAll('textarea[name="admin_notes"]').forEach(textarea => {
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                const form = this.closest('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        e.preventDefault();
                        submitBtn.click();
                    }
                }
            }
        });
    });
});
</script>