<?php
/** Admin Training Assignments */

$page_title = $page_title ?? 'Course Assignments';
$assignments = $assignments ?? [];
$courses = $courses ?? [];
$employees = $employees ?? [];
$managers = $managers ?? [];
$filters = $filters ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}
.progress-bar-mini {
    height: 8px;
    border-radius: 4px;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-user-graduate me-2 text-primary"></i>Course Assignments</h4>
            <p class="text-muted mb-0 small">Manage employee course assignments and track progress</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal">
            <i class="fas fa-plus me-1"></i> Assign Course
        </button>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Course</label>
                    <select name="course_id" class="form-select form-select-sm">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($filters['course_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['code'] . ' - ' . $c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Employee</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        <?php foreach ($employees as $e): ?>
                            <option value="<?= $e['id'] ?>" <?= ($filters['employee_id'] ?? '') == $e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['name']) ?> (<?= htmlspecialchars($e['department']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="assigned" <?= ($filters['status'] ?? '') === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                        <option value="in_progress" <?= ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="overdue" <?= ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                        <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Assigned By</label>
                    <select name="assigned_by" class="form-select form-select-sm">
                        <option value="">All</option>
                        <?php foreach ($managers as $m): ?>
                            <option value="<?= $m['id'] ?>" <?= ($filters['assigned_by'] ?? '') == $m['id'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search me-1"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($assignments)): ?>
            <div class="text-center py-5">
                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Assignments Found</h5>
                <p class="text-muted small mb-3">Assign courses to employees to get started</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignModal"><i class="fas fa-plus me-1"></i> Assign Course</button>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px">Course</th>
                            <th style="width: 180px">Employee</th>
                            <th style="width: 120px">Assigned By</th>
                            <th style="width: 100px">Assigned Date</th>
                            <th style="width: 100px">Due Date</th>
                            <th style="width: 100px">Progress</th>
                            <th style="width: 120px">Status</th>
                            <th style="width: 100px">Score</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $a): 
                            $statusClass = [
                                'assigned' => 'secondary',
                                'in_progress' => 'primary',
                                'completed' => 'success',
                                'failed' => 'danger',
                                'overdue' => 'warning',
                                'cancelled' => 'secondary'
                            ][$a['status']] ?? 'secondary';
                            
                            $isOverdue = $a['due_date'] && $a['due_date'] < date('Y-m-d') && $a['status'] !== 'completed';
                        ?>
                        <tr>
                            <td>
                                <code class="small"><?= htmlspecialchars($a['course_code']) ?></code>
                                <div class="small text-muted"><?= htmlspecialchars($a['course_title']) ?></div>
                            </td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($a['employee_name']) ?></div>
                                <div class="small text-muted"><?= htmlspecialchars($a['employee_email']) ?></div>
                            </td>
                            <td>
                                <small class="text-muted"><?= htmlspecialchars($a['assigned_by_name'] ?? '—') ?></small>
                            </td>
                            <td><?= date('d M Y', strtotime($a['assigned_at'])) ?></td>
                            <td>
                                <?= $a['due_date'] ? date('d M Y', strtotime($a['due_date'])) : '<span class="text-muted">—</span>' ?>
                                <?php if ($isOverdue): ?><i class="fas fa-exclamation-circle text-danger ms-1"></i><?php endif; ?>
                            </td>
                            <td>
                                <div class="progress progress-bar-mini">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $a['progress'] ?>%" aria-valuenow="<?= $a['progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?= $a['progress'] ?>%</small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusClass ?> status-badge">
                                    <?= ucfirst(str_replace('_', ' ', $a['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($a['score'])): ?>
                                    <span class="fw-semibold <?= $a['score'] >= 70 ? 'text-success' : 'text-danger' ?>"><?= $a['score'] ?>%</span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php if ($a['status'] !== 'completed'): ?>
                                        <button class="btn btn-outline-success" onclick="updateProgress(<?= $a['id'] ?>, 100, 'completed')" title="Mark Complete"><i class="fas fa-check"></i></button>
                                    <?php endif; ?>
                                    <?php if ($a['status'] === 'assigned'): ?>
                                        <button class="btn btn-outline-primary" onclick="updateProgress(<?= $a['id'] ?>, 50, 'in_progress')" title="Start"><i class="fas fa-play"></i></button>
                                    <?php endif; ?>
                                    <?php if (!empty($a['certificate_issued'])): ?>
                                        <span class="btn btn-outline-info disabled" title="Certificate: <?= $a['certificate_number'] ?>"><i class="fas fa-certificate"></i></span>
                                    <?php elseif ($a['status'] === 'completed' && !$a['certificate_issued']): ?>
                                        <button class="btn btn-outline-info" onclick="issueCertificate(<?= $a['id'] ?>)" title="Issue Certificate"><i class="fas fa-certificate"></i></button>
                                    <?php endif; ?>
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

<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/training/assign">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Assign Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                        <select name="course_id" class="form-select" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['code'] . ' - ' . $c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $e): ?>
                                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name'] . ' (' . $e['department'] . ')') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assigned By</label>
                        <select name="assigned_by" class="form-select">
                            <?php foreach ($managers as $m): ?>
                                <option value="<?= $m['id'] ?>" <?= $m['id'] == ($_SESSION['admin_id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Assign</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Issue Certificate Modal -->
    <div class="modal fade" id="certModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= BASE_URL ?>/admin/training/assignments/certificate" id="certForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="assignment_id" id="certAssignmentId">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-certificate me-2"></i>Issue Certificate</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Certificate Number (auto-generated if empty)</label>
                            <input type="text" name="certificate_number" class="form-control" placeholder="Leave blank for auto">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info"><i class="fas fa-certificate me-1"></i> Issue Certificate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function updateProgress(id, progress, status) {
    const formData = new FormData();
    formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>');
    formData.append('assignment_id', id);
    formData.append('progress', progress);
    formData.append('status', status);

    fetch('<?= BASE_URL ?>/admin/training/assignments/progress', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Failed');
    });
}

function issueCertificate(id) {
    document.getElementById('certAssignmentId').value = id;
    new bootstrap.Modal(document.getElementById('certModal')).show();
}
</script>