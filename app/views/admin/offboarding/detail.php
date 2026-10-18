<?php
/**
 * Offboarding Detail View
*/

$page_title = $page_title ?? 'Offboarding Details';
$employee = $employee ?? [];
$progress = $progress ?? [];
$templates = $templates ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.offb-item { border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 0.75rem; background: #fafafa; transition: all 0.2s; }
.offb-item:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); }
.offb-item.completed { border-left: 4px solid #22c55e; background: #f0fdf4; }
.offb-item.in_progress { border-left: 4px solid #3b82f6; background: #eff6ff; }
.offb-item.pending { border-left: 4px solid #f59e0b; background: #fffbeb; }
.offb-category-badge { font-size: 0.65rem; padding: 0.15rem 0.5rem; border-radius: 20px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-sign-out-alt me-2 text-primary"></i>Offboarding: <?= htmlspecialchars($employee['name'] ?? 'Unknown') ?></h4>
            <p class="text-muted mb-0 small"><?= htmlspecialchars($employee['employee_code'] ?? '') ?> • <?= htmlspecialchars($employee['department'] ?? 'N/A') ?> • <?= htmlspecialchars($employee['designation'] ?? 'N/A') ?></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" onclick="assignTemplate(<?= $employee['id'] ?>)">
                <i class="fas fa-plus me-1"></i> Assign Template
            </button>
            <a href="<?= BASE_URL ?>/admin/offboarding" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Progress Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $progress['total'] ?></div>
                    <div class="text-white-50 small">Total Items</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $progress['completed'] ?></div>
                    <div class="text-white-50 small">Completed</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $progress['in_progress'] ?></div>
                    <div class="text-white-50 small">In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $progress['pending'] ?></div>
                    <div class="text-white-50 small">Pending</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-tasks me-2"></i>Offboarding Progress</h5>
            <div class="badge bg-<?= 
                $progress['completion_rate'] === 100 ? 'success' : 
                ($progress['completion_rate'] > 0 ? 'primary' : 'secondary') ?> fs-6">
                <?= $progress['completion_rate'] ?>% Complete
            </div>
        </div>
        <div class="card-body">
            <div class="progress" style="height: 16px;" role="progressbar" aria-valuenow="<?= $progress['completion_rate'] ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $progress['completion_rate'] ?>%" aria-valuenow="<?= $progress['completion_rate'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <div class="d-flex justify-content-between mt-2 small text-muted">
                <span><?= $progress['completed'] ?> / <?= $progress['total'] ?> items completed</span>
                <span><?= $progress['completion_rate'] ?>% complete</span>
            </div>
        </div>
    </div>

    <!-- Checklist Items -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-tasks me-2"></i>Checklist Items</h5>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="statusFilter" onchange="filterItems(this.value)">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignTemplateModal">
                    <i class="fas fa-plus me-1"></i> Assign Template
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($progress['items'])): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Offboarding Items</h5>
                <p class="text-muted small mb-3">Assign an offboarding template to get started</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignTemplateModal">
                    <i class="fas fa-plus me-1"></i> Assign Template
                </button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Item</th>
                            <th style="width: 15%">Category</th>
                            <th style="width: 15%">Assigned To</th>
                            <th style="width: 10%">Due</th>
                            <th style="width: 10%">Status</th>
                            <th style="width: 10%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($progress['items'] as $item): 
                            $statusClass = [
                                'pending' => 'warning',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'skipped' => 'secondary',
                                'waived' => 'secondary'
                            ][$item['status']] ?? 'secondary';
                            $isOverdue = $item['due_date'] && $item['due_date'] < date('Y-m-d') && $item['status'] !== 'completed';
                        ?>
                        <tr class="offb-item <?= $item['status'] ?>" data-status="<?= $item['status'] ?>">
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($item['item_name']) ?></div>
                                <?php if (!empty($item['description'])): ?>
                                    <div class="small text-muted"><?= htmlspecialchars($item['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['category'])): ?>
                                    <span class="badge bg-light text-dark" style="background: #f1f5f9; color: #475569;">
                                        <?= htmlspecialchars(ucfirst($item['category'])) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['assigned_to'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($item['assigned_to']) ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($item['due_date'])): ?>
                                    <span class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-muted' ?>">
                                        <?= date('d M Y', strtotime($item['due_date'])) ?>
                                        <?php if ($isOverdue): ?><i class="fas fa-exclamation-circle ms-1"></i><?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $item['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($item['status'] !== 'completed'): ?>
                                        <button class="btn btn-outline-success btn-sm" onclick="updateItemStatus(<?= $item['id'] ?>, 'completed')" title="Mark Complete">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($item['status'] === 'pending'): ?>
                                        <button class="btn btn-outline-primary btn-sm" onclick="updateItemStatus(<?= $item['id'] ?>, 'in_progress')" title="Start">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($item['status'] === 'in_progress'): ?>
                                        <button class="btn btn-outline-warning btn-sm" onclick="updateItemStatus(<?= $item['id'] ?>, 'pending')" title="Mark Pending">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="editItemNotes(<?= $item['id'] ?>)" title="Notes">
                                        <i class="fas fa-sticky-note"></i>
                                    </button>
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

<!-- Assign Template Modal -->
<div class="modal fade" id="assignTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/offboarding/assign-template">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Assign Offboarding Template</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Template <span class="text-danger">*</span></label>
                        <select name="template_id" class="form-select" required>
                            <option value="">Select a template</option>
                            <?php foreach ($templates as $t): ?>
                                <option value="<?= $t['id'] ?>" data-items="<?= $t['item_count'] ?? 0 ?>">
                                    <?= htmlspecialchars($t['name']) ?> (<?= $t['item_count'] ?? 0 ?> items)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Assign Template
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Notes Modal -->
    <div class="modal fade" id="editNotesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="editNotesForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="item_id" id="editItemId">
                    <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">
                    <input type="hidden" name="status" value="">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-sticky-note me-2"></i>Add Notes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Add notes about this item..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Update Status</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="skipped">Skipped</option>
                                <option value="waived">Waived</option>
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
function filterItems(status) {
    const rows = document.querySelectorAll('.offb-item');
    rows.forEach(row => {
        if (!status || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function updateItemStatus(itemId, newStatus) {
    const csrfToken = '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>';
    const employeeId = '<?= $employee['id'] ?>';
    
    const formData = new FormData();
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
    formData.append('item_id', itemId);
    formData.append('employee_id', '<?= $employee['id'] ?>');
    formData.append('status', newStatus);
    
    fetch('<?= BASE_URL ?>/admin/offboarding/update-item', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update status');
        }
    });
}

function editItemNotes(itemId) {
    document.getElementById('editItemId').value = itemId;
    // Find the item data
    const items = <?= json_encode($progress['items']) ?>;
    const item = items.find(i => i.id == itemId);
    if (item) {
        document.querySelector('#editNotesForm [name="notes"]').value = item.notes || '';
        document.querySelector('#editNotesForm [name="status"]').value = item.status || 'pending';
    }
    new bootstrap.Modal(document.getElementById('editNotesModal')).show();
}

function cancelEncashment(id) {
    if (!confirm('Are you sure you want to cancel this request?')) return;
    fetch('<?= BASE_URL ?>/employee/self-service/encashment/cancel/' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>')
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Failed'); });
}
</script>