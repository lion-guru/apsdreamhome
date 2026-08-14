<?php
$current_page = $current_page ?? 'documents';
$documents = $documents ?? [];
$stats = $stats ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
$base = BASE_URL ?? '/apsdreamhome';

$docTypeLabels = [
    'aadhaar' => ['label' => 'Aadhaar Card', 'icon' => 'fa-id-card', 'color' => 'primary'],
    'pan' => ['label' => 'PAN Card', 'icon' => 'fa-credit-card', 'color' => 'success'],
    'agreement' => ['label' => 'Agreement', 'icon' => 'fa-file-contract', 'color' => 'info'],
    'payment_receipt' => ['label' => 'Payment Receipt', 'icon' => 'fa-receipt', 'color' => 'warning'],
    'general' => ['label' => 'General Document', 'icon' => 'fa-file', 'color' => 'secondary'],
    'photo' => ['label' => 'Photo', 'icon' => 'fa-image', 'color' => 'info'],
    'bank_statement' => ['label' => 'Bank Statement', 'icon' => 'fa-university', 'color' => 'danger'],
    'salary_slip' => ['label' => 'Salary Slip', 'icon' => 'fa-money-check-alt', 'color' => 'warning'],
];
$statusBadges = [
    'pending' => 'warning',
    'approved' => 'success',
    'rejected' => 'danger',
];
?>

<style>
    .doc-stat-card { border-radius: 12px; padding: 20px; text-align: center; transition: all .2s; }
    .doc-stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.08); }
    .doc-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 10px; }
    .doc-row { transition: all .2s; }
    .doc-row:hover { background: #f8fafc; }
    .doc-icon-box { width: 44px; height: 44px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .upload-zone { border: 2px dashed #d1d5db; border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; transition: all .2s; }
    .upload-zone:hover { border-color: #2563eb; background: #eff6ff; }
</style>

<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-folder-open text-primary me-2"></i><?= __('assoc_doc_title', [], 'Document Locker') ?></h4>
            <small class="text-muted"><?= __('assoc_doc_desc', [], 'Upload and manage your important documents here.') ?></small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-cloud-upload-alt me-1"></i> <?= __('assoc_doc_upload', [], 'Upload Document') ?>
        </button>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="doc-stat-card bg-white border">
                <div class="doc-stat-icon" class="style-24507"><i class="fas fa-folder"></i></div>
                <h4 class="mb-0"><?= $stats['total'] ?></h4>
                <small class="text-muted"><?= __('assoc_doc_total', [], 'Total Documents') ?></small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat-card bg-white border">
                <div class="doc-stat-icon" class="style-17090"><i class="fas fa-clock"></i></div>
                <h4 class="mb-0"><?= $stats['pending'] ?></h4>
                <small class="text-muted"><?= __('assoc_doc_pending', [], 'Pending Review') ?></small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat-card bg-white border">
                <div class="doc-stat-icon" class="style-59662"><i class="fas fa-check-circle"></i></div>
                <h4 class="mb-0"><?= $stats['approved'] ?></h4>
                <small class="text-muted"><?= __('assoc_doc_approved', [], 'Approved') ?></small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat-card bg-white border">
                <div class="doc-stat-icon" class="style-64406"><i class="fas fa-times-circle"></i></div>
                <h4 class="mb-0"><?= $stats['rejected'] ?></h4>
                <small class="text-muted"><?= __('assoc_doc_rejected', [], 'Rejected') ?></small>
            </div>
        </div>
    </div>

    <!-- Document List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-list me-2"></i><?= __('assoc_doc_list', [], 'Your Documents') ?></h6>
            <span class="badge bg-primary"><?= count($documents) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($documents)): ?>
            <div class="text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3" class="style-11393"></i>
                <p class="text-muted mb-2"><?= __('assoc_doc_empty', [], 'No documents uploaded yet.') ?></p>
                <p class="text-muted small mb-3"><?= __('assoc_doc_empty_desc', [], 'Documents you upload will appear here for easy access.') ?></p>
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-plus me-1"></i> <?= __('assoc_doc_upload_first', [], 'Upload Your First Document') ?>
                </button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= __('assoc_doc_col_type', [], 'Type') ?></th>
                            <th><?= __('assoc_doc_col_name', [], 'Document Name') ?></th>
                            <th><?= __('assoc_doc_col_date', [], 'Uploaded') ?></th>
                            <th><?= __('assoc_doc_col_status', [], 'Status') ?></th>
                            <th><?= __('assoc_doc_col_actions', [], 'Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc):
                            $typeInfo = $docTypeLabels[$doc['document_type'] ?? 'general'] ?? $docTypeLabels['general'];
                            $statusClass = $statusBadges[$doc['status'] ?? 'pending'] ?? 'secondary';
                        ?>
                        <tr class="doc-row">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="doc-icon-box" class="style-96626">
                                        <i class="fas <?= $typeInfo['icon'] ?>" class="style-48136"></i>
                                    </div>
                                    <span class="small"><?= htmlspecialchars($typeInfo['label']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($doc['title'] ?? $doc['document_name'] ?? 'Untitled') ?></td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($doc['created_at'] ?? 'now')) ?></small></td>
                            <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($doc['status'] ?? 'pending') ?></span></td>
                            <td>
                                <?php if (!empty($doc['file_url']) || !empty($doc['file_path'])): ?>
                                <a href="<?= htmlspecialchars($doc['file_url'] ?? $doc['file_path'] ?? '#') ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (($doc['status'] ?? '') === 'rejected' && !empty($doc['remarks'])): ?>
                                <button class="btn btn-sm btn-outline-danger" title="<?= htmlspecialchars($doc['remarks']) ?>">
                                    <i class="fas fa-exclamation-triangle"></i>
                                </button>
                                <?php endif; ?>
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

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-cloud-upload-alt me-2"></i><?= __('assoc_doc_upload_title', [], 'Upload Document') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= $base ?>/associate/documents/upload" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?= __('assoc_doc_upload_type', [], 'Document Type') ?></label>
                        <select name="document_type" class="form-select" required>
                            <option value="general">General Document</option>
                            <option value="aadhaar">Aadhaar Card</option>
                            <option value="pan">PAN Card</option>
                            <option value="agreement">Agreement</option>
                            <option value="payment_receipt">Payment Receipt</option>
                            <option value="bank_statement">Bank Statement</option>
                            <option value="salary_slip">Salary Slip</option>
                            <option value="photo">Photo</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('assoc_doc_upload_name', [], 'Document Title') ?></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. My Aadhaar Card" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= __('assoc_doc_upload_file', [], 'Select File') ?></label>
                        <input type="file" name="document_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                        <small class="text-muted">PDF, JPG, PNG, DOC/DOCX â€” Max 10MB</small>
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
