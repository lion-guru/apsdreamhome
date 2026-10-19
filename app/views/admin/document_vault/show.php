<?php
/** Admin Document Vault - Show Document */

$page_title = $page_title ?? 'Document: ' . ($document['document_name'] ?? 'Unknown');
$document = $document ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.status-badge { font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 999px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-file-alt me-2 text-primary"></i><?= htmlspecialchars($document['document_name'] ?? 'Unknown') ?></h4>
            <p class="text-muted mb-0 small"><?= htmlspecialchars($document['employee_name'] ?? '') ?> • <?= htmlspecialchars($document['category_name'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($document['verification_status'] !== 'verified'): ?>
                <a href="<?= BASE_URL ?>/admin/document-vault/<?= $document['id'] ?>/verify" class="btn btn-success" onclick="return confirm('Verify this document?')"><i class="fas fa-check me-1"></i> Verify</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/document-vault/<?= $document['id'] ?>/download" class="btn btn-info"><i class="fas fa-download me-1"></i> Download</a>
            <a href="<?= BASE_URL ?>/admin/document-vault" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Status & Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-<?= 
                $document['verification_status'] === 'verified' ? 'success' : 
                ($document['verification_status'] === 'rejected' ? 'danger' : 'warning') ?> text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= ucfirst(str_replace('_', ' ', $document['verification_status'])) ?></div>
                    <div class="text-white-50 small">Status</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= htmlspecialchars($document['document_number'] ?? '—') ?></div>
                    <div class="text-white-50 small">Document #</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $document['file_size'] ? number_format($document['file_size'] / 1024, 1) . ' KB' : '—' ?></div>
                    <div class="text-white-50 small">File Size</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $document['mime_type'] ?? '—' ?></div>
                    <div class="text-white-50 small">File Type</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Details -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Document Details</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Document Number</div>
                        <div class="fw-semibold"><?= htmlspecialchars($document['document_number'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Document Name</div>
                        <div class="fw-semibold"><?= htmlspecialchars($document['document_name'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Category</div>
                        <div class="fw-semibold"><?= htmlspecialchars($document['category_name'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Employee</div>
                        <div class="fw-semibold"><?= htmlspecialchars($document['employee_name'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Email</div>
                        <div class="text-muted"><?= htmlspecialchars($document['employee_email'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">File Name</div>
                        <div class="fw-semibold"><?= htmlspecialchars($document['file_name'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">File Size</div>
                        <div class="fw-semibold"><?= $document['file_size'] ? number_format($document['file_size'] / 1024, 1) . ' KB' : '—' ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">MIME Type</div>
                        <div class="fw-semibold"><?= htmlspecialchars($document['mime_type'] ?? '—') ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Issue Date</div>
                        <div class="fw-semibold"><?= $document['issue_date'] ? date('d M Y', strtotime($document['issue_date'])) : '—' ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Expiry Date</div>
                        <div class="fw-semibold<?= $document['expiry_date'] && $document['expiry_date'] < date('Y-m-d') ? ' text-danger' : '' ?>">
                            <?= $document['expiry_date'] ? date('d M Y', strtotime($document['expiry_date'])) : '—' ?>
                            <?php if ($document['expiry_date'] && $document['expiry_date'] < date('Y-m-d')): ?>
                                <span class="badge bg-danger ms-1">Expired</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($document['issuing_authority'])): ?>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Issuing Authority</div>
                        <div class="fw-semibold"><?= htmlspecialchars($document['issuing_authority']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($document['document_number_ref'])): ?>
                <div class="col-md-6">
                    <div class="detail-row">
                        <div class="small text-muted">Document Reference</div>
                        <div class="fw-semibold font-monospace"><?= htmlspecialchars($document['document_number_ref']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- File Preview / Download -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-file me-2"></i>File</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-3">
                <?php if (!empty($document['file_path'])): ?>
                    <a href="<?= BASE_URL . '/' . $document['file_path'] ?>" target="_blank" class="btn btn-primary">
                        <i class="fas fa-external-link-alt me-1"></i> Open in New Tab
                    </a>
                    <a href="<?= BASE_URL ?>/admin/document-vault/<?= $document['id'] ?>/download" class="btn btn-info">
                        <i class="fas fa-download me-1"></i> Download
                    </a>
                <?php else: ?>
                    <span class="text-muted">File not available</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Verification History -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-history me-2"></i>Verification History</h5>
        </div>
        <div class="card-body">
            <?php 
            $verificationHistory = $db->fetchAll("
                SELECT dv.*, u.name as verifier_name 
                FROM document_verification_log dv
                LEFT JOIN users u ON dv.verified_by = u.id
                WHERE dv.document_id = ? AND dv.tenant_id = ?
                ORDER BY dv.verified_at DESC
            ", [$document['id'], $tenantId ?? 1]);
            
            if (empty($verificationHistory)): ?>
            <div class="text-center py-4 text-muted">No verification history</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px">Date</th>
                            <th style="width: 120px">Action</th>
                            <th style="width: 150px">Verified By</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($verificationHistory as $vh): 
                            $actionClass = $vh['action'] === 'verified' ? 'success' : ($vh['action'] === 'rejected' ? 'danger' : 'warning');
                        ?>
                        <tr>
                            <td><?= date('d M Y H:i', strtotime($vh['verified_at'])) ?></td>
                            <td><span class="badge bg-<?= $actionClass ?>"><?= ucfirst($vh['action']) ?></span></td>
                            <td><?= htmlspecialchars($vh['verifier_name'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($vh['comments'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>