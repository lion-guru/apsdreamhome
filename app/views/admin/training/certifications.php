<?php
/** Admin Certifications */

$page_title = $page_title ?? 'Employee Certifications';
$certifications = $certifications ?? [];
$employees = $employees ?? [];
$expiring = $expiring ?? [];
$employee_id = $employee_id ?? 0;
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.expiring-soon { color: #f59e0b; }
.expiring-urgent { color: #ef4444; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-certificate me-2 text-primary"></i>Employee Certifications</h4>
            <p class="text-muted mb-0 small">Track and manage employee certifications</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCertModal">
            <i class="fas fa-plus me-1"></i> Add Certification
        </button>
    </div>

    <!-- Expiring Alert -->
    <?php if (!empty($expiring)): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <h6 class="fw-semibold mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Certifications Expiring Soon (60 days)</h6>
        <div class="row">
            <?php foreach (array_slice($expiring, 0, 5) as $c): 
                $daysLeft = ceil((strtotime($c['expiry_date']) - time()) / 86400);
                $urgent = $daysLeft <= 30;
            ?>
            <div class="col-md-3 mb-2">
                <div class="alert alert-<?= $urgent ? 'danger' : 'warning' ?> py-2 mb-0">
                    <div class="small fw-semibold"><?= htmlspecialchars($c['employee_name']) ?></div>
                    <div class="small"><?= htmlspecialchars($c['certification_name']) ?></div>
                    <div class="small">Expires: <?= date('d M Y', strtotime($c['expiry_date'])) ?> <span class="<?= $urgent ? 'text-danger fw-bold' : 'text-warning fw-bold' ?>">(<?= $daysLeft ?> days)</span></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Filter by Employee</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $e): ?>
                            <option value="<?= $e['id'] ?>" <?= $employee_id == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name'] . ' (' . $e['department'] . ')') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="<?= BASE_URL ?>/admin/training/certifications" class="btn btn-outline-secondary btn-sm w-100">Clear Filter</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Certifications Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($certifications)): ?>
            <div class="text-center py-5">
                <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Certifications Found</h5>
                <p class="text-muted small mb-3">Add certifications for employees</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCertModal"><i class="fas fa-plus me-1"></i> Add Certification</button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Certification</th>
                            <th>Issuing Body</th>
                            <th>Issue Date</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certifications as $c): 
                            $daysLeft = $c['expiry_date'] ? ceil((strtotime($c['expiry_date']) - time()) / 86400) : null;
                            $isExpiring = $daysLeft !== null && $daysLeft <= 60 && $daysLeft > 30;
                            $isUrgent = $daysLeft !== null && $daysLeft <= 30 && $daysLeft >= 0;
                            $isExpired = $daysLeft !== null && $daysLeft < 0;
                            
                            $statusClass = $c['status'];
                            if ($isExpired) $statusClass = 'expired';
                            elseif ($isUrgent) $statusClass = 'expiring-urgent';
                            elseif ($isExpiring) $statusClass = 'expiring-soon';
                            
                            $statusBadge = [
                                'active' => 'success',
                                'expired' => 'danger',
                                'revoked' => 'secondary',
                                'pending_verification' => 'warning'
                            ][$c['status']] ?? 'secondary';
                        ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($c['employee_name']) ?></div>
                                <?php if (!empty($c['course_title'])): ?>
                                    <div class="small text-muted"><?= htmlspecialchars($c['course_title']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($c['certification_name']) ?></td>
                            <td><?= htmlspecialchars($c['issuing_body'] ?? '—') ?></td>
                            <td><?= $c['issue_date'] ? date('d M Y', strtotime($c['issue_date'])) : '—' ?></td>
                            <td>
                                <?php if ($c['expiry_date']): ?>
                                    <span class="fw-semibold <?= $isUrgent ? 'text-danger' : ($isExpiring ? 'text-warning' : ($isExpired ? 'text-danger' : '')) ?>">
                                        <?= date('d M Y', strtotime($c['expiry_date'])) ?>
                                        <?php if ($daysLeft !== null): ?>
                                            (<?= $daysLeft > 0 ? $daysLeft . ' days' : abs($daysLeft) . ' days ago' ?>)
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusBadge ?>">
                                    <?= ucfirst(str_replace('_', ' ', $c['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if (!empty($c['credential_url'])): ?>
                                        <a href="<?= htmlspecialchars($c['credential_url']) ?>" target="_blank" class="btn btn-outline-info" title="View Credential"><i class="fas fa-external-link-alt"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($c['attachment_path'])): ?>
                                        <a href="<?= BASE_URL . '/' . htmlspecialchars($c['attachment_path']) ?>" target="_blank" class="btn btn-outline-secondary" title="View Document"><i class="fas fa-file"></i></a>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-primary" onclick="editCert(<?= $c['id'] ?>)" title="Edit"><i class="fas fa-edit"></i></button>
                                </div>
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

<!-- Add Certification Modal -->
<div class="modal fade" id="addCertModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/training/certifications">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-certificate me-2"></i>Add Certification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required <?= $employee_id ? 'disabled' : '' ?>>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?= $e['id'] ?>" <?= $employee_id == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name'] . ' (' . $e['department'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($employee_id): ?>
                            <input type="hidden" name="employee_id" value="<?= $employee_id ?>">
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Certification Name <span class="text-danger">*</span></label>
                        <input type="text" name="certification_name" class="form-control" required placeholder="e.g., AWS Solutions Architect">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Issuing Body</label>
                        <input type="text" name="issuing_body" class="form-control" placeholder="e.g., Amazon Web Services">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Issue Date <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control">
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Credential ID</label>
                            <input type="text" name="credential_id" class="form-control" placeholder="Optional: Credential/license number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Credential URL</label>
                            <input type="url" name="credential_url" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="pending_verification">Pending Verification</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Certification</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Cert Modal (similar structure) -->
    <div class="modal fade" id="editCertModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= BASE_URL ?>/admin/training/certifications/1" id="editCertForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="cert_id" id="editCertId">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-certificate me-2"></i>Edit Certification</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Certification Name <span class="text-danger">*</span></label>
                            <input type="text" name="certification_name" class="form-control" required id="editCertName">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Issuing Body</label>
                            <input type="text" name="issuing_body" class="form-control" id="editCertBody">
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Issue Date</label>
                                <input type="date" name="issue_date" class="form-control" id="editCertIssue">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expiry Date</label>
                                <input type="date" name="expiry_date" class="form-control" id="editCertExpiry">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Credential ID</label>
                                <input type="text" name="credential_id" class="form-control" id="editCertCredId">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Credential URL</label>
                                <input type="url" name="credential_url" class="form-control" id="editCertCredUrl">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select" id="editCertStatus">
                                <option value="active">Active</option>
                                <option value="expired">Expired</option>
                                <option value="revoked">Revoked</option>
                                <option value="pending_verification">Pending Verification</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function editCert(id) {
    // In production, fetch cert details via AJAX
    // For now, we'll just show the modal with the ID
    document.getElementById('editCertId').value = id;
    // Pre-fill would require AJAX call - simplified for now
    new bootstrap.Modal(document.getElementById('editCertModal')).show();
}
</script>