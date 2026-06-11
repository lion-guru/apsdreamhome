<?php
$page_title = $page_title ?? 'Employee Documents';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>Employee Documents</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fas fa-upload me-2"></i>Upload</button>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body aps-cp-card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Employee</label>
                <select name="employee_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All users</option>
                    <?php foreach ($users ?? [] as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= ($emp_id ?? '') == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Employee</th><th>Document Type</th><th>Document Name</th><th>File</th><th>Uploaded At</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($documents ?? [])): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No documents</td></tr>
                    <?php else: ?>
                        <?php foreach ($documents as $d): ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars($d['employee_name'] ?? '') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($d['document_type'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($d['document_name'] ?? '') ?></td>
                                <td>
                                    <?php if ($d['file_path'] ?? ''): ?>
                                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($d['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-download"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted">No file</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($d['uploaded_at'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if (($total_pages ?? 1) > 1): ?>
        <div class="card-footer bg-white">
            <nav><ul class="pagination pagination-sm mb-0 justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>"><a class="page-link" href="?employee_id=<?= $emp_id ?? 0 ?>&page=<?= $i ?>"><?= $i ?></a></li>
                <?php endfor; ?>
            </ul></nav>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/hr/documents/upload" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select</option>
                            <?php foreach ($users ?? [] as $emp): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select name="document_type" class="form-select">
                            <option value="id_proof">ID Proof</option>
                            <option value="address_proof">Address Proof</option>
                            <option value="education">Education Certificate</option>
                            <option value="experience">Experience Letter</option>
                            <option value="contract">Employment Contract</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3"><label class="form-label">Document Name</label><input type="text" name="document_name" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">File</label><input type="file" name="document_file" class="form-control"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Upload</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
