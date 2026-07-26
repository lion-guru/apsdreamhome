<?php
$documents = $documents ?? [];
$stats = $stats ?? ['total' => 0, 'verified' => 0, 'pending' => 0, 'expired' => 0];

function docIcon($type) {
    $icons = ['aadhaar' => 'id-card', 'pan' => 'id-badge', 'salary_slip' => 'money-bill', 'offer_letter' => 'file-contract', 'experience_letter' => 'briefcase', 'education' => 'graduation-cap', 'photo' => 'camera', 'other' => 'file'];
    return $icons[$type] ?? 'file';
}
function docStatusBadge($status) {
    $map = ['verified' => 'success', 'approved' => 'success', 'pending' => 'warning', 'rejected' => 'danger', 'expired' => 'danger'];
    $cls = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $cls . '">' . ucfirst(htmlspecialchars($status)) . '</span>';
}
function docTypeLabel($type) {
    $labels = ['aadhaar' => 'Aadhaar Card', 'pan' => 'PAN Card', 'salary_slip' => 'Salary Slip', 'offer_letter' => 'Offer Letter', 'experience_letter' => 'Experience Letter', 'education' => 'Education Certificate', 'photo' => 'Photo ID', 'other' => 'Other'];
    return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
}
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.emp-doc-stat { border: none; border-radius: 12px; transition: transform 0.2s; }
.emp-doc-stat:hover { transform: translateY(-2px); }
.emp-doc-stat .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.emp-doc-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; position: relative; overflow: hidden; }
.emp-doc-card:hover { border-color: #3b82f6; box-shadow: 0 4px 15px rgba(59,130,246,0.1); transform: translateY(-1px); }
.emp-doc-card .doc-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.emp-doc-card .doc-meta { font-size: 0.8rem; color: #64748b; }
.emp-doc-type-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 20px; background: #f1f5f9; color: #475569; }
.emp-doc-expired { border-left: 3px solid #ef4444 !important; }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-folder-open me-2 text-primary"></i>My Documents</h4>
            <p class="text-muted mb-0 small">Manage and upload your employee documents</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
            <i class="fas fa-cloud-upload-alt me-1"></i> Upload Document
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card emp-doc-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-files-o"></i></div>
                    <div><div class="fw-bold fs-4"><?= $stats['total'] ?></div><div class="text-muted small">Total</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-doc-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-check-circle"></i></div>
                    <div><div class="fw-bold fs-4 text-success"><?= $stats['verified'] ?></div><div class="text-muted small">Verified</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-doc-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-clock"></i></div>
                    <div><div class="fw-bold fs-4 text-warning"><?= $stats['pending'] ?></div><div class="text-muted small">Pending</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card emp-doc-stat shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-exclamation-triangle"></i></div>
                    <div><div class="fw-bold fs-4 text-danger"><?= $stats['expired'] ?></div><div class="text-muted small">Expired</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Grid -->
    <?php if (empty($documents)): ?>
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <div class="mb-3"><i class="fas fa-folder-open fa-4x text-muted opacity-25"></i></div>
                <h5 class="text-muted">No Documents Yet</h5>
                <p class="text-muted small mb-3">Upload your first document to get started</p>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                    <i class="fas fa-cloud-upload-alt me-1"></i> Upload Document
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($documents as $d): ?>
                <?php
                    $vs = $d['verification_status'] ?? 'pending';
                    $isExpired = !empty($d['expiry_date']) && $d['expiry_date'] < date('Y-m-d');
                    $cardClass = $isExpired ? 'emp-doc-card emp-doc-expired' : 'emp-doc-card';
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card <?= $cardClass ?> shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-3">
                                <div class="doc-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="fas fa-<?= docIcon($d['document_type'] ?? $d['type'] ?? 'other') ?>"></i>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($d['document_number'] ?: ($d['type'] ?? 'Document')) ?></h6>
                                        <?= docStatusBadge($vs) ?>
                                    </div>
                                    <div class="doc-meta mb-2">
                                        <span class="emp-doc-type-badge"><?= docTypeLabel($d['document_type'] ?? $d['type'] ?? 'other') ?></span>
                                    </div>
                                    <?php if (!empty($d['issued_by'])): ?>
                                        <div class="doc-meta"><i class="fas fa-building me-1"></i> <?= htmlspecialchars($d['issued_by']) ?></div>
                                    <?php endif; ?>
                                    <div class="d-flex gap-3 mt-2 doc-meta">
                                        <?php if (!empty($d['issue_date'])): ?>
                                            <span><i class="fas fa-calendar me-1"></i> <?= date('d M Y', strtotime($d['issue_date'])) ?></span>
                                        <?php endif; ?>
                                        <?php if ($isExpired): ?>
                                            <span class="text-danger fw-semibold"><i class="fas fa-exclamation-circle me-1"></i> Expired <?= date('d M Y', strtotime($d['expiry_date'])) ?></span>
                                        <?php elseif (!empty($d['expiry_date'])): ?>
                                            <span><i class="fas fa-calendar-check me-1"></i> Valid till <?= date('d M Y', strtotime($d['expiry_date'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 d-flex gap-2">
                            <?php if (!empty($d['url'])): ?>
                                <a href="<?= htmlspecialchars($d['url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?= BASE_URL ?>/employee/documents/upload" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="uploadDocModalLabel"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Document Type *</label>
                            <select name="document_type" class="form-select" required>
                                <option value="">Select type...</option>
                                <option value="aadhaar">Aadhaar Card</option>
                                <option value="pan">PAN Card</option>
                                <option value="salary_slip">Salary Slip</option>
                                <option value="offer_letter">Offer Letter</option>
                                <option value="experience_letter">Experience Letter</option>
                                <option value="education">Education Certificate</option>
                                <option value="photo">Photo ID</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Document Number</label>
                            <input type="text" name="document_number" class="form-control" placeholder="e.g. ABCDE1234F">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Issued By</label>
                            <input type="text" name="issued_by" class="form-control" placeholder="e.g. UIDAI, NSDL">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                            <small class="text-muted">Leave blank if no expiry</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">File *</label>
                            <input type="file" name="document_file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <small class="text-muted">Accepted: PDF, JPG, PNG, DOC, DOCX (Max 10MB)</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-1"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
