<?php
/**
 * Grievance Detail View
*/

$page_title = $page_title ?? 'Grievance Detail';
$grievance = $grievance ?? [];
$comments = $comments ?? [];
$assignees = $assignees ?? [];
$categories = $categories ?? [];
$priorities = $priorities ?? [];
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
            <h4 class="mb-1 fw-bold"><i class="fas fa-gavel me-2 text-primary"></i>Grievance: <?= htmlspecialchars($grievance['grievance_number'] ?? 'N/A') ?></h4>
            <p class="text-muted mb-0 small"><?= htmlspecialchars($grievance['subject'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/grievances" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Status & Meta -->
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <?php
        $statusClass = [
            'open' => 'warning',
            'under_review' => 'info',
            'investigation' => 'purple',
            'mediation' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary',
            'reopened' => 'primary'
        ][$grievance['status']] ?? 'secondary';
        $priorityClass = [
            'low' => 'secondary',
            'normal' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            'critical' => 'danger'
        ][$grievance['priority']] ?? 'secondary';
        $isSlaBreached = $grievance['sla_breached'] ?? false;
        ?>
        <span class="badge bg-<?= $statusClass ?> fs-6 px-3 py-2">
            <i class="fas fa-<?= match($grievance['status'] ?? '') {
                'open' => 'folder-open',
                'under_review' => 'search',
                'investigation' => 'magnifying-glass',
                'mediation' => 'handshake',
                'resolved' => 'check-circle',
                'closed' => 'lock',
                default => 'redo',
            } ?> me-1"></i>
            <?= ucfirst(str_replace('_', ' ', $grievance['status'])) ?>
        </span>
        <span class="badge bg-<?= $priorityClass ?> fs-6 px-3 py-2">
            <i class="fas fa-<?= $grievance['priority'] === 'critical' ? 'skull-crossbones' : ($grievance['priority'] === 'urgent' ? 'exclamation-triangle' : ($grievance['priority'] === 'high' ? 'arrow-up' : ($grievance['priority'] === 'low' ? 'arrow-down' : 'minus'))) ?> me-1"></i>
            <?= ucfirst($grievance['priority']) ?>
        </span>
        <span class="badge bg-<?= $grievance['category'] === 'harassment' ? 'danger' : ($grievance['category'] === 'discrimination' ? 'warning' : 'info') ?> fs-6 px-3 py-2">
            <i class="fas fa-<?= $grievance['category'] === 'harassment' ? 'user-shield' : ($grievance['category'] === 'discrimination' ? 'balance-scale' : 'info-circle') ?> me-1"></i>
            <?= ucfirst($grievance['category']) ?>
        </span>
        <?php if ($isSlaBreached): ?>
            <span class="badge bg-danger fs-6 px-3 py-2">
                <i class="fas fa-exclamation-triangle me-1"></i>SLA Breached
            </span>
        <?php endif; ?>
    </div>

    <!-- Main Details -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card grv-card grv-<?= $grievance['status'] ?> shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-info-circle me-2"></i>Grievance Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Grievance Number</label>
                            <div class="fw-bold fs-5"><?= htmlspecialchars($grievance['grievance_number'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Sub Category</label>
                            <div class="fw-semibold"><?= htmlspecialchars($grievance['sub_category'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Employee</label>
                            <div class="fw-semibold"><?= htmlspecialchars($grievance['employee_name'] ?? 'Unknown') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Employee Code</label>
                            <div class="fw-semibold"><?= htmlspecialchars($grievance['employee_code'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Department</label>
                            <div class="fw-semibold"><?= htmlspecialchars($grievance['department'] ?? 'N/A') ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Assignee</label>
                            <div class="fw-semibold">
                                <?php if (!empty($grievance['assignee_name'])): ?>
                                    <?= htmlspecialchars($grievance['assignee_name']) ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Unassigned</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">SLA Hours</label>
                            <div class="fw-semibold text-<?= ($grievance['sla_breached'] ?? false) ? 'danger' : 'success' ?>">
                                <?= $grievance['sla_hours'] ?? 0 ?> hours
                                <?php if ($grievance['sla_breached'] ?? false): ?>
                                    <i class="fas fa-exclamation-triangle ms-1"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Anonymous</label>
                            <div class="fw-semibold"><?= ($grievance['is_anonymous'] ?? 0) ? 'Yes' : 'No' ?></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Created</label>
                            <div><?= date('d M Y, h:i A', strtotime($grievance['created_at'] ?? 'now')) ?></div>
                        </div>
                        <?php if (!empty($grievance['resolved_at'])): ?>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Resolved At</label>
                            <div><?= date('d M Y, h:i A', strtotime($grievance['resolved_at'])) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <label class="form-label text-muted small">Subject</label>
                            <div class="fw-semibold"><?= htmlspecialchars($grievance['subject'] ?? '') ?></div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted small">Description</label>
                            <div class="text-muted"><?= nl2br(htmlspecialchars($grievance['description'] ?? '')) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold"><i class="fas fa-cogs me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-cog me-1"></i> Change Status
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $grievance['id'] ?>, 'under_review')"><i class="fas fa-search me-2"></i>Under Review</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $grievance['id'] ?>, 'investigation')"><i class="fas fa-magnifying-glass me-2"></i>Investigation</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $grievance['id'] ?>, 'mediation')"><i class="fas fa-handshake me-2"></i>Mediation</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $grievance['id'] ?>, 'resolved')"><i class="fas fa-check-circle me-2"></i>Resolved</a></li>
                                <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $grievance['id'] ?>, 'closed')"><i class="fas fa-lock me-2"></i>Closed</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(<?= $grievance['id'] ?>, 'reopened')"><i class="fas fa-redo me-2"></i>Reopen</a></li>
                            </ul>
                        </div>
                        <div class="btn-group w-100 mt-2" role="group">
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-toggle="modal" data-bs-target="#assignGrievanceModal">
                                <i class="fas fa-user-plus me-1"></i> Assign
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-50" data-bs-toggle="modal" data-bs-target="#addCommentModal">
                                <i class="fas fa-comment me-1"></i> Comment
                            </button>
                        </div>
                        <?php if (!empty($grievance['assigned_to']) && $grievance['status'] !== 'closed'): ?>
                            <div class="mt-3 p-3 bg-light rounded">
                                <h6 class="fw-semibold mb-2"><i class="fas fa-user-tie me-1"></i>Assigned To</h6>
                                <div class="fw-semibold"><?= htmlspecialchars($grievance['assignee_name'] ?? 'Assigned Staff') ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if ($grievance['is_anonymous'] ?? false): ?>
                            <div class="mt-3 p-3 bg-warning bg-opacity-10 rounded border border-warning">
                                <i class="fas fa-user-secret me-2 text-warning"></i>
                                <strong>Anonymous Submission</strong>
                                <p class="mb-0 small text-muted">Employee identity is protected</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments & Thread -->
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><i class="fas fa-comments me-2"></i>Comments & Activity</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCommentModal">
                <i class="fas fa-plus me-1"></i> Add Comment
            </button>
        </div>
        <div class="card-body p-0">
            <?php if (empty($comments)): ?>
            <div class="text-center py-5">
                <i class="fas fa-comment-slash fa-2x text-muted mb-2"></i>
                <h6 class="text-muted">No comments yet</h6>
                <p class="text-muted small mb-0">Be the first to add a comment</p>
            </div>
            <?php else: ?>
            <div class="p-3">
                <?php foreach ($comments as $c): 
                    $isInternal = $c['is_internal'] ?? false;
                ?>
                <div class="border-bottom pb-3 mb-3 last:border-0 last:mb-0">
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0">
                            <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 0.875rem;">
                                <?= strtoupper(substr($c['author_name'] ?? 'U', 0, 1)) ?>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <strong><?= htmlspecialchars($c['author_name'] ?? 'Unknown') ?></strong>
                                    <?php if ($isInternal): ?>
                                        <span class="badge bg-danger ms-2">Internal</span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted"><?= date('d M Y, h:i A', strtotime($c['created_at'])) ?></small>
                            </div>
                            <div class="mt-2 text-muted"><?= nl2br(htmlspecialchars($c['comment'] ?? '')) ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Assign Grievance Modal -->
    <div class="modal fade" id="assignGrievanceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= BASE_URL ?>/admin/grievances/<?= (int)$grievance['id'] ?>/assign">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus me-2 text-primary"></i>Assign Grievance</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="detail_assignee_id" class="form-label fw-semibold">Select Staff Member <span class="text-danger">*</span></label>
                            <select name="assignee_id" id="detail_assignee_id" class="form-select" required>
                                <option value="">-- Choose Investigator / Staff --</option>
                                <?php foreach ($assignees as $staff): ?>
                                    <option value="<?= $staff['id'] ?>" <?= (!empty($grievance['assigned_to']) && $grievance['assigned_to'] == $staff['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($staff['name']) ?> (<?= ucfirst($staff['role']) ?><?= !empty($staff['department']) ? ' · ' . htmlspecialchars($staff['department']) : '' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">The assigned staff member will receive an instant notification in their portal.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i> Save Assignment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Comment Modal -->
    <div class="modal fade" id="addCommentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="addCommentForm" action="<?= BASE_URL ?>/admin/grievances/<?= (int)$grievance['id'] ?>/comment">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-comment me-2"></i>Add Comment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="detail_comment" class="form-label fw-semibold">Comment <span class="text-danger">*</span></label>
                            <textarea name="comment" id="detail_comment" class="form-control" rows="4" required placeholder="Enter your comment..."></textarea>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_internal" id="is_internal" value="1">
                            <label class="form-check-label" for="is_internal">Internal note (visible to admins only)</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Post Comment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function updateStatus(grievanceId, status) {
    if (!confirm('Change status to ' + status.replace('_', ' ') + '?')) return;

    fetch('<?= BASE_URL ?>/admin/grievances/' + grievanceId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'status=' + encodeURIComponent(status) + '&csrf_token=' + encodeURIComponent('<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>')
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update status');
        }
    })
    .catch(() => location.reload());
}
</script>