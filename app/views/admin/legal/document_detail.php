<?php
$doc = $doc ?? null;
$uploads = $uploads ?? [];
if (!$doc) { echo '<div class="container-fluid py-4"><div class="alert alert-danger">Document not found</div></div>'; return; }
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-file-contract me-2 text-primary"></i><?= htmlspecialchars($doc['document_number'] ?? 'Draft') ?> <small class="text-muted"><?= htmlspecialchars($doc['title'] ?? '') ?></small></h4>
        <div>
            <a href="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/preview" class="btn btn-outline-info btn-sm" target="_blank"><i class="fas fa-print me-1"></i>Preview</a>
            <a href="<?= BASE_URL ?>/admin/legal/documents" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-info-circle me-2"></i>Document Details</div>
                <div class="aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><strong>Document #:</strong><br><?= htmlspecialchars($doc['document_number'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Customer:</strong><br><?= htmlspecialchars($doc['customer_name'] ?? '-') ?><br><small class="text-muted"><?= htmlspecialchars($doc['customer_phone'] ?? '') ?></small></div>
                        <div class="col-md-4"><strong>Status:</strong><br><span class="badge bg-<?= match($doc['status']) { 'signed' => 'success', 'final' => 'info', 'draft' => 'secondary', 'expired' => 'warning', 'cancelled' => 'danger', default => 'secondary' } ?> fs-6"><?= $doc['status'] ?></span></div>
                        <div class="col-md-4"><strong>Category:</strong><br><?= htmlspecialchars($doc['category_name'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Template:</strong><br><?= htmlspecialchars($doc['template_name'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Entity:</strong><br><?= htmlspecialchars(ucfirst($doc['entity_type'] ?? 'general')) ?> #<?= htmlspecialchars($doc['entity_id'] ?? '-') ?></div>
                        <div class="col-md-4"><strong>Effective:</strong><br><?= $doc['effective_date'] ? date('d M Y', strtotime($doc['effective_date'])) : '-' ?></div>
                        <div class="col-md-4"><strong>Expiry:</strong><br><?= $doc['expiry_date'] ? date('d M Y', strtotime($doc['expiry_date'])) : '-' ?></div>
                        <div class="col-md-4"><strong>Created:</strong><br><?= date('d M Y h:i A', strtotime($doc['created_at'] ?? 'now')) ?></div>
                        <div class="col-12">
                            <strong>Submission Status:</strong><br>
                            <?php if (!empty($doc['submitted_online'])): ?><span class="badge bg-success me-1"><i class="fas fa-globe me-1"></i>Online <?= $doc['submitted_online_at'] ? date('d M Y', strtotime($doc['submitted_online_at'])) : '' ?></span><?php endif; ?>
                            <?php if (!empty($doc['submitted_physically'])): ?><span class="badge bg-primary me-1"><i class="fas fa-file me-1"></i>Physical <?= $doc['submitted_physically_at'] ? date('d M Y', strtotime($doc['submitted_physically_at'])) : '' ?></span><?php endif; ?>
                            <?php if (!empty($doc['kyc_verified'])): ?><span class="badge bg-info"><i class="fas fa-id-card me-1"></i>KYC Verified</span><?php endif; ?>
                            <?php if (empty($doc['submitted_online']) && empty($doc['submitted_physically'])): ?><span class="badge bg-secondary">Not Submitted</span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header d-flex justify-content-between">
                    <span><i class="fas fa-file-alt me-2"></i>Content Preview</span>
                    <a href="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/preview" class="btn btn-sm btn-outline-info" target="_blank"><i class="fas fa-external-link-alt me-1"></i>Full Preview</a>
                </div>
                <div class="aps-cp-card-body" style="max-height:500px;overflow-y:auto;border:1px solid #dee2e6;padding:20px;background:#fff;">
                    <?= $doc['content'] ?? '<p class="text-muted">No content</p>' ?>
                </div>
            </div>

            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-upload me-2"></i>Uploads (<?= count($uploads) ?>)</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($uploads)): ?>
                        <p class="text-muted small">No uploads yet</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>File</th><th>Type</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
                                <tbody>
                                <?php foreach ($uploads as $u): ?>
                                    <tr>
                                        <td><a href="<?= BASE_URL ?>/<?= htmlspecialchars($u['file_path']) ?>" target="_blank"><?= htmlspecialchars($u['file_name']) ?></a></td>
                                        <td><span class="badge bg-light text-dark"><?= htmlspecialchars($u['upload_type'] ?? 'other') ?></span></td>
                                        <td><span class="badge bg-<?= $u['status'] === 'verified' ? 'success' : ($u['status'] === 'rejected' ? 'danger' : 'warning') ?>"><?= $u['status'] ?></span></td>
                                        <td class="small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                        <td>
                                            <?php if ($u['status'] === 'pending'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/legal/uploads/<?= $u['id'] ?>/verify" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="status" value="verified">
                                                    <button class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>
                                                </form>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/legal/uploads/<?= $u['id'] ?>/verify" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/uploads/<?= $u['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this upload?')">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-cog me-2"></i>Actions</div>
                <div class="aps-cp-card-body">
                    <div class="d-grid gap-2">
                        <?php if ($doc['status'] === 'draft'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/status/final">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button class="btn btn-success w-100 btn-sm"><i class="fas fa-check me-1"></i>Mark as Final</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($doc['status'] === 'final'): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/status/signed">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button class="btn btn-success w-100 btn-sm"><i class="fas fa-pen me-1"></i>Mark as Signed</button>
                            </form>
                        <?php endif; ?>
                        <?php if (empty($doc['submitted_online'])): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/mark-online">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button class="btn btn-info w-100 btn-sm"><i class="fas fa-globe me-1"></i>Mark Submitted Online</button>
                            </form>
                        <?php endif; ?>
                        <?php if (empty($doc['submitted_physically'])): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/mark-physical">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button class="btn btn-primary w-100 btn-sm"><i class="fas fa-file me-1"></i>Mark Submitted Physically</button>
                            </form>
                        <?php endif; ?>
                        <?php if (empty($doc['kyc_verified'])): ?>
                            <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/kyc-verify">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button class="btn btn-warning w-100 btn-sm"><i class="fas fa-id-card me-1"></i>Verify KYC</button>
                            </form>
                        <?php endif; ?>
                        <hr>
                        <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/status/expired">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button class="btn btn-warning w-100 btn-sm"><i class="fas fa-clock me-1"></i>Mark Expired</button>
                        </form>
                        <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/status/cancelled">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button class="btn btn-danger w-100 btn-sm"><i class="fas fa-ban me-1"></i>Mark Cancelled</button>
                        </form>
                        <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/delete" onsubmit="return confirm('Archive this document?')">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <button class="btn btn-outline-danger w-100 btn-sm"><i class="fas fa-archive me-1"></i>Archive</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-sticky-note me-2"></i>Notes</div>
                <div class="aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>/update">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($doc['notes'] ?? '') ?></textarea>
                        <button class="btn btn-sm btn-outline-primary mt-2 w-100"><i class="fas fa-save me-1"></i>Save Notes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
