<?php
$r = $request ?? [];
$verifyResults = $_SESSION['kyc_verify_results'] ?? null;
unset($_SESSION['kyc_verify_results']);
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-id-card me-2"></i>KYC Request #<?= $r['id'] ?? '' ?></h1>
        <a href="<?= BASE_URL ?>/admin/kyc" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>KYC Details</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th class="style-97126">ID</th><td>#<?= $r['id'] ?? '' ?></td></tr>
                        <tr><th>Legal Name</th><td><strong><?= htmlspecialchars($r['legal_name'] ?? '—') ?></strong></td></tr>
                        <tr><th>PAN</th><td><code><?= htmlspecialchars($r['pan_number'] ?? '—') ?></code></td></tr>
                        <tr><th>Aadhaar</th><td><code><?= htmlspecialchars($r['aadhaar_number'] ?? '—') ?></code></td></tr>
                        <tr><th>Date of Birth</th><td><?= htmlspecialchars($r['dob'] ?? '—') ?></td></tr>
                        <tr><th>Status</th>
                            <td>
                                <span class="badge bg-<?= match($r['status'] ?? 'pending') { 'approved' => 'success', 'rejected' => 'danger', 'pending' => 'warning', default => 'secondary' } ?> fs-6">
                                    <?= ucfirst($r['status'] ?? 'Pending') ?>
                                </span>
                            </td>
                        </tr>
                        <tr><th>Submitted</th><td><?= date('M j, Y H:i', strtotime($r['created_at'] ?? 'now')) ?></td></tr>
                        <?php if (!empty($r['rejection_reason'])): ?>
                        <tr><th>Rejection Reason</th><td class="text-danger"><?= htmlspecialchars($r['rejection_reason'] ?? '') ?></td></tr>
                        <?php endif; ?>
                        <?php if (!empty($r['verified_at'])): ?>
                        <tr><th>Verified At</th><td><?= date('M j, Y H:i', strtotime($r['verified_at'])) ?></td></tr>
                        <?php endif; ?>
                    </table></div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-user me-2"></i>User Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm">
                        <tr><th class="style-97126">Name</th><td><?= htmlspecialchars($r['user_name'] ?? '') ?></td></tr>
                        <tr><th>Email</th><td><?= htmlspecialchars($r['user_email'] ?? '') ?></td></tr>
                        <tr><th>Phone</th><td><?= htmlspecialchars($r['user_phone'] ?? '') ?></td></tr>
                        <tr><th>User ID</th><td><?= (int)($r['user_id'] ?? 0) ?></td></tr>
                    </table></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-file-image me-2"></i>Documents</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <?php if (!empty($r['pan_document'])): ?>
                            <div class="col-md-4">
                                <div class="card aps-cp-card">
                                    <div class="card-header aps-cp-card-header"><small>PAN Card</small></div>
                                    <a href="<?= BASE_URL . '/' . $r['pan_document'] ?>" target="_blank" class="d-block">
                                        <img src="<?= BASE_URL . '/' . $r['pan_document'] ?>" class="card-img-top" class="style-96299" alt="PAN">
                                    </a>
                                    <div class="card-body text-center p-2">
                                        <a href="<?= BASE_URL . '/' . $r['pan_document'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Full</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($r['aadhaar_front_document'])): ?>
                            <div class="col-md-4">
                                <div class="card aps-cp-card">
                                    <div class="card-header aps-cp-card-header"><small>Aadhaar Front</small></div>
                                    <a href="<?= BASE_URL . '/' . $r['aadhaar_front_document'] ?>" target="_blank" class="d-block">
                                        <img src="<?= BASE_URL . '/' . $r['aadhaar_front_document'] ?>" class="card-img-top" class="style-96299" alt="Aadhaar Front">
                                    </a>
                                    <div class="card-body text-center p-2">
                                        <a href="<?= BASE_URL . '/' . $r['aadhaar_front_document'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Full</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($r['aadhaar_back_document'])): ?>
                            <div class="col-md-4">
                                <div class="card aps-cp-card">
                                    <div class="card-header aps-cp-card-header"><small>Aadhaar Back</small></div>
                                    <a href="<?= BASE_URL . '/' . $r['aadhaar_back_document'] ?>" target="_blank" class="d-block">
                                        <img src="<?= BASE_URL . '/' . $r['aadhaar_back_document'] ?>" class="card-img-top" class="style-96299" alt="Aadhaar Back">
                                    </a>
                                    <div class="card-body text-center p-2">
                                        <a href="<?= BASE_URL . '/' . $r['aadhaar_back_document'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">View Full</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (empty($r['pan_document']) && empty($r['aadhaar_front_document']) && empty($r['aadhaar_back_document'])): ?>
                            <div class="col-12 text-muted text-center py-3">No documents uploaded</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (($r['status'] ?? '') !== 'approved'): ?>
            <!-- Verify via API Button -->
            <div class="card shadow-sm mb-4 border-primary">
                <div class="card-header bg-primary text-white"><h5 class="mb-0"><i class="fas fa-robot me-2"></i>API Verification</h5></div>
                <div class="card-body aps-cp-card-body">
                    <form method="post" action="<?= BASE_URL ?>/admin/kyc/<?= (int)($r['id'] ?? 0) ?>/verify" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-shield-alt me-1"></i>Verify PAN + Aadhaar via NSDL/UIDAI</button>
                    </form>
                    <?php if ($verifyResults): ?>
                    <div class="mt-3">
                        <h6 class="fw-bold">Verification Results:</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="card <?= ($verifyResults['pan']['success'] ?? false) ? 'border-success' : 'border-danger' ?>">
                                    <div class="card-body text-center p-2">
                                        <small class="fw-bold">PAN (NSDL)</small><br>
                                        <?php if ($verifyResults['pan']['success'] ?? false): ?>
                                            <span class="badge bg-success">Verified</span>
                                            <br><small class="text-muted"><?= htmlspecialchars($verifyResults['pan']['data']['name_on_card'] ?? '') ?></small>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Failed</span>
                                            <br><small class="text-danger"><?= htmlspecialchars($verifyResults['pan']['message'] ?? '') ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card <?= ($verifyResults['aadhaar']['success'] ?? false) ? 'border-success' : 'border-danger' ?>">
                                    <div class="card-body text-center p-2">
                                        <small class="fw-bold">Aadhaar (UIDAI)</small><br>
                                        <?php if ($verifyResults['aadhaar']['success'] ?? false): ?>
                                            <span class="badge bg-success">Verified</span>
                                            <br><small class="text-muted">Checksum OK</small>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Failed</span>
                                            <br><small class="text-danger"><?= htmlspecialchars($verifyResults['aadhaar']['message'] ?? '') ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark"><h5 class="mb-0"><i class="fas fa-gavel me-2"></i>Take Action</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex gap-2">
                        <form method="post" action="<?= BASE_URL ?>/admin/kyc/<?= (int)($r['id'] ?? 0) ?>/approve" class="flex-grow-1" data-aps-confirm="Approve this KYC request?">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <button type="submit" class="btn btn-success w-100"><i class="fas fa-check me-1"></i>Approve</button>
                        </form>
                        <button type="button" class="btn btn-danger flex-grow-1" data-bs-toggle="collapse" data-bs-target="#rejectForm">
                            <i class="fas fa-times me-1"></i>Reject
                        </button>
                    </div>
                    <div class="collapse mt-3" id="rejectForm">
                        <form method="post" action="<?= BASE_URL ?>/admin/kyc/<?= (int)($r['id'] ?? 0) ?>/reject">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Enter reason for rejection..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100" data-aps-confirm="Reject this KYC request?">
                                <i class="fas fa-times me-1"></i>Confirm Rejection
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card shadow-sm mb-4 border-success">
                <div class="card-body text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-success">KYC Approved</h5>
                    <p class="text-muted mb-0">This request was verified and approved.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
