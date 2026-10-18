<?php
/**
 * Employee Grievances Index
*/

$page_title = $page_title ?? 'Employee Grievances';
$grievances = $grievances ?? [];
$stats = $stats ?? [];
$categories = $categories ?? [];
$priorities = $priorities ?? [];
$assignees = $assignees ?? [];
$employees = $employees ?? [];
$filters = $filters ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.grv-card { border: 1px solid #e2e8f0; border-radius: 12px; transition: all 0.2s; }
.grv-card:hover { border-color: #7c2d12; box-shadow: 0 4px 15px rgba(124,45,18,0.1); }
.grv-open { border-left: 4px solid #ffc107; }
.grv-under_review { border-left: 4px solid #3b82f6; }
.grv-investigation { border-left: 4px solid #9333ea; }
.grv-mediation { border-left: 4px solid #f59e0b; }
.grv-resolved { border-left: 4px solid #22c55e; }
.grv-closed { border-left: 4px solid #64748b; }
.grv-reopened { border-left: 4px solid #3b82f6; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-gavel me-2 text-primary"></i>Employee Grievances</h4>
            <p class="text-muted mb-0 small">Manage employee grievances and complaints</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createGrievanceModal">
                <i class="fas fa-plus me-1"></i> New Grievance
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-3"><?= $stats['total'] ?? 0 ?></div>
                    <div class="text-white-50 small">Total Grievances</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-warning text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['open'] ?? 0 ?></div>
                    <div class="text-white-50 small">Open</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['in_progress'] ?? 0 ?></div>
                    <div class="text-white-50 small">In Progress</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <div class="fw-bold fs-4"><?= $stats['resolved'] ?? 0 ?></div>
                    <div class="text-white-50 small">Resolved</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="under_review" <?= ($filters['status'] ?? '') === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                        <option value="investigation" <?= ($filters['status'] ?? '') === 'investigation' ? 'selected' : '' ?>>Investigation</option>
                        <option value="mediation" <?= ($filters['status'] ?? '') === 'mediation' ? 'selected' : '' ?>>Mediation</option>
                        <option value="resolved" <?= ($filters['status'] ?? '') === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                        <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="reopened" <?= ($filters['status'] ?? '') === 'reopened' ? 'selected' : '' ?>>Reopened</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($filters['category'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <?php foreach ($priorities as $key => $label): ?>
                            <option value="<?= $key ?>" <?= ($filters['priority'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Assignee</label>
                    <select name="assignee" class="form-select form-select-sm">
                        <option value="">All Assignees</option>
                        <?php foreach ($assignees as $a): ?>
                            <option value="<?= $a['id'] ?>" <?= ($filters['assignee'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Grievances List -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($grievances)): ?>
            <div class="text-center py-5">
                <i class="fas fa-gavel fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Grievances Found</h5>
                <p class="text-muted small mb-3">Submit your first grievance or adjust filters</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createGrievanceModal">
                    <i class="fas fa-plus me-1"></i> Submit Grievance
                </button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="grievancesTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Category</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assignee</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grievances as $g): 
                            $statusClass = [
                                'open' => 'warning',
                                'under_review' => 'info',
                                'investigation' => 'purple',
                                'mediation' => 'warning',
                                'resolved' => 'success',
                                'closed' => 'secondary',
                                'reopened' => 'primary'
                            ][$g['status']] ?? 'secondary';
                            $priorityClass = [
                                'low' => 'secondary',
                                'normal' => 'info',
                                'high' => 'warning',
                                'urgent' => 'danger',
                                'critical' => 'danger'
                            ][$g['priority']] ?? 'secondary';
                            $isSlaBreached = $g['sla_breached'] ?? false;
                        ?>
                        <tr class="grv-card grv-<?= $g['status'] ?>">
                            <td class="fw-semibold"><?= $g['grievance_number'] ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($g['employee_name'] ?? 'Unknown') ?></div>
                                <small class="text-muted"><?= htmlspecialchars($g['employee_code'] ?? '') ?> • <?= htmlspecialchars($g['department'] ?? 'N/A') ?></small>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($g['category'] ?? '') ?></span>
                                <?php if (!empty($g['sub_category'])): ?>
                                    <div class="small text-muted"><?= htmlspecialchars($g['sub_category']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-truncate" style="max-width: 250px;"><?= htmlspecialchars($g['subject']) ?></div>
                                <small class="text-muted"><?= substr($g['description'] ?? '', 0, 80) ?><?= strlen($g['description'] ?? '') > 80 ? '...' : '' ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $priorityClass ?>"><?= ucfirst($g['priority']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?>">
                                    <?= ucfirst(str_replace('_', ' ', $g['status'])) ?>
                                </span>
                                <?php if ($isSlaBreached): ?>
                                    <i class="fas fa-exclamation-triangle text-danger ms-1" title="SLA Breached"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($g['assignee_name'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($g['assignee_name']) ?></small>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($g['created_at'])) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/grievances/<?= $g['id'] ?>" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $g['id'] ?>, 'under_review')"><i class="fas fa-search me-2"></i>Under Review</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $g['id'] ?>, 'investigation')"><i class="fas fa-magnifying-glass me-2"></i>Investigation</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $g['id'] ?>, 'mediation')"><i class="fas fa-handshake me-2"></i>Mediation</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $g['id'] ?>, 'resolved')"><i class="fas fa-check-circle me-2"></i>Resolved</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $g['id'] ?>, 'closed')"><i class="fas fa-lock me-2"></i>Closed</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(<?= $g['id'] ?>, 'reopened')"><i class="fas fa-redo me-2"></i>Reopen</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="assignGrievance(<?= $g['id'] ?>)"><i class="fas fa-user-plus me-2"></i>Assign</a></li>
                                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/admin/grievances/<?= $g['id'] ?>"><i class="fas fa-eye me-2"></i>View Details</a></li>
                                        </ul>
                                    </div>
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

<!-- Create Grievance Modal -->
<div class="modal fade" id="createGrievanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="createGrievanceForm" action="<?= BASE_URL ?>/admin/grievances/store">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Submit Grievance</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="create_employee_id" class="form-label fw-semibold">Employee / Complainant <span class="text-danger">*</span></label>
                            <select name="employee_id" id="create_employee_id" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp['employee_id'] ?? $emp['id'] ?>">
                                        <?= htmlspecialchars($emp['name']) ?> (<?= htmlspecialchars($emp['email']) ?> - <?= htmlspecialchars($emp['department'] ?? 'Staff') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="create_category" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <select name="category" id="create_category" class="form-select" required>
                                <option value="">Select category</option>
                                <?php foreach ($categories as $key => $label): ?>
                                    <option value="<?= $key ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="create_priority" class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" id="create_priority" class="form-select" required>
                                <option value="normal" selected>Normal</option>
                                <option value="low">Low</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="create_subject" class="form-label fw-semibold">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" id="create_subject" class="form-control" required placeholder="Brief summary of the grievance">
                        </div>
                        <div class="col-12">
                            <label for="create_description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="create_description" class="form-control" rows="4" required placeholder="Detailed description of the grievance..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="create_sub_category" class="form-label fw-semibold">Sub-category</label>
                            <input type="text" name="sub_category" id="create_sub_category" class="form-control" placeholder="e.g., Workstation ergonomics, Salary delay">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch pt-4">
                                <input class="form-check-input" type="checkbox" name="is_anonymous" id="is_anonymous" value="1">
                                <label class="form-check-label" for="is_anonymous">Submit anonymously</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-1"></i> Submit Grievance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Grievance Modal -->
<div class="modal fade" id="assignGrievanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignGrievanceForm" method="POST" onsubmit="submitAssignForm(event)">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" id="modalAssignGrievanceId" value="">
                <div class="modal-header bg-light">
                    <h5 class="modal-title"><i class="fas fa-user-check me-2 text-primary"></i>Assign Grievance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label for="modalAssigneeSelect" class="form-label fw-semibold">Select Staff / Investigator</label>
                    <select name="assignee_id" id="modalAssigneeSelect" class="form-select" required>
                        <option value="">-- Choose Staff Member --</option>
                        <?php foreach ($assignees as $staff): ?>
                            <option value="<?= $staff['id'] ?>">
                                <?= htmlspecialchars($staff['name']) ?> (<?= ucfirst($staff['role']) ?> - <?= htmlspecialchars($staff['department'] ?? 'Operations') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check me-1"></i> Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function updateStatus(grievanceId, status) {
    if (!confirm('Change status to ' + status + '?')) return;
    
    fetch('<?= BASE_URL ?>/admin/grievances/' + grievanceId + '/status', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'status=' + encodeURIComponent(status) + '&csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>')
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed to update status');
    })
    .catch(err => {
        alert('Network error while updating status');
    });
}

function assignGrievance(grievanceId) {
    document.getElementById('modalAssignGrievanceId').value = grievanceId;
    var modal = new bootstrap.Modal(document.getElementById('assignGrievanceModal'));
    modal.show();
}

function submitAssignForm(e) {
    e.preventDefault();
    var grievanceId = document.getElementById('modalAssignGrievanceId').value;
    var assigneeId = document.getElementById('modalAssigneeSelect').value;
    if (!assigneeId) {
        alert('Please select an assignee.');
        return;
    }

    fetch('<?= BASE_URL ?>/admin/grievances/' + grievanceId + '/assign', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: 'assignee_id=' + encodeURIComponent(assigneeId) + '&csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>')
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed to assign grievance');
    })
    .catch(err => {
        alert('Network error while assigning grievance');
    });
}
</script>